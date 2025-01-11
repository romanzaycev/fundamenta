<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav\Impl\Pgsql;

use Cycle\Database\DatabaseInterface;
use Psr\Log\LoggerInterface;
use Romanzaycev\Fundamenta\Components\Eav\Exceptions\QueryException;
use Romanzaycev\Fundamenta\Components\Eav\Executor;
use Romanzaycev\Fundamenta\Components\Eav\Query;
use Romanzaycev\Fundamenta\Components\Eav\QueryBuilder\LogicCompiler;
use Romanzaycev\Fundamenta\Components\Eav\Row;
use Romanzaycev\Fundamenta\Components\Eav\RowSet;
use Romanzaycev\Fundamenta\Configuration;

readonly class PgsqlExecutor implements Executor
{
    private string $valuesTable;
    private string $attributesTable;
    private string $entitiesTable;

    public function __construct(
        private Configuration $configuration,
        private Materializer $materializer,
        private DatabaseInterface $database,
        private LoggerInterface $logger,
    )
    {
        $tablesCfg = $this->configuration->get("eav.schema.tables");
        $this->valuesTable = $tablesCfg["value"];
        $this->attributesTable = $tablesCfg["attribute"];
        $this->entitiesTable = $tablesCfg["entity"];
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

        $withSql = /** @lang PostgreSQL */<<<'SQL'
        WITH mapped_val AS (SELECT ev.entity_id as ei,
           ev.attribute_id  as ai,
           ea.code          as code,
           ev.value_varchar as vv,
           ev.value_text    as vt,
           ev.value_integer as vi,
           ev.value_numeric as vn,
           ev.value_bool    as vb,
           ev.value_date    as vd,
           NULL             as notval
        FROM %s ev
        JOIN %s ea on ea.id = ev.attribute_id
        JOIN %s ee on ee.id = ea.entity_id
        WHERE ee.type = :eav_ee_type %s)
        SQL;
        $optimizationString = "";
        $optiAttributesCodes = $ctx->getAttributesCodes();

        if (is_array($optiAttributesCodes) && !empty($optiAttributesCodes)) {
            $optimizationString .= " AND ea.code IN(";
            $codePlaceholders = [];

            foreach ($optiAttributesCodes as $i => $attributeCode) {
                $codePlaceholders[] = ":eav_opti_ea". $i;
                $ctx->bind("eav_opti_ea". $i, $attributeCode);
            }

            $optimizationString .= (implode(", ", $codePlaceholders) . ")");
        }

        $withSql = sprintf(
            $withSql,
            $this->valuesTable,
            $this->attributesTable,
            $this->entitiesTable,
            $optimizationString,
        );
        $ctx->bind("eav_ee_type", $query->getEntityType());
        $selectSql = /** @lang PostgreSQL */<<<'SQL'
        SELECT ee.id as id,
               ee.type as type,
               ee.created_at as created_at,
               ee.updated_at as updated_at,
               json_agg(json_build_object('code', mapped_val.code, 'value',
                   COALESCE(mapped_val.vv, mapped_val.vt, cast(mapped_val.vi as varchar),
                       cast(mapped_val.vn as varchar), cast(mapped_val.vb as varchar),
                       cast(mapped_val.vd as varchar)))) AS values
        FROM %s ee
        JOIN mapped_val ON ee.id = mapped_val.ei
        WHERE ee.id IN (SELECT ei FROM mapped_val)
        SQL;
        $selectSql = sprintf($selectSql, $this->entitiesTable);

        if (!empty($whereStr)) {
            $whereStr = "\nAND " . $whereStr;
        }

        $sql = $withSql . "\n" . $selectSql . $whereStr;
        $sql .= "\nGROUP BY ee.id, ee.type, ee.created_at, ee.updated_at";
        $limit = $query->getLimit();
        $offset = $query->getOffset();
        $nonPaginatedBindings = $ctx->getBindings();
        $countSql = $withSql . "\n" . "SELECT count(*) as cnt FROM (SELECT ee.id
        FROM $this->entitiesTable ee
        JOIN mapped_val ON ee.id = mapped_val.ei
        WHERE ee.id IN (SELECT ei FROM mapped_val) $whereStr GROUP BY ee.id) as CT";

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
                $query->getEntityType(),
                $this->materializer,
                $this->logger,
            ),
        );
    }
}
