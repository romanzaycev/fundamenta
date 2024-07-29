<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Views;

interface ViewEngineProvider
{
    public function register(EngineManager $manager): void;
}
