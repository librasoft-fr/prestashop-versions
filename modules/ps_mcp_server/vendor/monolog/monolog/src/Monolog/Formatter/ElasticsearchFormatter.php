<?php declare(strict_types=1);



namespace Monolog\Formatter;

use DateTimeInterface;


class ElasticsearchFormatter extends NormalizerFormatter
{
    
    protected $index;

    
    protected $type;

    
    public function __construct(string $index, string $type)
    {
        
        parent::__construct(DateTimeInterface::ISO8601);

        $this->index = $index;
        $this->type = $type;
    }

    
    public function format(array $record)
    {
        $record = parent::format($record);

        return $this->getDocument($record);
    }

    
    public function getIndex(): string
    {
        return $this->index;
    }

    
    public function getType(): string
    {
        return $this->type;
    }

    
    protected function getDocument(array $record): array
    {
        $record['_index'] = $this->index;
        $record['_type'] = $this->type;

        return $record;
    }
}
