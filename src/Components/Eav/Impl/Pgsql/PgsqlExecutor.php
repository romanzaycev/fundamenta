<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Impl\Pgsql;

use Cycle\Database\DatabaseInterface;
use Psr\Log\LoggerInterface;
use Romanzaycev\Fundamenta\Components\Eav\AttributeHelper;
use Romanzaycev\Fundamenta\Components\Eav\AttributeType;
use Romanzaycev\Fundamenta\Components\Eav\Exceptions\QueryException;
use Romanzaycev\Fundamenta\Components\Eav\Internals\Executor;
use Romanzaycev\Fundamenta\Components\Eav\Internals\LogicCompiler;
use Romanzaycev\Fundamenta\Components\Eav\Order;
use Romanzaycev\Fundamenta\Components\Eav\Query;
use Romanzaycev\Fundamenta\Components\Eav\Row;
use Romanzaycev\Fundamenta\Components\Eav\RowSet;
use Romanzaycev\Fundamenta\Configuration;

readonly class PgsqlExecutor implements Executor
{
    private string $attributesTable;
    private string $entitiesTable;
    private string $valuesTable;

    public function __construct(
        private Configuration $configuration,
        private Materializer $materializer,
        private DatabaseInterface $database,
        private LoggerInterface $logger,
    )
    {
        $tablesCfg = $this->configuration->get("eav.schema.tables");
        $this->entitiesTable = $tablesCfg["entity"];
        $this->attributesTable = $tablesCfg["attribute"];
        $this->valuesTable = $tablesCfg["value"];
    }

    /**
     * @throws QueryException
     */
    public function execute(Query $query): RowSet
    {
        $ctx = new QueryContext();

        $where = $query->getWhere();
        $merger = new Merger(
            $query,
            $ctx,
            $this->materializer,
        );
        $whereStr = $where ? $merger->merge((new LogicCompiler($where))->compile()) : "";
        $merger->finalize();

        $sortingSelectSql = "";
        $orderParams = [];
        $order = $query->getOrder();

        if (!empty($order)) {
            $attrOrderSqls = [];

            foreach ($order as $attr => $o) {
                $attr = AttributeHelper::normalizeAttributeCode($attr);

                if (AttributeHelper::isEntityOwned($attr)) {
                    $orderParams[] = "ee." . $attr . " " . $o->value;
                } else {
                    if ($attribute = $this->materializer->getAttribute($query->getEntityTypeCode(), $attr)) {
                        $attrOrderSqls[] = sprintf(
                            "CASE WHEN pr.ea_code = cast('%s' as varchar) THEN ev.value_%s END AS sortable_%s",
                            $attribute->code,
                            match ($attribute->type) {
                                AttributeType::VARCHAR => "varchar",
                                AttributeType::TEXT => "text",
                                AttributeType::INTEGER => "integer",
                                AttributeType::NUMERIC => "numeric",
                                AttributeType::BOOL => "bool",
                                AttributeType::DATE_TIME => "date",
                            },
                            $attribute->code,
                        );
                        $sortingAggrFn = in_array($o, [Order::DESC, Order::DESC_NULLS, Order::NULLS_DESC]) ? "MAX" : "MIN";
                        $orderParams[] = $sortingAggrFn . "(mapped_val.sortable_" . $attribute->code . ") " . $o->value;
                    }
                }
            }

            if (!empty($attrOrderSqls)) {
                $sortingSelectSql = (",\n" . implode(",\n", $attrOrderSqls) . "\n");
            }
        }

        $withSql = $this->getWithSql($query, $ctx, $sortingSelectSql);
        $selectSql = /** @lang PostgreSQL */<<<'SQL'
        SELECT
            ee.id         AS id,
            ee.type_id    AS type_id,
            ee.alias      AS alias,
            ee.created_at AS created_at,
            ee.updated_at AS updated_at,
            jsonb_object_agg(
                mapped_val.code,
                COALESCE(
                    mapped_val.vv,
                    mapped_val.vt,
                    cast(mapped_val.vi as varchar),
                    cast(mapped_val.vn as varchar),
                    cast(mapped_val.vb as varchar),
                    cast(mapped_val.vd as varchar)
                )
            )::jsonb AS values
        FROM %%eav_entities_tbl%% ee
        JOIN mapped_val ON ee.id = mapped_val.ei
        WHERE ee.id IN (SELECT ei FROM mapped_val)
        SQL;
        $selectSql = str_replace('%%eav_entities_tbl%%', $this->entitiesTable, $selectSql);

        if (!empty($whereStr)) {
            $whereStr = "\nAND " . $whereStr;
        }

        $sql = $withSql . "\n" . $selectSql . $whereStr;
        $sql .= "\nGROUP BY ee.id, ee.type_id, ee.created_at, ee.updated_at, ee.alias";

        $nonPaginatedBindings = $ctx->getBindings();

        if (!empty($orderParams)) {
            $sql .= "\nORDER BY " . \implode(", ", $orderParams);
        }

        $limit = $query->getLimit();
        $offset = $query->getOffset();

        if ($limit || $offset) {
            $ctx->bind("limit", (int)$limit);
            $ctx->bind("offset", (int)$offset);

            $sql .= "\nLIMIT :limit OFFSET :offset";
        }

        $bindings = $ctx->getBindings();
        $this
            ->logger
            ->debug("[PgsqlExecutor] SQL debug: " . $sql, [
                "bindings" => $bindings,
            ]);

        $countSql = $this->getCountSql($withSql, $whereStr);

        return new RowSet(
            $this
                ->database
                ->query(
                    $sql,
                    $bindings,
                ),
            function () use ($countSql, $nonPaginatedBindings) {
                return (int)$this
                    ->database
                    ->query($countSql, $nonPaginatedBindings)
                    ->fetchColumn(0)
                ;
            },
            fn (array $item): Row => PgsqlRow::create(
                $item,
                $query->getEntityTypeCode(),
                $this->materializer,
                $this->logger,
            ),
        );
    }

    /**
     * @throws QueryException
     */
    public function count(Query $query): int
    {
        $ctx = new QueryContext();

        $where = $query->getWhere();
        $merger = new Merger(
            $query,
            $ctx,
            $this->materializer,
        );
        $whereStr = $where ? $merger->merge((new LogicCompiler($where))->compile()) : "";
        $merger->finalize();

        if (!empty($whereStr)) {
            $whereStr = "\nAND " . $whereStr;
        }

        return (int)$this
            ->database
            ->query($this->getCountSql(
                $this->getWithSql($query, $ctx),
                $whereStr,
            ), $ctx->getBindings())
            ->fetchColumn(0);
    }

    /**
     * @throws QueryException
     */
    public function getWithSql(Query $query, QueryContext $ctx, string $sortingSelectSql = ""): string
    {
        $withSql = /** @lang PostgreSQL */<<<'SQL'
        WITH
            pr AS (
                SELECT
                    ee.id   AS ee_id,
                    ea.id   AS ea_id,
                    ea.code AS ea_code
                FROM %%eav_entities_tbl%% ee
                CROSS JOIN %%eav_attributes_tbl%% ea
                WHERE
                    ee.type_id = :eav_ee_type_id
                    AND ea.type_id = :eav_ee_type_id
                    %%optimization_where_sql%%
            ),
            mapped_val AS (
                SELECT
                    pr.ee_id         AS ei,
                    pr.ea_id         AS ai,
                    pr.ea_code       AS code,
                    ev.value_varchar AS vv,
                    ev.value_text    AS vt,
                    ev.value_integer AS vi,
                    ev.value_numeric AS vn,
                    ev.value_bool    AS vb,
                    ev.value_date    AS vd,
                    NULL             AS notval%%sorting_select_sql%%
                FROM pr
                LEFT JOIN %%eav_values_tbl%% ev ON ev.attribute_id = pr.ea_id AND ev.entity_id = pr.ee_id
            )
        SQL;

        $optimizationString = "";
        $optiAttributesCodes = $ctx->getSelectedAttributesCodes();

        if (is_array($optiAttributesCodes) && !empty($optiAttributesCodes)) {
            $optimizationString .= "AND ea.code IN(";
            $codePlaceholders = [];

            foreach ($optiAttributesCodes as $i => $attributeCode) {
                $codePlaceholders[] = ":eav_opti_ea_code". $i;
                $ctx->bind("eav_opti_ea_code". $i, $attributeCode);
            }

            $optimizationString .= (implode(", ", $codePlaceholders) . ")");
        }

        $withSql = str_replace("%%optimization_where_sql%%", $optimizationString, $withSql);
        $ctx->bind(
            "eav_ee_type_id",
            $this->materializer->getTypeIdByCode($query->getEntityTypeCode()),
        );

        return str_replace(
            [
                "%%eav_entities_tbl%%",
                "%%eav_attributes_tbl%%",
                "%%eav_values_tbl%%",
                "%%sorting_select_sql%%",
                "%%optimization_where_sql%%",
            ],
            [
                $this->entitiesTable,
                $this->attributesTable,
                $this->valuesTable,
                $sortingSelectSql,
                $optimizationString,
            ],
            $withSql,
        );
    }

    public function getCountSql(string $withSql, string $whereStr): string
    {
        return $withSql . "\n" . "SELECT count(*) as cnt FROM (SELECT ee.id
            FROM $this->entitiesTable ee
            JOIN mapped_val ON ee.id = mapped_val.ei
            WHERE ee.id IN (SELECT ei FROM mapped_val) $whereStr GROUP BY ee.id) as CT";
    }
}
