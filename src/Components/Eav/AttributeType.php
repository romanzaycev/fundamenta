<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav;

enum AttributeType : string
{
    case VARCHAR = "varchar";
    case TEXT = "text";
    case INTEGER = "integer";
    case NUMERIC = "numeric";
    case BOOL = "bool";
    case DATE_TIME = "date_time";
}
