<?php


namespace Opis\JsonSchema\Parsers;

use Opis\JsonSchema\{
    Keyword, KeywordValidator, Schema, Uri
};
use Opis\JsonSchema\Schemas\{
    BooleanSchema, EmptySchema, ExceptionSchema, ObjectSchema
};
use Opis\JsonSchema\Resolvers\{
    FilterResolver,
    FormatResolver,
    ContentMediaTypeResolver,
    ContentEncodingResolver
};
use Opis\JsonSchema\Parsers\Drafts\{Draft06, Draft07, Draft201909, Draft202012};
use Opis\JsonSchema\Exceptions\{ParseException, SchemaException};
use Opis\JsonSchema\Info\SchemaInfo;

class SchemaParser
{
    protected const DRAFT_REGEX = '~^https?://json-schema\.org/draft(?:/|-)(\d[0-9-]*\d)/schema#?$~i';
    protected const ANCHOR_REGEX = '/^[a-z][a-z0-9\\-.:_]*/i';
    protected const DEFAULT_DRAFT = '2020-12';

    
    protected const DEFAULT_OPTIONS = [
        'allowFilters' => true,
        'allowFormats' => true,
        'allowMappers' => true,
        'allowTemplates' => true,
        'allowGlobals' => true,
        'allowDefaults' => true,
        'allowSlots' => true,
        'allowKeywordValidators' => true,
        'allowPragmas' => true,

        'allowDataKeyword' => true,
        'allowKeywordsAlongsideRef' => false,
        'allowUnevaluated' => true,
        'allowRelativeJsonPointerInRef' => true,
        'allowExclusiveMinMaxAsBool' => true,

        'keepDependenciesKeyword' => true,
        'keepAdditionalItemsKeyword' => true,

        'decodeContent' => ['06', '07'],
        'defaultDraft' => self::DEFAULT_DRAFT,

        'varRefKey' => '$ref',
        'varEachKey' => '$each',
        'varDefaultKey' => 'default',
    ];

    
    protected array $options;

    
    protected array $drafts;

    
    protected array $resolvers;

    
    public function __construct(
        array $resolvers = [],
        array $options = [],
        ?Vocabulary $extraVocabulary = null
    )
    {
        if ($options) {
            $this->options = $options + self::DEFAULT_OPTIONS;
        } else {
            $this->options = self::DEFAULT_OPTIONS;
        }

        $this->resolvers = $this->getResolvers($resolvers);

        $this->drafts = $this->getDrafts($extraVocabulary ?? new DefaultVocabulary());
    }

    
    protected function getDrafts(?Vocabulary $extraVocabulary): array
    {
        return [
            '06' => new Draft06($extraVocabulary),
            '07' => new Draft07($extraVocabulary),
            '2019-09' => new Draft201909($extraVocabulary),
            '2020-12' => new Draft202012($extraVocabulary),
        ];
    }

    
    protected function getResolvers(array $resolvers): array
    {
        if (!array_key_exists('format', $resolvers)) {
            $resolvers['format'] = new FormatResolver();
        }

        if (!array_key_exists('contentEncoding', $resolvers)) {
            $resolvers['contentEncoding'] = new ContentEncodingResolver();
        }

        if (!array_key_exists('contentMediaType', $resolvers)) {
            $resolvers['contentMediaType'] = new ContentMediaTypeResolver();
        }

        if (!array_key_exists('$filters', $resolvers)) {
            $resolvers['$filters'] = new FilterResolver();
        }

        return $resolvers;
    }

    
    public function option(string $name, $default = null)
    {
        return $this->options[$name] ?? $default;
    }

    
    public function setOption(string $name, $value): self
    {
        $this->options[$name] = $value;

        return $this;
    }

    
    public function getOptions(): array
    {
        return $this->options;
    }

    
    public function setResolver(string $name, $resolver): self
    {
        $this->resolvers[$name] = $resolver;

        return $this;
    }

    
    public function getFilterResolver(): ?FilterResolver
    {
        return $this->getResolver('$filters');
    }

    
    public function setFilterResolver(?FilterResolver $resolver): self
    {
        return $this->setResolver('$filters', $resolver);
    }

    
    public function getFormatResolver(): ?FormatResolver
    {
        return $this->getResolver('format');
    }

    
    public function setFormatResolver(?FormatResolver $resolver): self
    {
        return $this->setResolver('format', $resolver);
    }

    
    public function getContentEncodingResolver(): ?ContentEncodingResolver
    {
        return $this->getResolver('contentEncoding');
    }

    
    public function setContentEncodingResolver(?ContentEncodingResolver $resolver): self
    {
        return $this->setResolver('contentEncoding', $resolver);
    }

    
    public function getMediaTypeResolver(): ?ContentMediaTypeResolver
    {
        return $this->getResolver('contentMediaType');
    }

    
    public function setMediaTypeResolver(?ContentMediaTypeResolver $resolver): self
    {
        return $this->setResolver('contentMediaType', $resolver);
    }

    
    public function defaultDraftVersion(): string
    {
        return $this->option('defaultDraft', self::DEFAULT_DRAFT);
    }

    
    public function setDefaultDraftVersion(string $draft): self
    {
        return $this->setOption('defaultDraft', $draft);
    }

    
    public function parseDraftVersion(string $schema): ?string
    {
        if (!preg_match(self::DRAFT_REGEX, $schema, $m)) {
            return null;
        }

        return $m[1] ?? null;
    }

    
    public function parseId(object $schema): ?string
    {
        if (property_exists($schema, '$id') && is_string($schema->{'$id'})) {
            return $schema->{'$id'};
        }

        return null;
    }

    
    public function parseAnchor(object $schema, string $draft): ?string
    {
        if (!property_exists($schema, '$anchor') ||
            !isset($this->drafts[$draft]) ||
            !$this->drafts[$draft]->supportsAnchorId()) {
            return null;
        }

        $anchor = $schema->{'$anchor'};

        if (!is_string($anchor) || !preg_match(self::ANCHOR_REGEX, $anchor)) {
            return null;
        }

        return $anchor;
    }

    
    public function parseSchemaDraft(object $schema): ?string
    {
        if (!property_exists($schema, '$schema') || !is_string($schema->{'$schema'})) {
            return null;
        }

        return $this->parseDraftVersion($schema->{'$schema'});
    }

    
    public function parseRootSchema(
        object $schema,
        Uri $id,
        callable $handle_id,
        callable $handle_object,
        ?string $draft = null
    ): ?Schema
    {
        $existent = false;
        if (property_exists($schema, '$id')) {
            $existent = true;
            $id = Uri::parse($schema->{'$id'}, true);
        }

        if ($id instanceof Uri) {
            if ($id->fragment() === null) {
                $id = Uri::merge($id, null, true);
            }
        } else {
            throw new ParseException('Root schema id must be an URI', new SchemaInfo($schema, $id));
        }

        if (!$id->isAbsolute()) {
            throw new ParseException('Root schema id must be an absolute URI', new SchemaInfo($schema, $id));
        }

        if ($id->fragment() !== '') {
            throw new ParseException('Root schema id must have an empty fragment or none', new SchemaInfo($schema, $id));
        }

        
        if ($resolved = $handle_id($id)) {
            return $resolved;
        }

        if (property_exists($schema, '$schema')) {
            if (!is_string($schema->{'$schema'})) {
                throw new ParseException('Schema draft must be a string', new SchemaInfo($schema, $id));
            }
            $draft = $this->parseDraftVersion($schema->{'$schema'});
        }

        if ($draft === null) {
            $draft = $this->defaultDraftVersion();
        }

        if (!$existent) {
            $schema->{'$id'} = (string)$id;
        }

        $resolved = $handle_object($schema, $id, $draft);

        if (!$existent) {
            unset($schema->{'$id'});
        }

        return $resolved;
    }

    
    public function parseSchema(SchemaInfo $info): Schema
    {
        if ($info->isBoolean()) {
            return new BooleanSchema($info);
        }

        try {
            return $this->parseSchemaObject($info);
        } catch (SchemaException $exception) {
            return new ExceptionSchema($info, $exception);
        }
    }

    
    public function draft(string $version): ?Draft
    {
        return $this->drafts[$version] ?? null;
    }

    
    public function addDraft(Draft $draft): self
    {
        $this->drafts[$draft->version()] = $draft;

        return $this;
    }

    
    public function supportedDrafts(): array
    {
        return array_keys($this->drafts);
    }

    
    protected function setOptions(array $options): self
    {
        $this->options = $options + $this->options;

        return $this;
    }

    
    protected function getResolver(string $name)
    {
        $resolver = $this->resolvers[$name] ?? null;

        if (!is_object($resolver)) {
            return null;
        }

        return $resolver;
    }

    
    protected function parseSchemaObject(SchemaInfo $info): Schema
    {
        $draftObject = $this->draft($info->draft());

        if ($draftObject === null) {
            throw new ParseException("Unsupported draft-{$info->draft()}", $info);
        }

        
        $schema = $info->data();

        
        if (property_exists($schema, '$id')) {
            $id = $info->id();
            if ($id === null || !$id->isAbsolute()) {
                throw new ParseException('Schema id must be a valid URI', $info);
            }
        }

        if ($hasRef = property_exists($schema, '$ref')) {
            if ($this->option('allowKeywordsAlongsideRef') || $draftObject->allowKeywordsAlongsideRef()) {
                $hasRef = false;
            }
        }

        $shared = (object) [];

        if ($this->option('allowKeywordValidators')) {
            $keywordValidator = $this->parseKeywordValidators($info, $draftObject->keywordValidators(), $shared);
        } else {
            $keywordValidator = null;
        }

        return $this->parseSchemaKeywords($info, $keywordValidator, $draftObject->keywords(), $shared, $hasRef);
    }

    
    protected function parseKeywordValidators(SchemaInfo $info, array $keywordValidators, object $shared): ?KeywordValidator
    {
        $last = null;

        while ($keywordValidators) {
            
            $keywordValidator = array_pop($keywordValidators);
            if ($keywordValidator && ($keyword = $keywordValidator->parse($info, $this, $shared))) {
                $keyword->setNext($last);
                $last = $keyword;
                unset($keyword);
            }
            unset($keywordValidator);
        }

        return $last;
    }

    
    protected function parseSchemaKeywords(SchemaInfo $info, ?KeywordValidator $keywordValidator,
                                           array $parsers, object $shared, bool $hasRef = false): Schema
    {
        
        $prepend = [];
        
        $append = [];
        
        $before = [];
        
        $after = [];
        
        $types = [];
        
        $ref = [];

        if ($hasRef) {
            foreach ($parsers as $parser) {
                $kType = $parser->type();

                if ($kType === KeywordParser::TYPE_APPEND) {
                    $container = &$append;
                } elseif ($kType === KeywordParser::TYPE_AFTER_REF) {
                    $container = &$ref;
                } elseif ($kType === KeywordParser::TYPE_PREPEND) {
                    $container = &$prepend;
                } else {
                    continue;
                }

                if ($keyword = $parser->parse($info, $this, $shared)) {
                    $container[] = $keyword;
                }

                unset($container, $keyword, $kType);
            }
        } else {
            foreach ($parsers as $parser) {
                $keyword = $parser->parse($info, $this, $shared);
                if ($keyword === null) {
                    continue;
                }

                $kType = $parser->type();

                switch ($kType) {
                    case KeywordParser::TYPE_PREPEND:
                        $prepend[] = $keyword;
                        break;
                    case KeywordParser::TYPE_APPEND:
                        $append[] = $keyword;
                        break;
                    case KeywordParser::TYPE_BEFORE:
                        $before[] = $keyword;
                        break;
                    case KeywordParser::TYPE_AFTER:
                        $after[] = $keyword;
                        break;
                    case KeywordParser::TYPE_AFTER_REF:
                        $ref[] = $keyword;
                        break;
                    default:
                        if (!isset($types[$kType])) {
                            $types[$kType] = [];
                        }
                        $types[$kType][] = $keyword;
                        break;

                }
            }
        }

        unset($shared);

        if ($prepend) {
            $before = array_merge($prepend, $before);
        }
        unset($prepend);

        if ($ref) {
            $after = array_merge($after, $ref);
        }
        unset($ref);

        if ($append) {
            $after = array_merge($after, $append);
        }
        unset($append);

        if (empty($before)) {
            $before = null;
        }
        if (empty($after)) {
            $after = null;
        }
        if (empty($types)) {
            $types = null;
        }

        if (empty($types) && empty($before) && empty($after)) {
            return new EmptySchema($info, $keywordValidator);
        }

        return new ObjectSchema($info, $keywordValidator, $types, $before, $after);
    }
}