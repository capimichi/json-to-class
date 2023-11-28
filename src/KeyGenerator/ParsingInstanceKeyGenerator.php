<?php

namespace App\KeyGenerator;

use App\Entity\ParsingInstance;

class ParsingInstanceKeyGenerator
{
    
    public static function createSlugKey(ParsingInstance $parsingInstance)
    {
        return md5(microtime() . rand(0, 1000000));
    }
}