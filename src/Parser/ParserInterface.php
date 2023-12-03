<?php

namespace App\Parser;

use App\Entity\ParsingInstance;

interface ParserInterface
{
    
    /**
     * @param ParsingInstance $parsingInstance
     * @param string          $rootName
     * @param string          $content
     * @param callable|null   $callback
     *
     * @return array
     */
    public function parse(
        ParsingInstance $parsingInstance,
        $rootName,
        $content,
        $callback = null
    );
    
}