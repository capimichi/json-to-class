<?php

namespace App\Model\Element;

use App\Entity\Element;

class SimilarityComparison
{
    
    /**
     * @var Element
     */
    protected $sourceElement;
    
    /**
     * @var Element
     */
    protected $targetElement;
    
    /**
     * SimilarityComparison constructor.
     *
     * @param Element $sourceElement
     * @param Element $targetElement
     */
    public function __construct(Element $sourceElement, Element $targetElement)
    {
        $this->sourceElement = $sourceElement;
        $this->targetElement = $targetElement;
    }
    
    public function getSimilarityComparisonId(): string
    {
        $id = [
            $this->getSourceElement()->getId(),
            $this->getTargetElement()->getId(),
        ];
        sort($id);
        return implode('-', $id);
    }
    
    /**
     * @return Element
     */
    public function getSourceElement(): Element
    {
        return $this->sourceElement;
    }
    
    /**
     * @return Element
     */
    public function getTargetElement(): Element
    {
        return $this->targetElement;
    }
    
    /**
     * @return bool
     */
    public function isSameElement(): bool
    {
        return $this->getSourceElement()->getId() == $this->getTargetElement()->getId();
    }
    
    public function getNameDistance(): int
    {
        $sourceName = $this->getSourceElement()->getName();
        $targetName = $this->getTargetElement()->getName();
        return levenshtein($sourceName, $targetName);
    }
    
    public function getFieldInBoths(): array
    {
        $sourceElement = $this->getSourceElement();
        $sourceChildren = $sourceElement->getChildren()->toArray();
        
        $targetElement = $this->getTargetElement();
        $targetChildren = $targetElement->getChildren()->toArray();
        
        $fieldsInBoth = [];
        foreach ($sourceChildren as $sourceChild) {
            foreach ($targetChildren as $targetChild) {
                if ($sourceChild->getName() === $targetChild->getName()) {
                    $fieldsInBoth[$sourceChild->getId()] = $sourceChild;
                }
            }
        }
        $fieldsInBoth = array_values($fieldsInBoth);
        return $fieldsInBoth;
    }
    
    public function getFieldsDifferences(): array
    {
        $sourceElement = $this->getSourceElement();
        $sourceChildren = $sourceElement->getChildren()->toArray();
        
        $targetElement = $this->getTargetElement();
        $targetChildren = $targetElement->getChildren()->toArray();
        
        $fieldsDifferences = [];
        foreach ($sourceChildren as $sourceChild) {
            $found = false;
            foreach ($targetChildren as $targetChild) {
                if ($sourceChild->getName() === $targetChild->getName()) {
                    $found = true;
                }
            }
            if (!$found) {
                $fieldsDifferences[$sourceChild->getId()] = $sourceChild;
            }
        }
        foreach ($targetChildren as $targetChild) {
            $found = false;
            foreach ($sourceChildren as $sourceChild) {
                if ($sourceChild->getName() === $targetChild->getName()) {
                    $found = true;
                }
            }
            if (!$found) {
                $fieldsDifferences[$targetChild->getId()] = $targetChild;
            }
        }
        $fieldsDifferences = array_values($fieldsDifferences);
        return $fieldsDifferences;
    }
    
    public function getFieldsDifferencesCount(): int
    {
        return count($this->getFieldsDifferences());
    }
    
    public function getFieldsInBothsCount(): int
    {
        return count($this->getFieldInBoths());
    }
    
    public function getFieldsCount(): int
    {
        return $this->getFieldsInBothsCount() + $this->getFieldsDifferencesCount();
    }
    
    public function getFieldsSimilarity(): float
    {
        $fieldsInBothsCount = $this->getFieldsInBothsCount();
        $fieldsCount = $this->getFieldsCount();
        if ($fieldsCount === 0) {
            return 0;
        }
        return $fieldsInBothsCount / $fieldsCount;
    }
    
    public function getNameSimilarity(): float
    {
        $nameDistance = $this->getNameDistance();
        $nameLength = max(strlen($this->getSourceElement()->getName()), strlen($this->getTargetElement()->getName()));
        if ($nameLength === 0) {
            return 0;
        }
        return 1 - ($nameDistance / $nameLength);
    }
    
    public function getSimilarity(): float
    {
        $fieldsSimilarity = $this->getFieldsSimilarity();
        $nameSimilarity = $this->getNameSimilarity();
        return ($fieldsSimilarity + $nameSimilarity) / 2;
    }
    
    public function getSourceChildren(): array
    {
        $children = $this->getSourceElement()->getChildren()->toArray();
        usort($children, function (Element $a, Element $b) {
            return $a->getName() <=> $b->getName();
        });
        return $children;
    }
    
    public function getTargetChildren(): array
    {
        $children = $this->getTargetElement()->getChildren()->toArray();
        usort($children, function (Element $a, Element $b) {
            return $a->getName() <=> $b->getName();
        });
        return $children;
    }
}