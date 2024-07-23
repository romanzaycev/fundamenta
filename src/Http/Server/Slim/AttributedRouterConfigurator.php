<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Http\Server\Slim;

use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\Helpers\ClassFinder;
use Romanzaycev\Fundamenta\Http\Route;
use ReflectionClass;
use ReflectionMethod;
use Slim\App;

class AttributedRouterConfigurator
{
    /**
     * @throws \Throwable
     */
    public static function configure(App $app): void
    {
        /** @var Configuration $configuration */
        $configuration = $app->getContainer()->get(Configuration::class);
        $classes = new ClassFinder(
            $configuration->get("app.path"),
            $configuration->get("app.namespace"),
        );

        foreach ($classes as $class) {
            $reflection = new ReflectionClass($class);
            $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                if ($method->isStatic()) {
                    continue;
                }

                $routeAttributes = $method->getAttributes(Route::class);

                foreach ($routeAttributes as $routeAttribute) {
                    $routeAttrArgs = self::normalizeAttributeArguments($routeAttribute->getArguments());
                    $app->map(
                        $routeAttrArgs["method"],
                        $routeAttrArgs["pattern"],
                        sprintf("%s:%s", $class, $method->name),
                    );
                }
            }
        }
    }

    private static function normalizeAttributeArguments(array $routeAttrArgs): array
    {
        $args = [
            "pattern",
            "method",
        ];
        $result = array_fill_keys($args, null);
        $result["method"] = ["GET"];

        foreach ($args as $index => $name) {
            $result[$name] = $routeAttrArgs[$name] ?? $routeAttrArgs[$index] ?? $result[$name];
        }

        return $result;
    }
}
