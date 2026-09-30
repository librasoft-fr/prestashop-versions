<?php



namespace Symfony\Component\Finder;

use Symfony\Component\Finder\Comparator\DateComparator;
use Symfony\Component\Finder\Comparator\NumberComparator;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Iterator\CustomFilterIterator;
use Symfony\Component\Finder\Iterator\DateRangeFilterIterator;
use Symfony\Component\Finder\Iterator\DepthRangeFilterIterator;
use Symfony\Component\Finder\Iterator\ExcludeDirectoryFilterIterator;
use Symfony\Component\Finder\Iterator\FilecontentFilterIterator;
use Symfony\Component\Finder\Iterator\FilenameFilterIterator;
use Symfony\Component\Finder\Iterator\LazyIterator;
use Symfony\Component\Finder\Iterator\SizeRangeFilterIterator;
use Symfony\Component\Finder\Iterator\SortableIterator;


class Finder implements \IteratorAggregate, \Countable
{
    public const IGNORE_VCS_FILES = 1;
    public const IGNORE_DOT_FILES = 2;
    public const IGNORE_VCS_IGNORED_FILES = 4;

    private int $mode = 0;
    private array $names = [];
    private array $notNames = [];
    private array $exclude = [];
    private array $filters = [];
    private array $pruneFilters = [];
    private array $depths = [];
    private array $sizes = [];
    private bool $followLinks = false;
    private bool $reverseSorting = false;
    private \Closure|int|false $sort = false;
    private int $ignore = 0;
    
    private array $dirs = [];
    private array $dates = [];
    
    private array $iterators = [];
    private array $contains = [];
    private array $notContains = [];
    private array $paths = [];
    private array $notPaths = [];
    private bool $ignoreUnreadableDirs = false;

    private static array $vcsPatterns = ['.svn', '_svn', 'CVS', '_darcs', '.arch-params', '.monotone', '.bzr', '.git', '.hg'];

    public function __construct()
    {
        $this->ignore = static::IGNORE_VCS_FILES | static::IGNORE_DOT_FILES;
    }

    
    public static function create(): static
    {
        return new static();
    }

    
    public function directories(): static
    {
        $this->mode = Iterator\FileTypeFilterIterator::ONLY_DIRECTORIES;

        return $this;
    }

    
    public function files(): static
    {
        $this->mode = Iterator\FileTypeFilterIterator::ONLY_FILES;

        return $this;
    }

    
    public function depth(string|int|array $levels): static
    {
        foreach ((array) $levels as $level) {
            $this->depths[] = new NumberComparator($level);
        }

        return $this;
    }

    
    public function date(string|array $dates): static
    {
        foreach ((array) $dates as $date) {
            $this->dates[] = new DateComparator($date);
        }

        return $this;
    }

    
    public function name(string|array $patterns): static
    {
        $this->names = array_merge($this->names, (array) $patterns);

        return $this;
    }

    
    public function notName(string|array $patterns): static
    {
        $this->notNames = array_merge($this->notNames, (array) $patterns);

        return $this;
    }

    
    public function contains(string|array $patterns): static
    {
        $this->contains = array_merge($this->contains, (array) $patterns);

        return $this;
    }

    
    public function notContains(string|array $patterns): static
    {
        $this->notContains = array_merge($this->notContains, (array) $patterns);

        return $this;
    }

    
    public function path(string|array $patterns): static
    {
        $this->paths = array_merge($this->paths, (array) $patterns);

        return $this;
    }

    
    public function notPath(string|array $patterns): static
    {
        $this->notPaths = array_merge($this->notPaths, (array) $patterns);

        return $this;
    }

    
    public function size(string|int|array $sizes): static
    {
        foreach ((array) $sizes as $size) {
            $this->sizes[] = new NumberComparator($size);
        }

        return $this;
    }

    
    public function exclude(string|array $dirs): static
    {
        $this->exclude = array_merge($this->exclude, (array) $dirs);

        return $this;
    }

    
    public function ignoreDotFiles(bool $ignoreDotFiles): static
    {
        if ($ignoreDotFiles) {
            $this->ignore |= static::IGNORE_DOT_FILES;
        } else {
            $this->ignore &= ~static::IGNORE_DOT_FILES;
        }

        return $this;
    }

    
    public function ignoreVCS(bool $ignoreVCS): static
    {
        if ($ignoreVCS) {
            $this->ignore |= static::IGNORE_VCS_FILES;
        } else {
            $this->ignore &= ~static::IGNORE_VCS_FILES;
        }

        return $this;
    }

    
    public function ignoreVCSIgnored(bool $ignoreVCSIgnored): static
    {
        if ($ignoreVCSIgnored) {
            $this->ignore |= static::IGNORE_VCS_IGNORED_FILES;
        } else {
            $this->ignore &= ~static::IGNORE_VCS_IGNORED_FILES;
        }

        return $this;
    }

    
    public static function addVCSPattern(string|array $pattern)
    {
        foreach ((array) $pattern as $p) {
            self::$vcsPatterns[] = $p;
        }

        self::$vcsPatterns = array_unique(self::$vcsPatterns);
    }

    
    public function sort(\Closure $closure): static
    {
        $this->sort = $closure;

        return $this;
    }

    
    public function sortByExtension(): static
    {
        $this->sort = SortableIterator::SORT_BY_EXTENSION;

        return $this;
    }

    
    public function sortByName(bool $useNaturalSort = false): static
    {
        $this->sort = $useNaturalSort ? SortableIterator::SORT_BY_NAME_NATURAL : SortableIterator::SORT_BY_NAME;

        return $this;
    }

    
    public function sortByCaseInsensitiveName(bool $useNaturalSort = false): static
    {
        $this->sort = $useNaturalSort ? SortableIterator::SORT_BY_NAME_NATURAL_CASE_INSENSITIVE : SortableIterator::SORT_BY_NAME_CASE_INSENSITIVE;

        return $this;
    }

    
    public function sortBySize(): static
    {
        $this->sort = SortableIterator::SORT_BY_SIZE;

        return $this;
    }

    
    public function sortByType(): static
    {
        $this->sort = SortableIterator::SORT_BY_TYPE;

        return $this;
    }

    
    public function sortByAccessedTime(): static
    {
        $this->sort = SortableIterator::SORT_BY_ACCESSED_TIME;

        return $this;
    }

    
    public function reverseSorting(): static
    {
        $this->reverseSorting = true;

        return $this;
    }

    
    public function sortByChangedTime(): static
    {
        $this->sort = SortableIterator::SORT_BY_CHANGED_TIME;

        return $this;
    }

    
    public function sortByModifiedTime(): static
    {
        $this->sort = SortableIterator::SORT_BY_MODIFIED_TIME;

        return $this;
    }

    
    public function filter(\Closure $closure ): static
    {
        $prune = 1 < \func_num_args() ? func_get_arg(1) : false;
        $this->filters[] = $closure;

        if ($prune) {
            $this->pruneFilters[] = $closure;
        }

        return $this;
    }

    
    public function followLinks(): static
    {
        $this->followLinks = true;

        return $this;
    }

    
    public function ignoreUnreadableDirs(bool $ignore = true): static
    {
        $this->ignoreUnreadableDirs = $ignore;

        return $this;
    }

    
    public function in(string|array $dirs): static
    {
        $resolvedDirs = [];

        foreach ((array) $dirs as $dir) {
            if (is_dir($dir)) {
                $resolvedDirs[] = [$this->normalizeDir($dir)];
            } elseif ($glob = glob($dir, (\defined('GLOB_BRACE') ? \GLOB_BRACE : 0) | \GLOB_ONLYDIR | \GLOB_NOSORT)) {
                sort($glob);
                $resolvedDirs[] = array_map($this->normalizeDir(...), $glob);
            } else {
                throw new DirectoryNotFoundException(\sprintf('The "%s" directory does not exist.', $dir));
            }
        }

        $this->dirs = array_merge($this->dirs, ...$resolvedDirs);

        return $this;
    }

    
    public function getIterator(): \Iterator
    {
        if (!$this->dirs && !$this->iterators) {
            throw new \LogicException('You must call one of in() or append() methods before iterating over a Finder.');
        }

        if (1 === \count($this->dirs) && !$this->iterators) {
            $iterator = $this->searchInDirectory($this->dirs[0]);
        } else {
            $iterator = new \AppendIterator();
            foreach ($this->dirs as $dir) {
                $iterator->append(new \IteratorIterator(new LazyIterator(fn () => $this->searchInDirectory($dir))));
            }

            foreach ($this->iterators as $it) {
                $iterator->append(new \IteratorIterator(new LazyIterator(static function () use ($it) {
                    foreach ($it as $file) {
                        if (!$file instanceof \SplFileInfo) {
                            $file = new \SplFileInfo($file);
                        }
                        $key = $file->getPathname();
                        if (!$file instanceof SplFileInfo) {
                            $file = new SplFileInfo($key, $file->getPath(), $key);
                        }

                        yield $key => $file;
                    }
                })));
            }
        }

        if ($this->sort || $this->reverseSorting) {
            $iterator = (new SortableIterator($iterator, $this->sort, $this->reverseSorting))->getIterator();
        }

        return $iterator;
    }

    
    public function append(iterable $iterator): static
    {
        $this->iterators[] = $iterator;

        return $this;
    }

    
    public function hasResults(): bool
    {
        foreach ($this->getIterator() as $_) {
            return true;
        }

        return false;
    }

    
    public function count(): int
    {
        return iterator_count($this->getIterator());
    }

    private function searchInDirectory(string $dir): \Iterator
    {
        $exclude = $this->exclude;
        $notPaths = $this->notPaths;

        if ($this->pruneFilters) {
            $exclude = array_merge($exclude, $this->pruneFilters);
        }

        if (static::IGNORE_VCS_FILES === (static::IGNORE_VCS_FILES & $this->ignore)) {
            $exclude = array_merge($exclude, self::$vcsPatterns);
        }

        if (static::IGNORE_DOT_FILES === (static::IGNORE_DOT_FILES & $this->ignore)) {
            $notPaths[] = '#(^|/)\..+(/|$)#';
        }

        $minDepth = 0;
        $maxDepth = \PHP_INT_MAX;

        foreach ($this->depths as $comparator) {
            switch ($comparator->getOperator()) {
                case '>':
                    $minDepth = $comparator->getTarget() + 1;
                    break;
                case '>=':
                    $minDepth = $comparator->getTarget();
                    break;
                case '<':
                    $maxDepth = $comparator->getTarget() - 1;
                    break;
                case '<=':
                    $maxDepth = $comparator->getTarget();
                    break;
                default:
                    $minDepth = $maxDepth = $comparator->getTarget();
            }
        }

        $flags = \RecursiveDirectoryIterator::SKIP_DOTS;

        if ($this->followLinks) {
            $flags |= \RecursiveDirectoryIterator::FOLLOW_SYMLINKS;
        }

        $iterator = new Iterator\RecursiveDirectoryIterator($dir, $flags, $this->ignoreUnreadableDirs);

        if ($exclude) {
            $iterator = new ExcludeDirectoryFilterIterator($iterator, $exclude);
        }

        $iterator = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::SELF_FIRST);

        if ($minDepth > 0 || $maxDepth < \PHP_INT_MAX) {
            $iterator = new DepthRangeFilterIterator($iterator, $minDepth, $maxDepth);
        }

        if ($this->mode) {
            $iterator = new Iterator\FileTypeFilterIterator($iterator, $this->mode);
        }

        if ($this->names || $this->notNames) {
            $iterator = new FilenameFilterIterator($iterator, $this->names, $this->notNames);
        }

        if ($this->contains || $this->notContains) {
            $iterator = new FilecontentFilterIterator($iterator, $this->contains, $this->notContains);
        }

        if ($this->sizes) {
            $iterator = new SizeRangeFilterIterator($iterator, $this->sizes);
        }

        if ($this->dates) {
            $iterator = new DateRangeFilterIterator($iterator, $this->dates);
        }

        if ($this->filters) {
            $iterator = new CustomFilterIterator($iterator, $this->filters);
        }

        if ($this->paths || $notPaths) {
            $iterator = new Iterator\PathFilterIterator($iterator, $this->paths, $notPaths);
        }

        if (static::IGNORE_VCS_IGNORED_FILES === (static::IGNORE_VCS_IGNORED_FILES & $this->ignore)) {
            $iterator = new Iterator\VcsIgnoredFilterIterator($iterator, $dir);
        }

        return $iterator;
    }

    
    private function normalizeDir(string $dir): string
    {
        if ('/' === $dir) {
            return $dir;
        }

        $dir = rtrim($dir, '/'.\DIRECTORY_SEPARATOR);

        if (preg_match('#^(ssh2\.)?s?ftp://#', $dir)) {
            $dir .= '/';
        }

        return $dir;
    }
}
