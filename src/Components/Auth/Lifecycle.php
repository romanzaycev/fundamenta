<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Auth;

enum Lifecycle : int
{
    case PERSISTENT = 1;
    case PER_REQUEST = 2;
}
