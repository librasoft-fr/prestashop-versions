<?php

declare(strict_types=1);



namespace phpDocumentor\Reflection\DocBlock\Tags;

use InvalidArgumentException;

use function filter_var;
use function preg_match;
use function trim;

use const FILTER_VALIDATE_EMAIL;


final class Author extends BaseTag implements Factory\StaticMethod
{
    
    protected string $name = 'author';

    
    private string $authorName;

    
    private string $authorEmail;

    
    public function __construct(string $authorName, string $authorEmail)
    {
        if ($authorEmail && !filter_var($authorEmail, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('The author tag does not have a valid e-mail address');
        }

        $this->authorName  = $authorName;
        $this->authorEmail = $authorEmail;
    }

    
    public function getAuthorName(): string
    {
        return $this->authorName;
    }

    
    public function getEmail(): string
    {
        return $this->authorEmail;
    }

    
    public function __toString(): string
    {
        if ($this->authorEmail) {
            $authorEmail = '<' . $this->authorEmail . '>';
        } else {
            $authorEmail = '';
        }

        $authorName = $this->authorName;

        return $authorName . ($authorEmail !== '' ? ($authorName !== '' ? ' ' : '') . $authorEmail : '');
    }

    
    public static function create(string $body): ?self
    {
        $splitTagContent = preg_match('/^([^\<]*)(?:\<([^\>]*)\>)?$/u', $body, $matches);
        if (!$splitTagContent) {
            return null;
        }

        $authorName = trim($matches[1]);
        $email      = isset($matches[2]) ? trim($matches[2]) : '';

        return new static($authorName, $email);
    }
}
