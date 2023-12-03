<?php

namespace App\Enum;

class ExportTypeEnum
{
    const JSON_SCHEMA_TYPE  = 'json_schema';
    const PTYHON_MODEL_TYPE = 'python_model';
    
    
    public static function getChoices(): array
    {
        return [
            'JSON Schema'  => self::JSON_SCHEMA_TYPE,
            'Python model' => self::PTYHON_MODEL_TYPE,
        ];
    }
}