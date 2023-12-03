<?php


namespace App\Model\Element;


use App\Entity\Element;

class ElementGroup
{
    
    /**
     * @var Element[]
     */
    protected $elements = [];
    
    public function canAddElement(Element $element, $threshold = 0.5)
    {
        if (empty($this->getElements())) {
            return true;
        }
        
        foreach ($this->getElements() as $elementLoop) {
            $similarityComparison = new SimilarityComparison($elementLoop, $element);
            if ($similarityComparison->getSimilarity() >= $threshold) {
                return true;
            }
        }
        
        return false;
    }
    
    public function addElement(Element $element)
    {
        $this->elements[] = $element;
    }
    
    /**
     * @return Element[]
     */
    public function getElements(): array
    {
        return $this->elements;
    }
    
    
}