<?php

namespace PsMcpServerDeps\Opis\JsonSchema\Parsers\Drafts;

use PsMcpServerDeps\Opis\JsonSchema\Parsers\Draft;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\KeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\AdditionalItemsKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\AdditionalPropertiesKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\AllOfKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\AnyOfKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\ConstKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\ContainsKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\ContentEncodingKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\ContentMediaTypeKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\ContentSchemaKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\DefaultKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\DependenciesKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\DependentRequiredKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\DependentSchemasKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\EnumKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\ExclusiveMaximumKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\ExclusiveMinimumKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\FormatKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\IfThenElseKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\ItemsKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\MaximumKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\MaxItemsKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\MaxLengthKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\MaxPropertiesKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\MinimumKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\MinItemsKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\MinLengthKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\MinPropertiesKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\MultipleOfKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\NotKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\OneOfKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\PatternKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\PatternPropertiesKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\PropertiesKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\PropertyNamesKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\RefKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\RequiredKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\TypeKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\UnevaluatedItemsKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\UnevaluatedPropertiesKeywordParser;
use PsMcpServerDeps\Opis\JsonSchema\Parsers\Keywords\UniqueItemsKeywordParser;

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
            new PatternKeywordParser('pattern'),
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
