<?php

declare(strict_types=1);

namespace JsonSchema\Tool\Validator;

class UriValidator
{
    public static function isValid(string $uri): bool
    {
        
        $hierarchicalPattern = '/^
            ([a-z][a-z0-9+\-.]*):\/\/                # Scheme (http, https, ftp, etc.)
            (?:([^:@\/?#]+)(?::([^@\/?#]*))?@)?      # Optional userinfo (user:pass@)
            ([a-z0-9._~-]+|\[[a-f0-9:.]+\])          # Hostname or IPv6 in brackets
            (?::(\d{1,5}))?                          # Optional port
            (\/[a-zA-Z0-9._~!$&\'()*+,;=:@\/%-]*)*   # Path (valid characters only)
            (\?([^#]*))?                             # Optional query
            (\#(.*))?                                # Optional fragment
        $/ix';

        
        $nonHierarchicalPattern = '/^
                (mailto|data|urn|news|tel|file):     # Only allow known non-hierarchical schemes
                (.+)                                 # Must contain at least one character after scheme
        $/ix';

        
        $newsGroupPattern = '/^[a-z0-9]+(\.[a-z0-9]+)*$/i';
        $telPattern = '/^\+?[0-9.\-() ]+$/'; 

        
        $emailPattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';

        
        if (preg_match($hierarchicalPattern, $uri, $matches) === 1) {
            
            if (!empty($matches[4]) && preg_match('/\.\./', $matches[4])) {
                return false;
            }

            
            if (!empty($matches[5]) && ($matches[5] < 1 || $matches[5] > 65535)) {
                return false;
            }

            
            if (!empty($matches[6]) && preg_match('/[<>{}|\\\^`]/', $matches[6])) {
                return false;
            }

            return true;
        }

        
        if (preg_match($nonHierarchicalPattern, $uri, $matches) === 1) {
            $scheme = strtolower($matches[1]); 

            
            if ($scheme === 'mailto') {
                return preg_match($emailPattern, $matches[2]) === 1;
            }

            if ($scheme === 'news') {
                return preg_match($newsGroupPattern, $matches[2]) === 1;
            }

            if ($scheme === 'tel') {
                return preg_match($telPattern, $matches[2]) === 1;
            }

            return true; 
        }

        return false;
    }
}
