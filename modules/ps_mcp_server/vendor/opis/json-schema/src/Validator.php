<?php


namespace Opis\JsonSchema;

use InvalidArgumentException, RuntimeException;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Resolvers\{SchemaResolver};

class Validator
{
    protected SchemaLoader $loader;
    protected int $maxErrors = 1;
    protected bool $stopAtFirstError = true;

    
    public function __construct(
        ?SchemaLoader $loader = null,
        int $max_errors = 1,
        bool $stop_at_first_error = true
    )
    {
        $this->loader = $loader ?? new SchemaLoader(new SchemaParser(), new SchemaResolver(), true);
        $this->maxErrors = $max_errors;
        $this->stopAtFirstError = $stop_at_first_error;
    }

    
    public function validate($data, $schema, ?array $globals = null, ?array $slots = null): ValidationResult
    {
        if (is_string($schema)) {
            if ($uri = Uri::parse($schema, true)) {
                $schema = $uri;
            } else {
                $schema = json_decode($schema, false);
            }
        }

        $error = null;
        if (is_bool($schema)) {
            $error = $this->dataValidation($data, $schema, $globals, $slots);
        } elseif (is_object($schema)) {
            if ($schema instanceof Uri) {
                $error = $this->uriValidation($data, $schema, $globals, $slots);
            } elseif ($schema instanceof Schema) {
                $error = $this->schemaValidation($data, $schema, $globals, $slots);
            } else {
                $error = $this->dataValidation($data, $schema, $globals, $slots);
            }
        } else {
            throw new InvalidArgumentException("Invalid schema");
        }

        return new ValidationResult($error);
    }

    
    public function uriValidation($data, $uri, ?array $globals = null, ?array $slots = null): ?ValidationError
    {
        if (is_string($uri)) {
            $uri = Uri::parse($uri, true);
        }

        if (!($uri instanceof Uri)) {
            throw new InvalidArgumentException("Invalid uri");
        }

        if ($uri->fragment() === null) {
            $uri = Uri::merge($uri, null, true);
        }

        $schema = $this->loader->loadSchemaById($uri);

        if ($schema === null) {
            throw new RuntimeException("Schema not found: $uri");
        }

        return $this->schemaValidation($data, $schema, $globals, $slots);
    }

    
    public function dataValidation(
        $data,
        $schema,
        ?array $globals = null,
        ?array $slots = null,
        ?string $id = null,
        ?string $draft = null
    ): ?ValidationError
    {
        if (is_string($schema)) {
            $schema = json_decode($schema, false);
        }

        if ($schema === true) {
            return null;
        }

        if ($schema === false) {
            $schema = $this->loader->loadBooleanSchema(false, $id, $draft);
        } else {
            if (!is_object($schema)) {
                throw new InvalidArgumentException("Invalid schema");
            }

            $schema = $this->loader->loadObjectSchema($schema, $id, $draft);
        }

        return $this->schemaValidation($data, $schema, $globals, $slots);
    }

    
    public function schemaValidation(
        $data,
        Schema $schema,
        ?array $globals = null,
        ?array $slots = null
    ): ?ValidationError
    {
        return $schema->validate($this->createContext($data, $globals, $slots));
    }

    
    public function createContext($data, ?array $globals = null, ?array $slots = null): ValidationContext
    {
        if ($slots) {
            $slots = $this->parseSlots($slots);
        }

        return new ValidationContext(
            $data,
            $this->loader,
            null,
            null,
                $globals ?? [],
            $slots,
            $this->maxErrors,
            $this->stopAtFirstError,
        );
    }

    
    public function parser(): SchemaParser
    {
        return $this->loader->parser();
    }

    
    public function setParser(SchemaParser $parser): self
    {
        $this->loader->setParser($parser);

        return $this;
    }

    
    public function resolver(): ?SchemaResolver
    {
        return $this->loader->resolver();
    }

    
    public function setResolver(?SchemaResolver $resolver): self
    {
        $this->loader->setResolver($resolver);

        return $this;
    }

    
    public function loader(): SchemaLoader
    {
        return $this->loader;
    }

    
    public function setLoader(SchemaLoader $loader): self
    {
        $this->loader = $loader;

        return $this;
    }

    
    public function getMaxErrors(): int
    {
        return $this->maxErrors;
    }

    
    public function setMaxErrors(int $max_errors): self
    {
        $this->maxErrors = $max_errors;

        return $this;
    }

    
    public function getStopAtFirstError(): bool
    {
        return $this->stopAtFirstError;
    }

    
    public function setStopAtFirstError(bool $stop): self
    {
        $this->stopAtFirstError = $stop;
        return $this;
    }

    
    protected function parseSlots(array $slots): array
    {
        foreach ($slots as $name => &$value) {
            if (!is_string($name)) {
                unset($slots[$name]);
                continue;
            }

            if (is_string($value)) {
                $value = Uri::parse($value, true);
            }

            if ($value instanceof Uri) {
                $value = $this->loader->loadSchemaById($value);
            } elseif (is_bool($value)) {
                $value = $this->loader->loadBooleanSchema($value);
            }

            if (!is_object($value)) {
                unset($slots[$name]);
            }

            unset($value);
        }

        return $slots;
    }
}