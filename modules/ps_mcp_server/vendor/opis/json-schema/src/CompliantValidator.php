<?php


namespace Opis\JsonSchema;

class CompliantValidator extends Validator
{
    protected const COMPLIANT_OPTIONS = [
        'allowFilters' => false,
        'allowFormats' => true,
        'allowMappers' => false,
        'allowTemplates' => false,
        'allowGlobals' => false,
        'allowDefaults' => false,
        'allowSlots' => false,
        'allowKeywordValidators' => false,
        'allowPragmas' => false,
        'allowDataKeyword' => false,
        'allowKeywordsAlongsideRef' => false,
        'allowUnevaluated' => true,
        'allowRelativeJsonPointerInRef' => false,
        'allowExclusiveMinMaxAsBool' => false,
        'keepDependenciesKeyword' => false,
        'keepAdditionalItemsKeyword' => false,
    ];

    public function __construct(
        ?SchemaLoader $loader = null,
        int $max_errors = 1,
        bool $stop_at_first_error = true
    )
    {
        parent::__construct($loader, $max_errors, $stop_at_first_error);

        
        $parser = $this->parser();
        foreach (static::COMPLIANT_OPTIONS as $name => $value) {
            $parser->setOption($name, $value);
        }
    }
}
