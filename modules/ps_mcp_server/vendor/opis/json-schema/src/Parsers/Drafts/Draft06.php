<?php


namespace Opis\JsonSchema\Parsers\Drafts;

use Opis\JsonSchema\Parsers\Draft;
use Opis\JsonSchema\Parsers\KeywordParser;
use Opis\JsonSchema\Parsers\Keywords\{
    AdditionalItemsKeywordParser,
    AdditionalPropertiesKeywordParser,
    AllOfKeywordParser,
    AnyOfKeywordParser,
    ConstKeywordParser,
    ContainsKeywordParser,
    ContentEncodingKeywordParser,
    ContentMediaTypeKeywordParser,
    DefaultKeywordParser,
    DependenciesKeywordParser,
    EnumKeywordParser,
    ExclusiveMaximumKeywordParser,
    ExclusiveMinimumKeywordParser,
    FormatKeywordParser,
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
    UniqueItemsKeywordParser
};

class Draft06 extends Draft
{
    
    public function version(): string
    {
        return '06';
    }

    public function allowKeywordsAlongsideRef(): bool
    {
        return false;
    }

    
    public function supportsAnchorId(): bool
    {
        return false;
    }

    
    protected function getRefKeywordParser(): KeywordParser
    {
        return new RefKeywordParser('$ref');
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

            
            new MinimumKeywordParser('minimum', 'exclusiveMinimum'),
            new MaximumKeywordParser('maximum', 'exclusiveMaximum'),
            new ExclusiveMinimumKeywordParser('exclusiveMinimum'),
            new ExclusiveMaximumKeywordParser('exclusiveMaximum'),
            new MultipleOfKeywordParser('multipleOf'),

            
            new MinItemsKeywordParser('minItems'),
            new MaxItemsKeywordParser('maxItems'),
            new UniqueItemsKeywordParser('uniqueItems'),
            new ContainsKeywordParser('contains'),
            new ItemsKeywordParser('items'),
            new AdditionalItemsKeywordParser('additionalItems'),

            
            new MinPropertiesKeywordParser('minProperties'),
            new MaxPropertiesKeywordParser('maxProperties'),
            new RequiredKeywordParser('required'),
            new DependenciesKeywordParser('dependencies'),
            new PropertyNamesKeywordParser('propertyNames'),
            new PropertiesKeywordParser('properties'),
            new PatternPropertiesKeywordParser('patternProperties'),
            new AdditionalPropertiesKeywordParser('additionalProperties'),

            
            new NotKeywordParser('not'),
            new AnyOfKeywordParser('anyOf'),
            new AllOfKeywordParser('allOf'),
            new OneOfKeywordParser('oneOf'),

            
            new DefaultKeywordParser('default'),
        ];
    }

}