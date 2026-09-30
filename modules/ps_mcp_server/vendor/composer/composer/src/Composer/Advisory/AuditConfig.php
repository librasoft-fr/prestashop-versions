<?php declare(strict_types=1);



namespace Composer\Advisory;


class AuditConfig
{
    
    public $audit;

    
    public $auditFormat;

    
    public function __construct(bool $audit = true, string $auditFormat = Auditor::FORMAT_SUMMARY)
    {
        $this->audit = $audit;
        $this->auditFormat = $auditFormat;
    }
}
