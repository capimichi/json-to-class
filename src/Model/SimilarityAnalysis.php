<?php

namespace App\Model;

use App\Entity\Element;

class SimilarityAnalysis
{
    
    /**
     * @var Element[]
     */
    protected $elements;
    
    /**
     * SimilarityAnalysis constructor.
     *
     * @param Element[] $elements
     */
    public function __construct(array $elements)
    {
        $this->setElements($elements);
    }
    
    /**
     * @return Element[]
     */
    public function getElements()
    {
        return $this->elements;
    }
    
    /**
     * @param Element[] $elements
     */
    public function setElements($elements)
    {
        $elements = array_filter($elements, function (Element $element) {
            return $element->getType() == 'object';
        });
        $this->elements = $elements;
    }
    
    /**
     * @return SimilarityComparison[]
     */
    public function getAllSimilarityComparisons()
    {
        $similarityComparisons = [];
        $comparisonKeys = [];
        $elements = $this->getElements();
        
        foreach ($elements as $objectFirstLoop) {
            foreach ($elements as $objectSecondLoop) {
                
                $similarityComparison = new SimilarityComparison();
                $similarityComparison->setSourceElement($objectFirstLoop);
                $similarityComparison->setTargetElement($objectSecondLoop);
                
                if ($similarityComparison->isSameElement()) {
                    continue;
                }
                
                if ($similarityComparison->isInSameJoinGroup()) {
                    continue;
                }
                
                $comparisonKey = $similarityComparison->getUniqueKey();
                if (in_array($comparisonKey, $comparisonKeys)) {
                    continue;
                }
                $comparisonKeys[] = $comparisonKey;
                
                $similarityComparisons[] = $similarityComparison;
            }
        }
        
        return $similarityComparisons;
    }
    
    
}