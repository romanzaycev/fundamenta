<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav;

enum Operator : string
{
    case EQ = "=";
    case NQ = "!=";
    case GT = ">";
    case GTE = ">=";
    case LT = "<";
    case LTE = "<=";
    case IN = "IN";
    case IS = "IS";
    case IS_NOT = "IS NOT";
}
