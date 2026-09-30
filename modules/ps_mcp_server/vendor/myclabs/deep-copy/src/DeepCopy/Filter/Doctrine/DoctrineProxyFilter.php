<?php

namespace DeepCopy\Filter\Doctrine;

use DeepCopy\Filter\Filter;


class DoctrineProxyFilter implements Filter
{
    
    public function apply($object, $property, $objectCopier)
    {
        $object->__load();
    }
}
