<?php


namespace Opis\JsonSchema\Resolvers;

use Opis\JsonSchema\{Helper, Filter};
use Opis\JsonSchema\Filters\{CommonFilters,
    DataExistsFilter,
    DateTimeFilters,
    FilterExistsFilter,
    FormatExistsFilter,
    SchemaExistsFilter,
    SlotExistsFilter,
    GlobalVarExistsFilter};

class FilterResolver
{
    
    protected array $filters = [];

    
    protected array $ns = [];

    protected string $separator;

    protected string $defaultNS;

    
    public function __construct(string $ns_separator = '::', string $default_ns = 'default')
    {
        $this->separator = $ns_separator;
        $this->defaultNS = $default_ns;

        $this->registerDefaultFilters();
    }

    
    protected function registerDefaultFilters(): void
    {
        $this->registerMultipleTypes("schema-exists", new SchemaExistsFilter());
        $this->registerMultipleTypes("data-exists", new DataExistsFilter());
        $this->registerMultipleTypes("global-exists", new GlobalVarExistsFilter());
        $this->registerMultipleTypes("slot-exists", new SlotExistsFilter());
        $this->registerMultipleTypes("filter-exists", new FilterExistsFilter());
        $this->registerMultipleTypes("format-exists", new FormatExistsFilter());

        $cls = DateTimeFilters::class . "::";
        $this->registerCallable("string", "min-date", $cls . "MinDate");
        $this->registerCallable("string", "max-date", $cls . "MaxDate");
        $this->registerCallable("string", "not-date", $cls . "NotDate");
        $this->registerCallable("string", "min-time", $cls . "MinTime");
        $this->registerCallable("string", "max-time", $cls . "MaxTime");
        $this->registerCallable("string", "min-datetime", $cls . "MinDateTime");
        $this->registerCallable("string", "max-datetime", $cls . "MaxDateTime");

        $cls = CommonFilters::class . "::";
        $this->registerCallable("string", "regex", $cls . "Regex");
        $this->registerMultipleTypes("equals", $cls . "Equals");
    }


    
    public function resolve(string $name, string $type)
    {
        [$ns, $name] = $this->parseName($name);

        if (isset($this->filters[$ns][$name])) {
            return $this->filters[$ns][$name][$type] ?? null;
        }

        if (!isset($this->ns[$ns])) {
            return null;
        }

        $this->filters[$ns][$name] = $this->ns[$ns]->resolveAll($name);

        return $this->filters[$ns][$name][$type] ?? null;
    }

    
    public function resolveAll(string $name): ?array
    {
        [$ns, $name] = $this->parseName($name);

        if (isset($this->filters[$ns][$name])) {
            return $this->filters[$ns][$name];
        }

        if (!isset($this->ns[$ns])) {
            return null;
        }

        return $this->filters[$ns][$name] = $this->ns[$ns]->resolveAll($name);
    }

    
    public function register(string $type, string $name, Filter $filter): self
    {
        [$ns, $name] = $this->parseName($name);

        $this->filters[$ns][$name][$type] = $filter;

        return $this;
    }

    
    public function unregister(string $name, ?string $type = null): bool
    {
        [$ns, $name] = $this->parseName($name);
        if (!isset($this->filters[$ns][$name])) {
            return false;
        }

        if ($type === null) {
            unset($this->filters[$ns][$name]);

            return true;
        }

        if (isset($this->filters[$ns][$name][$type])) {
            unset($this->filters[$ns][$name][$type]);

            return true;
        }

        return false;
    }

    
    public function registerMultipleTypes(string $name, $filter, ?array $types = null): self
    {
        [$ns, $name] = $this->parseName($name);

        $types = $types ?? Helper::JSON_TYPES;

        foreach ($types as $type) {
            $this->filters[$ns][$name][$type] = $filter;
        }

        return $this;
    }

    
    public function registerCallable(string $type, string $name, callable $filter): self
    {
        [$ns, $name] = $this->parseName($name);

        $this->filters[$ns][$name][$type] = $filter;

        return $this;
    }

    
    public function registerNS(string $ns, FilterResolver $resolver): self
    {
        $this->ns[$ns] = $resolver;

        return $this;
    }

    
    public function unregisterNS(string $ns): bool
    {
        if (isset($this->filters[$ns])) {
            unset($this->filters[$ns]);
            unset($this->ns[$ns]);

            return true;
        }

        if (isset($this->ns[$ns])) {
            unset($this->ns[$ns]);

            return true;
        }

        return false;
    }

    public function __serialize(): array
    {
        return [
            'separator' => $this->separator,
            'defaultNS' => $this->defaultNS,
            'ns' => $this->ns,
            'filters' => $this->filters,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->separator = $data['separator'];
        $this->defaultNS = $data['defaultNS'];
        $this->ns = $data['ns'];
        $this->filters = $data['filters'];
    }

    
    protected function parseName(string $name): array
    {
        $name = strtolower($name);

        if (strpos($name, $this->separator) === false) {
            return [$this->defaultNS, $name];
        }

        return explode($this->separator, $name, 2);
    }
}