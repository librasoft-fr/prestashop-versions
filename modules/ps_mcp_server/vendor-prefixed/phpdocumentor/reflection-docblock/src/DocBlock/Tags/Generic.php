<?php

declare(strict_types=1);

namespace PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Tags;

use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\Description;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use PsMcpServerDeps\phpDocumentor\Reflection\DocBlock\StandardTagFactory;
use PsMcpServerDeps\phpDocumentor\Reflection\Types\Context as TypeContext;
use PsMcpServerDeps\Webmozart\Assert\Assert;

final class Generic extends BaseTag implements Factory\StaticMethod
{
    public function __construct(string $name, ?Description $description = null)
    {
        $this->validateTagName($name);

        $this->name = $name;
        $this->description = $description;
    }

    public static function create(
        string $body,
        string $name = '',
        ?DescriptionFactory $descriptionFactory = null,
        ?TypeContext $context = null,
    ): self {
        Assert::stringNotEmpty($name);
        Assert::notNull($descriptionFactory);

        $description = $body !== '' ? $descriptionFactory->create($body, $context) : null;

        return new static($name, $description);
    }

    public function __toString(): string
    {
        if ($this->description) {
            $description = $this->description->render();
        } else {
            $description = '';
        }

        return $description;
    }

    private function validateTagName(string $name): void
    {
        if (!\preg_match('/^' . StandardTagFactory::REGEX_TAGNAME . '$/u', $name)) {
            throw new \InvalidArgumentException('The tag name "' . $name . '" is not wellformed. Tags may only consist of letters, underscores, hyphens and backslashes.');
        }
    }
}
