<?php



namespace Mcp\Server\Transport\Http\OAuth;


final class AuthorizationResult
{
    
    private function __construct(
        private readonly bool $allowed,
        private readonly int $statusCode,
        private readonly ?string $error,
        private readonly ?string $errorDescription,
        private readonly ?array $scopes,
        private readonly array $attributes,
    ) {
    }

    
    public static function allow(array $attributes = []): self
    {
        return new self(true, 200, null, null, null, $attributes);
    }

    
    public static function unauthorized(
        ?string $error = null,
        ?string $errorDescription = null,
        ?array $scopes = null,
    ): self {
        return new self(false, 401, $error, $errorDescription, $scopes, []);
    }

    
    public static function forbidden(
        ?string $error = 'insufficient_scope',
        ?string $errorDescription = null,
        ?array $scopes = null,
    ): self {
        return new self(false, 403, $error ?? 'insufficient_scope', $errorDescription, $scopes, []);
    }

    
    public static function badRequest(
        ?string $error = 'invalid_request',
        ?string $errorDescription = null,
    ): self {
        return new self(false, 400, $error ?? 'invalid_request', $errorDescription, null, []);
    }

    public function isAllowed(): bool
    {
        return $this->allowed;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function getErrorDescription(): ?string
    {
        return $this->errorDescription;
    }

    
    public function getScopes(): ?array
    {
        return $this->scopes;
    }

    
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
