<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection\DocBlock\Tags;

use InvalidArgumentException;
use phpDocumentor\Reflection\Type;

use function in_array;
use function sprintf;
use function strlen;
use function substr;
use function trim;

abstract class TagWithType extends BaseTag
{
    
    protected ?Type $type = null;

    
    public function getType(): ?Type
    {
        return $this->type;
    }

    
    protected static function extractTypeFromBody(string $body): array
    {
        $type         = '';
        $nestingLevel = 0;
        for ($i = 0, $iMax = strlen($body); $i < $iMax; $i++) {
            $character = $body[$i];

            if ($nestingLevel === 0 && trim($character) === '') {
                break;
            }

            $type .= $character;
            if (in_array($character, ['<', '(', '[', '{'])) {
                $nestingLevel++;
                continue;
            }

            if (in_array($character, ['>', ')', ']', '}'])) {
                $nestingLevel--;
                continue;
            }
        }

        if ($nestingLevel < 0 || $nestingLevel > 0) {
            throw new InvalidArgumentException(
                sprintf('Could not find type in %s, please check for malformed notations', $body)
            );
        }

        $description = trim(substr($body, strlen($type)));

        return [$type, $description];
    }

    public function __toString(): string
    {
        if ($this->description) {
            $description = $this->description->render();
        } else {
            $description = '';
        }

        $type = (string) $this->type;

        return $type . ($description !== '' ? ($type !== '' ? ' ' : '') . $description : '');
    }
}
