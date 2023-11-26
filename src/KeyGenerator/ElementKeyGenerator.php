<?php

namespace App\KeyGenerator;

use App\Entity\ParsingInstance;

class ElementKeyGenerator
{
    
    public static function getParsingInstancePathKey(ParsingInstance $parsingInstance, $path)
    {
        $key = [
            $parsingInstance->getId(),
            $path,
        ];
        
        $key = implode('.', $key);
        
        return $key;
    }
}