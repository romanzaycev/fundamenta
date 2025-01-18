<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Eav;

enum Order : string
{
    case DESC = "DESC";
    case DESC_NULLS = "DESC NULLS LAST";
    case NULLS_DESC = "DESC NULLS FIRST";

    case ASC = "ASC";
    case ASC_NULLS = "ASC NULLS LAST";
    case NULLS_ASC = "ASC NULLS FIRST";
}
