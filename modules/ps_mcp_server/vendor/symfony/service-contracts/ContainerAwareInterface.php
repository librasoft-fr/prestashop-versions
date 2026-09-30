<?php



namespace Symfony\Contracts\Service;

use Psr\Container\ContainerInterface;


interface ContainerAwareInterface
{
    public function getContainer(): ContainerInterface;
}
