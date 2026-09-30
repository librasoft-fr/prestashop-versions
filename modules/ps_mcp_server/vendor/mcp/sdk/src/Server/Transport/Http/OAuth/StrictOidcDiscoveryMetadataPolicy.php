<?php



namespace Mcp\Server\Transport\Http\OAuth;


final class StrictOidcDiscoveryMetadataPolicy implements OidcDiscoveryMetadataPolicyInterface
{
    public function isValid(mixed $metadata): bool
    {
        if (!\is_array($metadata)
            || !isset($metadata['authorization_endpoint'], $metadata['token_endpoint'], $metadata['jwks_uri'])
            || !\is_string($metadata['authorization_endpoint'])
            || '' === trim($metadata['authorization_endpoint'])
            || !\is_string($metadata['token_endpoint'])
            || '' === trim($metadata['token_endpoint'])
            || !\is_string($metadata['jwks_uri'])
            || '' === trim($metadata['jwks_uri'])
            || !isset($metadata['code_challenge_methods_supported'])
        ) {
            return false;
        }

        if (!\is_array($metadata['code_challenge_methods_supported']) || [] === $metadata['code_challenge_methods_supported']) {
            return false;
        }

        foreach ($metadata['code_challenge_methods_supported'] as $method) {
            if (!\is_string($method) || '' === trim($method)) {
                return false;
            }
        }

        return true;
    }
}
