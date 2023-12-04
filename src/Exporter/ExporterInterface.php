<?php

namespace App\Exporter;

interface ExporterInterface
{
    
    /**
     * @param \App\Entity\ParsingInstance $parsingInstance
     * @param array                       $options
     *
     * @return string
     */
    public function export(
        \App\Entity\ParsingInstance $parsingInstance,
        $options = []
    );
}