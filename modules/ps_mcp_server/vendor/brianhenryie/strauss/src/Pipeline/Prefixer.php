<?php

namespace BrianHenryIE\Strauss\Pipeline;

use BrianHenryIE\Strauss\Composer\ComposerPackage;
use BrianHenryIE\Strauss\Composer\Extra\StraussConfig;
use BrianHenryIE\Strauss\Files\File;
use BrianHenryIE\Strauss\Types\DiscoveredSymbols;
use BrianHenryIE\Strauss\Types\FunctionSymbol;
use Exception;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

class Prefixer
{
    protected StraussConfig $config;

    
    protected $filesystem;

    protected string $targetDirectory;
    protected ?string $namespacePrefix;
    protected ?string $classmapPrefix;
    protected ?string $constantsPrefix;

    
    protected array $excludePackageNamesFromPrefixing;

    
    protected array $excludeNamespacesFromPrefixing;

    
    protected array $excludeFilePatternsFromPrefixing;

    
    protected array $changedFiles = array();
    protected string $workingDir;

    public function __construct(StraussConfig $config, string $workingDir)
    {
        $this->config = $config;

        $this->workingDir = $workingDir;
        $this->filesystem = new Filesystem(new LocalFilesystemAdapter($workingDir));

        $this->targetDirectory = $config->getTargetDirectory();
        $this->namespacePrefix = $config->getNamespacePrefix();
        $this->classmapPrefix = $config->getClassmapPrefix();
        $this->constantsPrefix = $config->getConstantsPrefix();

        $this->excludePackageNamesFromPrefixing = $config->getExcludePackagesFromPrefixing();
        $this->excludeNamespacesFromPrefixing = $config->getExcludeNamespacesFromPrefixing();
        $this->excludeFilePatternsFromPrefixing = $config->getExcludeFilePatternsFromPrefixing();
    }

    
    


    
    public function replaceInFiles(DiscoveredSymbols $discoveredSymbols, array $files): void
    {

        foreach ($files as $file) {
            $targetRelativeFilepathFromProject = $file->getAbsoluteTargetPath($this->workingDir);

            if (! $this->filesystem->fileExists($targetRelativeFilepathFromProject)) {
                
                continue;
            }

            
            $contents = $this->filesystem->read($targetRelativeFilepathFromProject);

            $updatedContents = $this->replaceInString($discoveredSymbols, $contents);

            if ($updatedContents !== $contents) {
                $file->setDidUpdate();
                $this->filesystem->write($targetRelativeFilepathFromProject, $updatedContents);
            }
        }
    }

    
    public function replaceInProjectFiles(DiscoveredSymbols $discoveredSymbols, array $relativeFilePaths): void
    {
        foreach ($relativeFilePaths as $workingDirRelativeFilepath) {
            if (! $this->filesystem->fileExists($workingDirRelativeFilepath)) {
                continue;
            }
            
            
            $contents = $this->filesystem->read($workingDirRelativeFilepath);

            $updatedContents = $this->replaceInString($discoveredSymbols, $contents);

            if ($updatedContents !== $contents) {
                $this->changedFiles[ $workingDirRelativeFilepath ] = null;
                $this->filesystem->write($workingDirRelativeFilepath, $updatedContents);
            }
        }
    }

    
    public function replaceInString(DiscoveredSymbols $discoveredSymbols, string $contents): string
    {
        $namespacesChanges = $discoveredSymbols->getDiscoveredNamespaces($this->config->getNamespacePrefix());
        $classes = $discoveredSymbols->getDiscoveredClasses($this->config->getClassmapPrefix());
        $constants = $discoveredSymbols->getDiscoveredConstants($this->config->getConstantsPrefix());
        $functions = $discoveredSymbols->getDiscoveredFunctions();

        foreach ($classes as $originalClassname) {
            $classmapPrefix = $this->classmapPrefix;

            $contents = $this->replaceClassname($contents, $originalClassname, $classmapPrefix);
        }

        foreach ($namespacesChanges as $originalNamespace => $namespaceSymbol) {
            if (in_array($originalNamespace, $this->excludeNamespacesFromPrefixing)) {
                continue;
            }

            $contents = $this->replaceNamespace($contents, $originalNamespace, $namespaceSymbol->getReplacement());
        }

        if (!is_null($this->constantsPrefix)) {
            $contents = $this->replaceConstants($contents, $constants, $this->constantsPrefix);
        }

        foreach ($functions as $functionSymbol) {
            $contents = $this->replaceFunctions($contents, $functionSymbol);
        }

        return $contents;
    }

    
    public function replaceNamespace(string $contents, string $originalNamespace, string $replacement): string
    {

        $searchNamespace = '\\'.rtrim($originalNamespace, '\\') . '\\';
        $searchNamespace = str_replace('\\\\', '\\', $searchNamespace);
        $searchNamespace = str_replace('\\', '\\\\{0,2}', $searchNamespace);

        $pattern = "
            /                              # Start the pattern
            (
            ^\s*                          # start of the string
            |\\n\s*                        # start of the line
            |(<?php\s+namespace|^\s*namespace|[\r\n]+\s*namespace)\s+                  # the namespace keyword
            |use\s+                        # the use keyword
            |use\s+function\s+			   # the use function syntax
            |new\s+
            |static\s+
            |\"                            # inside a string that does not contain spaces - needs work
            |'                             #   right now its just inside a string that doesnt start with a space
            |implements\s+
            |extends\s+                    # when the class being extended is namespaced inline
            |return\s+
            |instanceof\s+                 # when checking the class type of an object in a conditional
            |\(\s*                         # inside a function declaration as the first parameters type
            |,\s*                          # inside a function declaration as a subsequent parameter type
            |\.\s*                         # as part of a concatenated string
            |=\s*                          # as the value being assigned to a variable
            |\*\s+@\w+\s*                  # In a comments param etc  
            |&\s*                             # a static call as a second parameter of an if statement
            |\|\s*
            |!\s*                             # negating the result of a static call
            |=>\s*                            # as the value in an associative array
            |\[\s*                         # In a square array 
            |\?\s*                         # In a ternary operator
            |:\s*                          # In a ternary operator
            |<                             # In a generic type declaration
            |\(string\)\s*                 # casting a namespaced class to a string
            )
            @?                             # Maybe preceeded by the @ symbol for error suppression
            (?<searchNamespace>
            {$searchNamespace}             # followed by the namespace to replace
            )
            (?!:)                          # Not followed by : which would only be valid after a classname
            (
            \s*;                           # followed by a semicolon 
            |\\\\{1,2}[a-zA-Z0-9_\x7f-\xff]{1,}         # or a classname no slashes 
            |\s+as                         # or the keyword as 
            |\"                            # or quotes
            |'                             # or single quote         
            |:                             # or a colon to access a static
            |\\\\{
            |>                             # In a generic type declaration (end)
            )                            
            /Ux";                          

        $replacingFunction = function ($matches) use ($originalNamespace, $replacement) {
            $singleBackslash = '\\';
            $doubleBackslash = '\\\\';

            if (false !== strpos($matches['0'], $doubleBackslash)) {
                $originalNamespace = str_replace($singleBackslash, $doubleBackslash, $originalNamespace);
                $replacement = str_replace($singleBackslash, $doubleBackslash, $replacement);
            }

            $replaced = str_replace($originalNamespace, $replacement, $matches[0]);

            return $replaced;
        };

        $result = preg_replace_callback($pattern, $replacingFunction, $contents);

        $matchingError = preg_last_error();
        if (0 !== $matchingError) {
            $message = "Matching error {$matchingError}";
            if (PREG_BACKTRACK_LIMIT_ERROR === $matchingError) {
                $message = 'Preg Backtrack limit was exhausted!';
            }
            throw new Exception($message);
        }

        
        
        
        $functionReplacingPattern = '/\\\\?('.preg_quote(ltrim($replacement, '\\'), '/').'\\\\(?:[a-zA-Z0-9_\x7f-\xff]+\\\\)*[a-zA-Z0-9_\x7f-\xff]+\\()/';
        $result = preg_replace(
            $functionReplacingPattern,
            "\\\\$1",
            $result
        );

        return $result;
    }

    
    public function replaceClassname(string $contents, string $originalClassname, string $classnamePrefix): string
    {
        $searchClassname = preg_quote($originalClassname, '/');

        
        $pattern = '
			/											# Start the pattern
				(^\s*namespace|\r\n\s*namespace)\s+[a-zA-Z0-9_\x7f-\xff\\\\]+\s*{(.*?)(namespace|\z) 
														# Look for a preceding namespace declaration, up until a 
														# potential second namespace declaration.
				|										# if found, match that much before continuing the search on
								    		        	# the remainder of the string.
                (^\s*namespace|\r\n\s*namespace)\s+[a-zA-Z0-9_\x7f-\xff\\\\]+\s*;(.*) # Skip lines just declaring the namespace.
                |		        	
				([^a-zA-Z0-9_\x7f-\xff\$\\\])('. $searchClassname . ')([^a-zA-Z0-9_\x7f-\xff\\\]) # outside a namespace the class will not be prefixed with a slash
				
			/xsm'; 

        $replacingFunction = function ($matches) use ($originalClassname, $classnamePrefix) {

            
            if (1 === preg_match('/\s*namespace\s+[a-zA-Z0-9_\x7f-\xff\\\\]+[;{\s\n]{1}.*/', $matches[0])) {
                $updated = $this->replaceGlobalClassInsideNamedNamespace(
                    $matches[0],
                    $originalClassname,
                    $classnamePrefix
                );

                return $updated;
            } else {
                $newContents = '';
                foreach ($matches as $index => $captured) {
                    if (0 === $index) {
                        continue;
                    }

                    if ($captured == $originalClassname) {
                        $newContents .= $classnamePrefix;
                    }

                    $newContents .= $captured;
                }
                return $newContents;
            }

        };

        $result = preg_replace_callback($pattern, $replacingFunction, $contents);

        if (is_null($result)) {
            throw new Exception('preg_replace_callback returned null');
        }

        $matchingError = preg_last_error();
        if (0 !== $matchingError) {
            $message = "Matching error {$matchingError}";
            if (PREG_BACKTRACK_LIMIT_ERROR === $matchingError) {
                $message = 'Backtrack limit was exhausted!';
            }
            throw new Exception($message);
        }

        return $result;
    }

    
    protected function replaceGlobalClassInsideNamedNamespace($contents, $originalClassname, $classnamePrefix): string
    {
        $replacement = $classnamePrefix . $originalClassname;

        
        $usePattern = '/
			(\s*use\s+)
			('.$originalClassname.')   # Followed by the classname
			\s*;
			/x'; 

        $contents = preg_replace_callback(
            $usePattern,
            function ($matches) use ($replacement) {
                return $matches[1] . $replacement . ' as '. $matches[2] . ';';
            },
            $contents
        );

        $bodyPattern =
            '/([^a-zA-Z0-9_\x7f-\xff]  # Not a class character
			\\\)                       # Followed by a backslash to indicate global namespace
			('.$originalClassname.')   # Followed by the classname
			([^\\\;]{1})               # Not a backslash or semicolon which might indicate a namespace
			/x'; 

        $contents = preg_replace_callback(
            $bodyPattern,
            function ($matches) use ($replacement) {
                return $matches[1] . $replacement . $matches[3];
            },
            $contents
        );

        return $contents;
    }

    
    protected function replaceConstants(string $contents, array $originalConstants, string $prefix): string
    {

        foreach ($originalConstants as $constant) {
            $contents = $this->replaceConstant($contents, $constant, $prefix . $constant);
        }

        return $contents;
    }

    protected function replaceConstant(string $contents, string $originalConstant, string $replacementConstant): string
    {
        return str_replace($originalConstant, $replacementConstant, $contents);
    }

    protected function replaceFunctions(string $contents, FunctionSymbol $functionSymbol): string
    {
        $originalFunctionString = $functionSymbol->getOriginalSymbol();
        $replacementFunctionString = $functionSymbol->getReplacement();

        if ($originalFunctionString === $replacementFunctionString) {
            return $contents;
        }

        $functionsUsingCallable = [
            'function_exists',
            'call_user_func',
            'call_user_func_array',
            'forward_static_call',
            'forward_static_call_array',
            'register_shutdown_function',
            'register_tick_function',
            'unregister_tick_function',
        ];



        $pattern = '/
			(\s*use\s+function\s+)('.preg_quote($originalFunctionString, '/').')(\s+as|\s+;) # use function as
			|
			|('.implode('|', $functionsUsingCallable).')(\s*\(\s*[\'"])('.preg_quote($originalFunctionString, '/').')([\'"]) # function related calls without closing bracket
			|
			(\s*function\s+)('.preg_quote($originalFunctionString, '/').')(\s*\() # function declaration
			|
			([;\s]+)('.preg_quote($originalFunctionString, '/').')(\s*\() # function call
			/x'; 

        $contents = preg_replace_callback(
            $pattern,
            function ($matches) use ($originalFunctionString, $replacementFunctionString) {
                foreach ($matches as $index => $match) {
                    if ($match == $originalFunctionString) {
                        $matches[$index] = $replacementFunctionString;
                    }
                }
                unset($matches[0]);
                return implode('', $matches);
            },
            $contents
        );

        return $contents;
    }

    
    public function getModifiedFiles(): array
    {
        return $this->changedFiles;
    }
}
