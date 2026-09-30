<?php



namespace Mcp\Capability\Completion;


class ListCompletionProvider implements ProviderInterface
{
    
    public function __construct(
        private array $values,
    ) {
    }

    public function getCompletions(string $currentValue): array
    {
        if (empty($currentValue)) {
            return $this->values;
        }

        return array_values(array_filter(
            $this->values,
            static fn (string $value) => str_starts_with($value, $currentValue)
        ));
    }
}
