<?php

namespace App\Exporter;

interface ExporterInterface
{
    
    /**
     * @param \App\Entity\ParsingInstance $parsingInstance
     * @param string                      $exportDir
     *
     * @return string
     */
    public function export(
        \App\Entity\ParsingInstance $parsingInstance
    );
}