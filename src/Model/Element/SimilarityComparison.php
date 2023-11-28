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
    
    /**
     * @return bool
     */
    public function hasSameName(): bool
    {
        return $this->getSourceElement()->getName() == $this->getTargetElement()->getName();
    }
}