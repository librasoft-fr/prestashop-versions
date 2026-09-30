<?php

declare(strict_types=1);

namespace Psr\Http\Message;


interface UriInterface
{
    
    public function getScheme();

    
    public function getAuthority();

    
    public function getUserInfo();

    
    public function getHost();

    
    public function getPort();

    
    public function getPath();

    
    public function getQuery();

    
    public function getFragment();

    
    public function withScheme(string $scheme);

    
    public function withUserInfo(string $user, ?string $password = null);

    
    public function withHost(string $host);

    
    public function withPort(?int $port);

    
    public function withPath(string $path);

    
    public function withQuery(string $query);

    
    public function withFragment(string $fragment);

    
    public function __toString();
}
