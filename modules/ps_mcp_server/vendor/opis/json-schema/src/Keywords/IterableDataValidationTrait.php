<?php


namespace Opis\JsonSchema\Keywords;

use ArrayObject;
use Opis\JsonSchema\{ValidationContext, Schema};
use Opis\JsonSchema\Errors\{ValidationError, ErrorContainer};

trait IterableDataValidationTrait
{
    use ErrorTrait;

    
    protected function errorContainer(int $maxErrors = 1): ErrorContainer
    {
        return new ErrorContainer($maxErrors);
    }

    
    protected function iterateAndValidate(
        Schema $schema,
        ValidationContext $context,
        iterable $iterator,
        ?ArrayObject $keys = null
    ): ErrorContainer {
        $container = $this->errorContainer($context->maxErrors());

        if ($keys) {
            foreach ($iterator as $key) {
                $context->pushDataPath($key);
                $error = $schema->validate($context);
                $context->popDataPath();

                if ($error) {
                    if (!$container->isFull()) {
                        $container->add($error);
                    }
                } else {
                    $keys[] = $key;
                }
            }
        } else {
            foreach ($iterator as $key) {
                $context->pushDataPath($key);
                $error = $schema->validate($context);
                $context->popDataPath();

                if ($error && $container->add($error)->isFull()) {
                    break;
                }
            }
        }

        return $container;
    }

    
    protected function validateIterableData(
        Schema $parentSchema,
        Schema $schema,
        ValidationContext $context,
        iterable $iterator,
        string $keyword,
        string $message,
        array $args = [],
        ?ArrayObject $visited_keys = null
    ): ?ValidationError {
        $errors = $this->iterateAndValidate($schema, $context, $iterator, $visited_keys);

        if ($errors->isEmpty()) {
            return null;
        }

        return $this->error($parentSchema, $context, $keyword, $message, $args, $errors);
    }
}