<?php

namespace App\Helper;

class ArrayHelper
{
    
    public static function compareStringArrays($array1, $array2)
    {
        $missingInArray1 = array_diff($array2, $array1);
        $missingInArray2 = array_diff($array1, $array2);
        
        $missing = array_merge($missingInArray1, $missingInArray2);
        $missing = array_unique($missing);
        $missing = array_values($missing);
        
        return $missing;
    }
    
    public static function countNestedArrayKeys($array)
    {
        $count = 0;
        foreach ($array as $key => $value) {
            $count++;
            if (is_array($value)) {
                $count += self::countNestedArrayKeys($value);
            }
        }
        return $count;
    }
}