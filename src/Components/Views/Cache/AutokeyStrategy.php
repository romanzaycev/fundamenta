<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Views\Cache;

enum AutokeyStrategy : string
{
    case NONE = "none";
    case TEMPLATE_NAME = "template_name";
    case TEMPLATE_NAME_AND_DATA = "template_name_and_data";
}
