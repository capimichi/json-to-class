<?php

namespace App\Exporter;

interface ExporterInterface
{
    
    /**
     * @param \App\Entity\ParsingInstance $parsingInstance
     * @param                             $path
     *
     * @return mixed
     */
    public function export(
        \App\Entity\ParsingInstance $parsingInstance,
        $path
    );
}