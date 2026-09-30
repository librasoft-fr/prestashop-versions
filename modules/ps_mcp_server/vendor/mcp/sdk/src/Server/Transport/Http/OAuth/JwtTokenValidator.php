<?php



namespace Mcp\Server\Transport\Http\OAuth;

use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use Mcp\Exception\RuntimeException;


class JwtTokenValidator implements AuthorizationTokenValidatorInterface
{
    
    public function __construct(
        private readonly string|array $issuer,
        private readonly string|array $audience,
        private readonly JwksProviderInterface $jwksProvider,
        private readonly ?string $jwksUri = null,
        private readonly array $algorithms = ['RS256', 'RS384', 'RS512'],
        private readonly string $scopeClaim = 'scope',
    ) {
        if (!class_exists(JWT::class)) {
            throw new RuntimeException('For using the JwtTokenValidator, the firebase/php-jwt package is required. Try running "composer require firebase/php-jwt".');
        }
    }

    public function validate(string $accessToken): AuthorizationResult
    {
        try {
            
            $claims = (array) JWT::decode($accessToken, $this->getJwks());

            
            if (!$this->validateIssuer($claims)) {
                return AuthorizationResult::unauthorized('invalid_token', 'Token issuer mismatch.');
            }

            
            if (!$this->validateAudience($claims)) {
                return AuthorizationResult::unauthorized('invalid_token', 'Token audience mismatch.');
            }

            
            $attributes = [
                'oauth.claims' => $claims,
                'oauth.scopes' => $this->extractScopes($claims),
            ];

            
            if (isset($claims['sub'])) {
                $attributes['oauth.subject'] = $claims['sub'];
            }

            if (isset($claims['client_id'])) {
                $attributes['oauth.client_id'] = $claims['client_id'];
            }

            
            if (isset($claims['azp'])) {
                $attributes['oauth.authorized_party'] = $claims['azp'];
            }

            return AuthorizationResult::allow($attributes);
        } catch (ExpiredException) {
            return AuthorizationResult::unauthorized('invalid_token', 'Token has expired.');
        } catch (SignatureInvalidException) {
            return AuthorizationResult::unauthorized('invalid_token', 'Token signature verification failed.');
        } catch (BeforeValidException) {
            return AuthorizationResult::unauthorized('invalid_token', 'Token is not yet valid.');
        } catch (\InvalidArgumentException|\UnexpectedValueException|\DomainException $e) {
            return AuthorizationResult::unauthorized('invalid_token', 'Token validation failed: '.$e->getMessage());
        }
    }

    
    public function requireScopes(AuthorizationResult $result, array $requiredScopes): AuthorizationResult
    {
        if (!$result->isAllowed()) {
            return $result;
        }

        $tokenScopes = $result->getAttributes()['oauth.scopes'] ?? [];

        if (!\is_array($tokenScopes)) {
            $tokenScopes = [];
        }

        foreach ($requiredScopes as $required) {
            if (!\in_array($required, $tokenScopes, true)) {
                return AuthorizationResult::forbidden('insufficient_scope', \sprintf('Required scope: %s', $required), $requiredScopes);
            }
        }

        return $result;
    }

    
    private function getJwks(): array
    {
        $issuer = \is_array($this->issuer) ? $this->issuer[0] : $this->issuer;
        $jwksData = $this->jwksProvider->getJwks($issuer, $this->jwksUri);

        
        return JWK::parseKeySet($jwksData, $this->algorithms[0]);
    }

    
    private function validateAudience(array $claims): bool
    {
        if (!isset($claims['aud'])) {
            return false;
        }

        $tokenAudiences = \is_array($claims['aud']) ? $claims['aud'] : [$claims['aud']];
        $expectedAudiences = \is_array($this->audience) ? $this->audience : [$this->audience];

        foreach ($expectedAudiences as $expected) {
            if (\in_array($expected, $tokenAudiences, true)) {
                return true;
            }
        }

        return false;
    }

    
    private function validateIssuer(array $claims): bool
    {
        if (!isset($claims['iss'])) {
            return false;
        }

        $expectedIssuers = \is_array($this->issuer) ? $this->issuer : [$this->issuer];

        return \in_array($claims['iss'], $expectedIssuers, true);
    }

    
    private function extractScopes(array $claims): array
    {
        if (!isset($claims[$this->scopeClaim])) {
            return [];
        }

        $scopeValue = $claims[$this->scopeClaim];

        if (\is_array($scopeValue)) {
            return array_values(array_filter($scopeValue, 'is_string'));
        }

        if (\is_string($scopeValue)) {
            return array_values(array_filter(explode(' ', $scopeValue)));
        }

        return [];
    }
}
