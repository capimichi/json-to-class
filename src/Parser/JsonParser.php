<?php

namespace App\Parser;

use App\Entity\Element;
use App\Entity\ParsingInstance;
use App\Enum\ElementTypeEnum;
use App\Factory\ElementFactory;
use App\Helper\ArrayHelper;
use Doctrine\Inflector\InflectorFactory;
use Doctrine\ORM\EntityManagerInterface;

class JsonParser implements ParserInterface
{
    
    
    /**
     * @var ElementFactory
     */
    protected $elementFactory;
    
    /**
     * JsonParser constructor.
     *
     * @param ElementFactory $elementFactory
     */
    public function __construct(ElementFactory $elementFactory)
    {
        $this->elementFactory = $elementFactory;
    }
    
    /**
     * @param ParsingInstance $parsingInstance
     * @param string          $rootName
     * @param string          $content
     *
     * @return array
     */
    public function parse(
        ParsingInstance $parsingInstance,
        $rootName,
        $content
    )
    {
        $data = json_decode($content, true);
//        $totalCount = ArrayHelper::countNestedArrayKeys($data);
//        $index = 0;
        $this->parseElement(
            $parsingInstance,
            $rootName,
            $data
        );
    }
    
    protected function parseElement(
        ParsingInstance $parsingInstance,
        $name,
        $data,
        $path = '',
        $parent = null,
        $callback = null
    )
    {
        $type = ElementTypeEnum::TYPE_UNKNOWN;
        
        if (is_array($data)) {
            $arrayIsList = array_is_list($data);
            if ($arrayIsList) {
                $type = ElementTypeEnum::TYPE_ARRAY;
            } else {
                $type = ElementTypeEnum::TYPE_OBJECT;
            }
        } else {
            if (is_float($data)) {
                $type = ElementTypeEnum::TYPE_FLOAT;
            } elseif (is_int($data)) {
                $type = ElementTypeEnum::TYPE_INTEGER;
            } elseif (is_bool($data)) {
                $type = ElementTypeEnum::TYPE_BOOLEAN;
            } elseif (is_string($data)) {
                $type = ElementTypeEnum::TYPE_STRING;
            }
        }
        
        $nullable = is_null($data);
        $path = $path ? $path . '.' . $name : $name;
        
        $element = $this->elementFactory->createOrUpdateElement(
            $parsingInstance,
            $path,
            $name,
            $type,
            $nullable,
            $parent
        );
        
        if ($type === ElementTypeEnum::TYPE_ARRAY) {
            $singularName = $this->singularize($name);
            foreach ($data as $index => $value) {
                $listElement = $this->parseElement(
                    $parsingInstance,
                    $singularName,
                    $value,
                    $path,
                    $element
                );
            }
        } elseif ($type === ElementTypeEnum::TYPE_OBJECT) {
            
            $expectedNames = [];
            foreach ($element->getChildren() as $child) {
                $expectedNames[] = $child->getName();
            }
            
            foreach ($data as $key => $value) {
                $this->parseElement(
                    $parsingInstance,
                    $key,
                    $value,
                    $path,
                    $element
                );
            }
            
            $newNames = array_keys($data);
            
            $differentNames = ArrayHelper::compareStringArrays(
                $expectedNames,
                $newNames
            );
            
            foreach ($differentNames as $differentName) {
                printf("I should set %s to nullable\n", $differentName);
            }
            
        }

//        printf("Parsed element %s\n", $element->getPath());
        
        return $element;
    }
    
    private function singularize($name)
    {
        $inflector = InflectorFactory::create()->build();
        return $inflector->singularize($name);
    }
    
}