<?php

namespace App\Model\ParsingInstance;

use App\Entity\Element;
use App\Entity\ParsingInstance;
use App\Enum\ElementTypeEnum;

class UnknownTypeAnalysis
{
    
    
    /**
     * @var ParsingInstance
     */
    protected $parsingInstance;
    
    /**
     * UnknownTypeAnalysis constructor.
     *
     * @param ParsingInstance $parsingInstance
     */
    public function __construct(ParsingInstance $parsingInstance)
    {
        $this->parsingInstance = $parsingInstance;
    }
    
    public function getUnknownTypeElements()
    {
        $elements = $this->parsingInstance->getElements();
        $unknownTypeElements = [];
        foreach ($elements as $element) {
            if ($element->getType() === ElementTypeEnum::TYPE_UNKNOWN) {
                $unknownTypeElements[] = $element;
            }
        }
        
        // sort by path asc
        usort($unknownTypeElements, function ($a, $b) {
            return strcmp($a->getPath(), $b->getPath());
        });
        
        return $unknownTypeElements;
    }
    
    public function getUnknownTypeElementsCount()
    {
        return count($this->getUnknownTypeElements());
    }
    
    public function getUnknownTypeElementParents()
    {
        $parents = [];
        $elements = $this->parsingInstance->getElements();
        /** @var Element $element */
        foreach ($elements as $element) {
            if ($element->getType() === ElementTypeEnum::TYPE_UNKNOWN) {
                if ($element->getParent()) {
                    $parents[$element->getParent()->getId()] = $element->getParent();
                }
            }
        }
        
        $parents = array_values($parents);
        // sort by path asc
        usort($parents, function ($a, $b) {
            return strcmp($a->getPath(), $b->getPath());
        });
        
        return $parents;
    }
    
    public function getUnknownTypeElementChildren(Element $element)
    {
        $children = [];
        $elements = $element->getChildren();
        /** @var Element $element */
        foreach ($elements as $element) {
            if ($element->getType() === ElementTypeEnum::TYPE_UNKNOWN) {
                $children[$element->getId()] = $element;
            }
        }
        
        $children = array_values($children);
        // sort by path asc
        usort($children, function ($a, $b) {
            return strcmp($a->getPath(), $b->getPath());
        });
        
        return $children;
    }
    
    public function getCountUnknownTypeElementChildren(Element $element)
    {
        return count($this->getUnknownTypeElementChildren($element));
    }
    
}