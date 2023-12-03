<?php

namespace App\Service;

use App\Exporter\ExporterInterface;

class ExporterService
{
    
    /**
     * @var ExporterInterface[]
     */
    protected $exporters;
    
    /**
     * ExporterService constructor.
     *
     * @param ExporterInterface[] $exporters
     */
    public function __construct(array $exporters)
    {
        $this->exporters = $exporters;
    }
    
    public function export($exporterType, $parsingInstance)
    {
        if (!isset($this->exporters[$exporterType])) {
            throw new \Exception('Exporter not found');
        }
        return $this->exporters[$exporterType]->export($parsingInstance);
    }
    
}