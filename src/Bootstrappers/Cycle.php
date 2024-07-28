<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Bootstrappers;

use Cycle\Annotated;
use Cycle\Annotated\Locator\TokenizerEmbeddingLocator;
use Cycle\Annotated\Locator\TokenizerEntityLocator;
use Cycle\Database\DatabaseManager;
use Cycle\ORM\SchemaInterface;
use Cycle\Schema;
use DI\Container;
use DI\ContainerBuilder;
use Romanzaycev\Fundamenta\Configuration;
use Romanzaycev\Fundamenta\ModuleBootstrapper;
use Spiral\Tokenizer\ClassLocator;
use Symfony\Component\Finder\Finder;

class Cycle extends ModuleBootstrapper
{
    public static function preconfigure(Configuration $configuration): void
    {
        $configuration->setDefaults("cycle", []);
    }

    public static function boot(ContainerBuilder $builder, Configuration $configuration): void
    {
        $builder->addDefinitions([
            SchemaInterface::class => function (Container $container) use ($configuration) {
                $finder = (new Finder())->files()->in([
                    $configuration->get("runtime.path"),
                ]);
                $classLocator = new ClassLocator($finder);
                $embeddingLocator = new TokenizerEmbeddingLocator($classLocator);
                $entityLocator = new TokenizerEntityLocator($classLocator);

                return (new Schema\Compiler())->compile(new Schema\Registry($container->get(DatabaseManager::class)), [
                    new Schema\Generator\ResetTables(),             // Reconfigure table schemas (deletes columns if necessary)
                    new Annotated\Embeddings($embeddingLocator),    // Recognize embeddable entities
                    new Annotated\Entities($entityLocator),         // Identify attributed entities
                    new Annotated\TableInheritance(),               // Setup Single Table or Joined Table Inheritance
                    new Annotated\MergeColumns(),                   // Integrate table #[Column] attributes
                    new Schema\Generator\GenerateRelations(),       // Define entity relationships
                    new Schema\Generator\GenerateModifiers(),       // Apply schema modifications
                    new Schema\Generator\ValidateEntities(),        // Ensure entity schemas adhere to conventions
                    new Schema\Generator\RenderTables(),            // Create table schemas
                    new Schema\Generator\RenderRelations(),         // Establish keys and indexes for relationships
                    new Schema\Generator\RenderModifiers(),         // Implement schema modifications
                    new Schema\Generator\ForeignKeys(),             // Define foreign key constraints
                    new Annotated\MergeIndexes(),                   // Merge table index attributes
                    new Schema\Generator\SyncTables(),              // Align table changes with the database
                    new Schema\Generator\GenerateTypecast(),        // Typecast non-string columns
                ]);
            },
        ]);
    }

    public static function requires(): array
    {
        return [
            Dbal::class,
        ];
    }
}
