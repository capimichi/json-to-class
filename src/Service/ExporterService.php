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
    
    public function export($exporter, $parsingInstance, $path)
    {
        if (!isset($this->exporters[$exporter])) {
            throw new \Exception('Exporter not found');
        }
        $this->exporters[$exporter]->export($parsingInstance, $path);
    }
    
}