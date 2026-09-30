<?php



namespace Symfony\Component\Finder\Iterator;

use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Finder\SplFileInfo;


class RecursiveDirectoryIterator extends \RecursiveDirectoryIterator
{
    private bool $ignoreUnreadableDirs;
    private bool $ignoreFirstRewind = true;

    
    private string $rootPath;
    private string $subPath;
    private string $directorySeparator = '/';

    
    public function __construct(string $path, int $flags, bool $ignoreUnreadableDirs = false)
    {
        if ($flags & (self::CURRENT_AS_PATHNAME | self::CURRENT_AS_SELF)) {
            throw new \RuntimeException('This iterator only support returning current as fileinfo.');
        }

        parent::__construct($path, $flags);
        $this->ignoreUnreadableDirs = $ignoreUnreadableDirs;
        $this->rootPath = $path;
        if ('/' !== \DIRECTORY_SEPARATOR && !($flags & self::UNIX_PATHS)) {
            $this->directorySeparator = \DIRECTORY_SEPARATOR;
        }
    }

    
    public function current(): SplFileInfo
    {
        

        if (!isset($this->subPath)) {
            $this->subPath = $this->getSubPath();
        }
        $subPathname = $this->subPath;
        if ('' !== $subPathname) {
            $subPathname .= $this->directorySeparator;
        }
        $subPathname .= $this->getFilename();
        $basePath = $this->rootPath;

        if ('/' !== $basePath && !str_ends_with($basePath, $this->directorySeparator) && !str_ends_with($basePath, '/')) {
            $basePath .= $this->directorySeparator;
        }

        return new SplFileInfo($basePath.$subPathname, $this->subPath, $subPathname);
    }

    public function hasChildren(bool $allowLinks = false): bool
    {
        $hasChildren = parent::hasChildren($allowLinks);

        if (!$hasChildren || !$this->ignoreUnreadableDirs) {
            return $hasChildren;
        }

        try {
            parent::getChildren();

            return true;
        } catch (\UnexpectedValueException) {
            
            return false;
        }
    }

    
    public function getChildren(): \RecursiveDirectoryIterator
    {
        try {
            $children = parent::getChildren();

            if ($children instanceof self) {
                
                $children->ignoreUnreadableDirs = $this->ignoreUnreadableDirs;

                
                $children->rootPath = $this->rootPath;
            }

            return $children;
        } catch (\UnexpectedValueException $e) {
            throw new AccessDeniedException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function next(): void
    {
        $this->ignoreFirstRewind = false;

        parent::next();
    }

    public function rewind(): void
    {
        
        
        if ($this->ignoreFirstRewind) {
            $this->ignoreFirstRewind = false;

            return;
        }

        parent::rewind();
    }
}
