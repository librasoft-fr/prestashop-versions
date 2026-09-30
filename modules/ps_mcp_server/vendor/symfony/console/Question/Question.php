<?php



namespace Symfony\Component\Console\Question;

use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;


class Question
{
    private string $question;
    private ?int $attempts = null;
    private bool $hidden = false;
    private bool $hiddenFallback = true;
    private ?\Closure $autocompleterCallback = null;
    private ?\Closure $validator = null;
    private string|int|bool|float|null $default;
    private ?\Closure $normalizer = null;
    private bool $trimmable = true;
    private bool $multiline = false;

    
    public function __construct(string $question, string|bool|int|float|null $default = null)
    {
        $this->question = $question;
        $this->default = $default;
    }

    
    public function getQuestion(): string
    {
        return $this->question;
    }

    
    public function getDefault(): string|bool|int|float|null
    {
        return $this->default;
    }

    
    public function isMultiline(): bool
    {
        return $this->multiline;
    }

    
    public function setMultiline(bool $multiline): static
    {
        $this->multiline = $multiline;

        return $this;
    }

    
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    
    public function setHidden(bool $hidden): static
    {
        if ($this->autocompleterCallback) {
            throw new LogicException('A hidden question cannot use the autocompleter.');
        }

        $this->hidden = $hidden;

        return $this;
    }

    
    public function isHiddenFallback(): bool
    {
        return $this->hiddenFallback;
    }

    
    public function setHiddenFallback(bool $fallback): static
    {
        $this->hiddenFallback = $fallback;

        return $this;
    }

    
    public function getAutocompleterValues(): ?iterable
    {
        $callback = $this->getAutocompleterCallback();

        return $callback ? $callback('') : null;
    }

    
    public function setAutocompleterValues(?iterable $values): static
    {
        if (\is_array($values)) {
            $values = $this->isAssoc($values) ? array_merge(array_keys($values), array_values($values)) : array_values($values);

            $callback = static fn () => $values;
        } elseif ($values instanceof \Traversable) {
            $callback = static function () use ($values) {
                static $valueCache;

                return $valueCache ??= iterator_to_array($values, false);
            };
        } else {
            $callback = null;
        }

        return $this->setAutocompleterCallback($callback);
    }

    
    public function getAutocompleterCallback(): ?callable
    {
        return $this->autocompleterCallback;
    }

    
    public function setAutocompleterCallback(?callable $callback = null): static
    {
        if (1 > \func_num_args()) {
            trigger_deprecation('symfony/console', '6.2', 'Calling "%s()" without any arguments is deprecated, pass null explicitly instead.', __METHOD__);
        }
        if ($this->hidden && null !== $callback) {
            throw new LogicException('A hidden question cannot use the autocompleter.');
        }

        $this->autocompleterCallback = null === $callback ? null : $callback(...);

        return $this;
    }

    
    public function setValidator(?callable $validator = null): static
    {
        if (1 > \func_num_args()) {
            trigger_deprecation('symfony/console', '6.2', 'Calling "%s()" without any arguments is deprecated, pass null explicitly instead.', __METHOD__);
        }
        $this->validator = null === $validator ? null : $validator(...);

        return $this;
    }

    
    public function getValidator(): ?callable
    {
        return $this->validator;
    }

    
    public function setMaxAttempts(?int $attempts): static
    {
        if (null !== $attempts && $attempts < 1) {
            throw new InvalidArgumentException('Maximum number of attempts must be a positive value.');
        }

        $this->attempts = $attempts;

        return $this;
    }

    
    public function getMaxAttempts(): ?int
    {
        return $this->attempts;
    }

    
    public function setNormalizer(callable $normalizer): static
    {
        $this->normalizer = $normalizer(...);

        return $this;
    }

    
    public function getNormalizer(): ?callable
    {
        return $this->normalizer;
    }

    
    protected function isAssoc(array $array)
    {
        return (bool) \count(array_filter(array_keys($array), 'is_string'));
    }

    public function isTrimmable(): bool
    {
        return $this->trimmable;
    }

    
    public function setTrimmable(bool $trimmable): static
    {
        $this->trimmable = $trimmable;

        return $this;
    }
}
