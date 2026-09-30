<?php



namespace Mcp\Schema\Content;

use Mcp\Exception\InvalidArgumentException;
use Mcp\Schema\Annotations;


class TextContent extends Content
{
    
    public function __construct(
        public mixed $text,
        public readonly ?Annotations $annotations = null,
    ) {
        $this->text = (\is_array($text) || \is_object($text))
            ? json_encode($text, \JSON_PRETTY_PRINT) : (string) $text;

        parent::__construct('text');
    }

    
    public static function fromArray(array $data): self
    {
        if (!isset($data['text']) || !\is_string($data['text'])) {
            throw new InvalidArgumentException('Missing or invalid "text" in TextContent data.');
        }

        return new self(
            $data['text'],
            isset($data['annotations']) ? Annotations::fromArray($data['annotations']) : null
        );
    }

    
    public static function code(string $code, string $language = '', ?Annotations $annotations = null): self
    {
        return new self("```{$language}\n{$code}\n```", $annotations);
    }

    
    public function jsonSerialize(): array
    {
        $result = [
            'type' => 'text',
            'text' => $this->text,
        ];

        if (null !== $this->annotations) {
            $result['annotations'] = $this->annotations;
        }

        return $result;
    }
}
