<?php


namespace App\Exporter;


use App\Entity\Element;
use App\Enum\ElementTypeEnum;
use App\Enum\PythonTypeEnum;
use App\EnumElementTypeEnum;
use Doctrine\Inflector\InflectorFactory;
use Twig\Environment;

class PythonPydanticExporter extends PythonModelExporter implements ExporterInterface
{
    
    protected function getClassTemplatePath()
    {
        return 'Exporter/python_pydantic/class.py.twig';
    }
    
}