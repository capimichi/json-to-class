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
    
    public static function isArrayDict($array)
    {
        // First, check if contains any numeric keys
        $hasNumericKeys = count(array_filter(array_keys($array), 'is_numeric')) > 0;
        if ($hasNumericKeys) {
            return true;
        }
        
        // Second, check if contains any strange characters in keys
        $checkChars = [
            ".",
            "-",
        ];
        
        $hasStrangeChars = count(array_filter(array_keys($array), function ($key) use ($checkChars) {
                foreach ($checkChars as $checkChar) {
                    if (strpos($key, $checkChar) !== false) {
                        return true;
                    }
                }
                return false;
            })) > 0;
        
        if ($hasStrangeChars) {
            return true;
        }
        
        return false;
    }
}