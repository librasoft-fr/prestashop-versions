<?php


namespace Opis\JsonSchema\Parsers\Drafts;

use Opis\JsonSchema\Parsers\Draft;
use Opis\JsonSchema\Parsers\KeywordParser;
use Opis\JsonSchema\Parsers\Keywords\{AdditionalItemsKeywordParser,
    AdditionalPropertiesKeywordParser,
    AllOfKeywordParser,
    AnyOfKeywordParser,
    ConstKeywordParser,
    ContainsKeywordParser,
    ContentEncodingKeywordParser,
    ContentMediaTypeKeywordParser,
    ContentSchemaKeywordParser,
    DefaultKeywordParser,
    DependenciesKeywordParser,
    DependentRequiredKeywordParser,
    DependentSchemasKeywordParser,
    EnumKeywordParser,
    ExclusiveMaximumKeywordParser,
    ExclusiveMinimumKeywordParser,
    FormatKeywordParser,
    IfThenElseKeywordParser,
    ItemsKeywordParser,
    MaximumKeywordParser,
    MaxItemsKeywordParser,
    MaxLengthKeywordParser,
    MaxPropertiesKeywordParser,
    MinimumKeywordParser,
    MinItemsKeywordParser,
    MinLengthKeywordParser,
    MinPropertiesKeywordParser,
    MultipleOfKeywordParser,
    NotKeywordParser,
    OneOfKeywordParser,
    PatternKeywordParser,
    PatternPropertiesKeywordParser,
    PropertiesKeywordParser,
    PropertyNamesKeywordParser,
    RefKeywordParser,
    RequiredKeywordParser,
    TypeKeywordParser,
    UnevaluatedItemsKeywordParser,
    UnevaluatedPropertiesKeywordParser,
    UniqueItemsKeywordParser};

class Draft201909 extends Draft
{
    
    public function version(): string
    {
        return '2019-09';
    }

    public function allowKeywordsAlongsideRef(): bool
    {
        return true;
    }

    
    public function supportsAnchorId(): bool
    {
        return true;
    }

    
    protected function getRefKeywordParser(): KeywordParser
    {
        return new RefKeywordParser('$ref', [
            ['ref' => '$recursiveRef', 'anchor' => '$recursiveAnchor', 'fragment' => false],
        ]);
    }

    
    protected function getKeywordParsers(): array
    {
        return [
            
            new TypeKeywordParser('type'),
            new ConstKeywordParser('const'),
            new EnumKeywordParser('enum'),
            new FormatKeywordParser('format'),

            
            new MinLengthKeywordParser('minLength'),
            new MaxLengthKeywordParser('maxLength'),
            new PatternKeywordParser("pattern"),
            new ContentEncodingKeywordParser('contentEncoding'),
            new ContentMediaTypeKeywordParser('contentMediaType'),
            new ContentSchemaKeywordParser('contentSchema'),

            
            new MinimumKeywordParser('minimum', 'exclusiveMinimum'),
            new MaximumKeywordParser('maximum', 'exclusiveMaximum'),
            new ExclusiveMinimumKeywordParser('exclusiveMinimum'),
            new ExclusiveMaximumKeywordParser('exclusiveMaximum'),
            new MultipleOfKeywordParser('multipleOf'),

            
            new MinItemsKeywordParser('minItems'),
            new MaxItemsKeywordParser('maxItems'),
            new UniqueItemsKeywordParser('uniqueItems'),
            new ContainsKeywordParser('contains', 'minContains', 'maxContains'),
            new ItemsKeywordParser('items'),
            new AdditionalItemsKeywordParser('additionalItems'),

            
            new MinPropertiesKeywordParser('minProperties'),
            new MaxPropertiesKeywordParser('maxProperties'),
            new RequiredKeywordParser('required'),
            new DependenciesKeywordParser('dependencies'), 
            new DependentRequiredKeywordParser('dependentRequired'),
            new DependentSchemasKeywordParser('dependentSchemas'),
            new PropertyNamesKeywordParser('propertyNames'),
            new PropertiesKeywordParser('properties'),
            new PatternPropertiesKeywordParser('patternProperties'),
            new AdditionalPropertiesKeywordParser('additionalProperties'),

            
            new IfThenElseKeywordParser('if', 'then', 'else'),
            new AnyOfKeywordParser('anyOf'),
            new AllOfKeywordParser('allOf'),
            new OneOfKeywordParser('oneOf'),
            new NotKeywordParser('not'),

            
            new UnevaluatedPropertiesKeywordParser('unevaluatedProperties'),
            new UnevaluatedItemsKeywordParser('unevaluatedItems'),

            
            new DefaultKeywordParser('default'),
        ];
    }

}