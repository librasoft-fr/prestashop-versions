<?php

namespace DeepCopy\Matcher\Doctrine;

use DeepCopy\Matcher\Matcher;
use Doctrine\Persistence\Proxy;


class DoctrineProxyMatcher implements Matcher
{
    
    public function matches($object, $property)
    {
        return $object instanceof Proxy;
    }
}
