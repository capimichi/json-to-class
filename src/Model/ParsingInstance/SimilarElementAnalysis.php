<?php


namespace App\Model\ParsingInstance;


use App\Entity\ParsingInstance;
use App\Enum\ElementTypeEnum;
use App\Model\Element\SimilarityComparison;

class SimilarElementAnalysis
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
    
    public function getSimilarityComparisons()
    {
        $elements = $this->parsingInstance->getElements();
        $objectElements = array_filter($elements, function ($element) {
            return $element->getType() === ElementTypeEnum::TYPE_OBJECT;
        });
        
        $comparisons = [];
        foreach ($objectElements as $objectElementLoop1) {
            foreach ($objectElements as $objectElementLoop2) {
                $comparison = new SimilarityComparison($objectElementLoop1, $objectElementLoop2);
                if ($comparison->isSameElement()) {
                    continue;
                }
                $id = $comparison->getSimilarityComparisonId();
                $comparisons[$id] = $comparison;
            }
        }
        $comparisons = array_values($comparisons);
        return $comparisons;
    }
}