<?php

namespace App\Model;

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
     * @return Element
     */
    public function getSourceElement()
    {
        return $this->sourceElement;
    }
    
    /**
     * @param Element $sourceElement
     */
    public function setSourceElement($sourceElement)
    {
        $this->sourceElement = $sourceElement;
    }
    
    /**
     * @return Element
     */
    public function getTargetElement()
    {
        return $this->targetElement;
    }
    
    /**
     * @param Element $targetElement
     */
    public function setTargetElement($targetElement)
    {
        $this->targetElement = $targetElement;
    }
    
    /**
     * @return string
     */
    public function getUniqueKey()
    {
        $ids = [
            $this->sourceElement->getId(),
            $this->targetElement->getId(),
        ];
        sort($ids);
        return implode('-', $ids);
    }
    
    /**
     * @return bool
     */
    public function isSameElement()
    {
        return $this->sourceElement->getId() === $this->targetElement->getId();
    }
    
    /**
     * @return bool
     */
    public function isInSameJoinGroup()
    {
        if (!$this->sourceElement->getJoinGroup()) {
            return false;
        }
        
        if (!$this->targetElement->getJoinGroup()) {
            return false;
        }
        
        return $this->sourceElement->getJoinGroup()->getId() === $this->targetElement->getJoinGroup()->getId();
    }
    
    /**
     * Get similar fields.
     *
     * @return array
     */
    public function getBothFieldElements()
    {
        $bothFieldElements = [];
        
        $sourceChildren = $this->sourceElement->getChildren();
        $targetChildren = $this->targetElement->getChildren();
        
        foreach ($sourceChildren as $sourceChild) {
            foreach ($targetChildren as $targetChild) {
                if ($sourceChild->getName() === $targetChild->getName()) {
                    $bothFieldElements[] = $targetChild;
                }
            }
        }
        
        return $bothFieldElements;
    }
    
    /**
     * Get similar fields.
     *
     * @return array
     */
    public function getDifferentFieldElements()
    {
        $differentFieldElements = [];
        
        $sourceChildren = $this->sourceElement->getChildren();
        $targetChildren = $this->targetElement->getChildren();
        
        foreach ($sourceChildren as $sourceChild) {
            $found = false;
            foreach ($targetChildren as $targetChild) {
                if ($sourceChild->getName() === $targetChild->getName()) {
                    $found = true;
                }
            }
            if (!$found) {
                $differentFieldElements[$sourceChild->getId()] = $sourceChild;
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
                $differentFieldElements[$targetChild->getId()] = $targetChild;
            }
        }
        
        $differentFieldElements = array_values($differentFieldElements);
        
        return $differentFieldElements;
    }
}
