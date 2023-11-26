<?php

namespace App\Enum;

class SearchSimilaritiesSortEnum
{
    const SORT_BY_SIMILAR_FIELDS   = 'similar_fields';
    const SORT_BY_DIFFERENT_FIELDS = 'different_fields';
    
    
    /**
     * @return array
     */
    public static function getSorts()
    {
        return [
            self::SORT_BY_SIMILAR_FIELDS,
            self::SORT_BY_DIFFERENT_FIELDS,
        ];
    }
}