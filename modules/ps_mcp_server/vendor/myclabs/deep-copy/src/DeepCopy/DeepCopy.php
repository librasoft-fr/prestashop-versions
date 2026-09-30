<?php

namespace DeepCopy;

use ArrayObject;
use DateInterval;
use DatePeriod;
use DateTimeInterface;
use DateTimeZone;
use DeepCopy\Exception\CloneException;
use DeepCopy\Filter\ChainableFilter;
use DeepCopy\Filter\Filter;
use DeepCopy\Matcher\Matcher;
use DeepCopy\Reflection\ReflectionHelper;
use DeepCopy\TypeFilter\Date\DateIntervalFilter;
use DeepCopy\TypeFilter\Date\DatePeriodFilter;
use DeepCopy\TypeFilter\Spl\ArrayObjectFilter;
use DeepCopy\TypeFilter\Spl\SplDoublyLinkedListFilter;
use DeepCopy\TypeFilter\TypeFilter;
use DeepCopy\TypeMatcher\TypeMatcher;
use ReflectionObject;
use ReflectionProperty;
use SplDoublyLinkedList;


class DeepCopy
{
    
    private $hashMap = [];

    
    private $filters = [];

    
    private $typeFilters = [];

    
    private $skipUncloneable = false;

    
    private $useCloneMethod;

    
    public function __construct($useCloneMethod = false)
    {
        $this->useCloneMethod = $useCloneMethod;

        $this->addTypeFilter(new ArrayObjectFilter($this), new TypeMatcher(ArrayObject::class));
        $this->addTypeFilter(new DateIntervalFilter(), new TypeMatcher(DateInterval::class));
        $this->addTypeFilter(new DatePeriodFilter(), new TypeMatcher(DatePeriod::class));
        $this->addTypeFilter(new SplDoublyLinkedListFilter($this), new TypeMatcher(SplDoublyLinkedList::class));
    }

    
    public function skipUncloneable($skipUncloneable = true)
    {
        $this->skipUncloneable = $skipUncloneable;

        return $this;
    }

    
    public function copy($object)
    {
        $this->hashMap = [];

        return $this->recursiveCopy($object);
    }

    public function addFilter(Filter $filter, Matcher $matcher)
    {
        $this->filters[] = [
            'matcher' => $matcher,
            'filter'  => $filter,
        ];
    }

    public function prependFilter(Filter $filter, Matcher $matcher)
    {
        array_unshift($this->filters, [
            'matcher' => $matcher,
            'filter'  => $filter,
        ]);
    }

    public function addTypeFilter(TypeFilter $filter, TypeMatcher $matcher)
    {
        $this->typeFilters[] = [
            'matcher' => $matcher,
            'filter'  => $filter,
        ];
    }

    public function prependTypeFilter(TypeFilter $filter, TypeMatcher $matcher)
    {
        array_unshift($this->typeFilters, [
            'matcher' => $matcher,
            'filter'  => $filter,
        ]);
    }

    private function recursiveCopy($var)
    {
        
        if ($filter = $this->getFirstMatchedTypeFilter($this->typeFilters, $var)) {
            return $filter->apply($var);
        }

        
        if (is_resource($var)) {
            return $var;
        }

        
        if (is_array($var)) {
            return $this->copyArray($var);
        }

        
        if (! is_object($var)) {
            return $var;
        }

        
        if (PHP_VERSION_ID >= 80100 && enum_exists(get_class($var))) {
            return $var;
        }

        
        return $this->copyObject($var);
    }

    
    private function copyArray(array $array)
    {
        foreach ($array as $key => $value) {
            $array[$key] = $this->recursiveCopy($value);
        }

        return $array;
    }

    
    private function copyObject($object)
    {
        $objectHash = spl_object_hash($object);

        if (isset($this->hashMap[$objectHash])) {
            return $this->hashMap[$objectHash];
        }

        $reflectedObject = new ReflectionObject($object);
        $isCloneable = $reflectedObject->isCloneable();

        if (false === $isCloneable) {
            if ($this->skipUncloneable) {
                $this->hashMap[$objectHash] = $object;

                return $object;
            }

            throw new CloneException(
                sprintf(
                    'The class "%s" is not cloneable.',
                    $reflectedObject->getName()
                )
            );
        }

        $newObject = clone $object;
        $this->hashMap[$objectHash] = $newObject;

        if ($this->useCloneMethod && $reflectedObject->hasMethod('__clone')) {
            return $newObject;
        }

        if ($newObject instanceof DateTimeInterface || $newObject instanceof DateTimeZone) {
            return $newObject;
        }

        foreach (ReflectionHelper::getProperties($reflectedObject) as $property) {
            $this->copyObjectProperty($newObject, $property);
        }

        return $newObject;
    }

    private function copyObjectProperty($object, ReflectionProperty $property)
    {
        
        if ($property->isStatic()) {
            return;
        }

        
        if (method_exists($property, 'isReadOnly') && $property->isReadOnly()) {
            return;
        }

        
        foreach ($this->filters as $item) {
            
            $matcher = $item['matcher'];
            
            $filter = $item['filter'];

            if ($matcher->matches($object, $property->getName())) {
                $filter->apply(
                    $object,
                    $property->getName(),
                    function ($object) {
                        return $this->recursiveCopy($object);
                    }
                );

                if ($filter instanceof ChainableFilter) {
                    continue;
                }

                
                return;
            }
        }

        if (PHP_VERSION_ID < 80100) {
            $property->setAccessible(true);
        }

        
        if (method_exists($property, 'isInitialized') && !$property->isInitialized($object)) {
            return;
        }

        $propertyValue = $property->getValue($object);

        
        $property->setValue($object, $this->recursiveCopy($propertyValue));
    }

    
    private function getFirstMatchedTypeFilter(array $filterRecords, $var)
    {
        $matched = $this->first(
            $filterRecords,
            function (array $record) use ($var) {
                
                $matcher = $record['matcher'];

                return $matcher->matches($var);
            }
        );

        return isset($matched) ? $matched['filter'] : null;
    }

    
    private function first(array $elements, callable $predicate)
    {
        foreach ($elements as $element) {
            if (call_user_func($predicate, $element)) {
                return $element;
            }
        }

        return null;
    }
}
