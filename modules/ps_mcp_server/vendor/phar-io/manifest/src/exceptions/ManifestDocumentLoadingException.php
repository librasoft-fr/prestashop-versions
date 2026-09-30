<?php declare(strict_types = 1);

namespace PharIo\Manifest;

use LibXMLError;
use function sprintf;

class ManifestDocumentLoadingException extends \Exception implements Exception {
    
    private $libxmlErrors;

    
    public function __construct(array $libxmlErrors) {
        $this->libxmlErrors = $libxmlErrors;
        $first              = $this->libxmlErrors[0];

        parent::__construct(
            sprintf(
                '%s (Line: %d / Column: %d / File: %s)',
                $first->message,
                $first->line,
                $first->column,
                $first->file
            ),
            $first->code
        );
    }

    
    public function getLibxmlErrors(): array {
        return $this->libxmlErrors;
    }
}
