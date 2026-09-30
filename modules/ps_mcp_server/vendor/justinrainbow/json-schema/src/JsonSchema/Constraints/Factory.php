<?php

declare(strict_types=1);



namespace JsonSchema\Constraints;

use JsonSchema\DraftIdentifiers;
use JsonSchema\Exception\InvalidArgumentException;
use JsonSchema\SchemaStorage;
use JsonSchema\SchemaStorageInterface;
use JsonSchema\Uri\UriRetriever;
use JsonSchema\UriRetrieverInterface;
use JsonSchema\Validator;


class Factory
{
    
    protected $schemaStorage;

    
    protected $uriRetriever;

    
    private $checkMode = Constraint::CHECK_MODE_NORMAL;

    
    private $typeCheck = [];

    
    protected $errorContext = Validator::ERROR_DOCUMENT_VALIDATION;

    
    private $defaultDialect = DraftIdentifiers::DRAFT_6;

    
    protected $constraintMap = [
        'array' => 'JsonSchema\Constraints\CollectionConstraint',
        'collection' => 'JsonSchema\Constraints\CollectionConstraint',
        'object' => 'JsonSchema\Constraints\ObjectConstraint',
        'type' => 'JsonSchema\Constraints\TypeConstraint',
        'undefined' => 'JsonSchema\Constraints\UndefinedConstraint',
        'string' => 'JsonSchema\Constraints\StringConstraint',
        'number' => 'JsonSchema\Constraints\NumberConstraint',
        'enum' => 'JsonSchema\Constraints\EnumConstraint',
        'const' => 'JsonSchema\Constraints\ConstConstraint',
        'format' => 'JsonSchema\Constraints\FormatConstraint',
        'schema' => 'JsonSchema\Constraints\SchemaConstraint',
        'validator' => 'JsonSchema\Validator',
        'draft06' => Drafts\Draft06\Draft06Constraint::class,
        'draft07' => Drafts\Draft07\Draft07Constraint::class,
        'draft2019-09' => Drafts\Draft2019\Draft2019Constraint::class,
    ];

    
    private $instanceCache = [];

    
    public function __construct(
        ?SchemaStorageInterface $schemaStorage = null,
        ?UriRetrieverInterface $uriRetriever = null,
        int $checkMode = Constraint::CHECK_MODE_NORMAL
    ) {
        
        $this->setConfig($checkMode);

        $this->uriRetriever = $uriRetriever ?: new UriRetriever();
        $this->schemaStorage = $schemaStorage ?: new SchemaStorage($this->uriRetriever);
    }

    
    public function setConfig(int $checkMode = Constraint::CHECK_MODE_NORMAL): void
    {
        $this->checkMode = $checkMode;
    }

    
    public function addConfig(int $options): void
    {
        $this->checkMode |= $options;
    }

    
    public function removeConfig(int $options): void
    {
        $this->checkMode &= ~$options;
    }

    
    public function getConfig(?int $options = null): int
    {
        if ($options === null) {
            return $this->checkMode;
        }

        return $this->checkMode & $options;
    }

    public function getUriRetriever(): UriRetrieverInterface
    {
        return $this->uriRetriever;
    }

    public function getSchemaStorage(): SchemaStorageInterface
    {
        return $this->schemaStorage;
    }

    public function getTypeCheck(): TypeCheck\TypeCheckInterface
    {
        if (!isset($this->typeCheck[$this->checkMode])) {
            $this->typeCheck[$this->checkMode] = ($this->checkMode & Constraint::CHECK_MODE_TYPE_CAST)
                ? new TypeCheck\LooseTypeCheck()
                : new TypeCheck\StrictTypeCheck();
        }

        return $this->typeCheck[$this->checkMode];
    }

    public function setConstraintClass(string $name, string $class): Factory
    {
        
        if (!class_exists($class)) {
            throw new InvalidArgumentException('Unknown constraint ' . $name);
        }
        
        if (!in_array('JsonSchema\Constraints\ConstraintInterface', class_implements($class))) {
            throw new InvalidArgumentException('Invalid class ' . $name);
        }
        $this->constraintMap[$name] = $class;

        return $this;
    }

    
    public function createInstanceFor($constraintName)
    {
        if (!isset($this->constraintMap[$constraintName])) {
            throw new InvalidArgumentException('Unknown constraint ' . $constraintName);
        }

        if (!isset($this->instanceCache[$constraintName])) {
            $this->instanceCache[$constraintName] = new $this->constraintMap[$constraintName]($this);
        }

        return clone $this->instanceCache[$constraintName];
    }

    
    public function getErrorContext(): int
    {
        return $this->errorContext;
    }

    
    public function setErrorContext(int $errorContext): void
    {
        $this->errorContext = $errorContext;
    }

    public function getDefaultDialect(): string
    {
        return $this->defaultDialect;
    }

    public function setDefaultDialect(string $defaultDialect): void
    {
        $this->defaultDialect = $defaultDialect;
    }
}
