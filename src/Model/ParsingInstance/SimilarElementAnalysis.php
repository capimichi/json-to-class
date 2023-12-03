<?php


namespace App\Model\ParsingInstance;


use App\Entity\Element;
use App\Entity\ParsingInstance;
use App\Enum\ElementTypeEnum;
use App\Model\Element\ElementGroup;
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
    
    public function getElementGroups($threshold = 0.5)
    {
        $groups = [];
        
        $elements = $this->parsingInstance->getElements()->toArray();
        $objectElements = array_filter($elements, function ($element) {
            return $element->getType() === ElementTypeEnum::TYPE_OBJECT;
        });
        
        foreach ($objectElements as $objectElementLoop1) {
            
            $elementGroup = null;
            /** @var ElementGroup $group */
            foreach ($groups as $group) {
                if ($group->canAddElement($objectElementLoop1, $threshold)) {
                    $group->addElement($objectElementLoop1);
                    $elementGroup = $group;
                    break;
                }
            }
            
            if (!$elementGroup) {
                $elementGroup = new ElementGroup();
                $elementGroup->addElement($objectElementLoop1);
                $groups[] = $elementGroup;
            }
        }
        
        // filter out groups with only one element
        $groups = array_filter($groups, function (ElementGroup $group) {
            return count($group->getElements()) > 1;
        });
        
        return $groups;
    }
    
    public function getSimilarityComparisonsFromSourceElement(Element $element, $threshold = 0.5)
    {
        $comparisons = $this->getSimilarityComparisons($threshold);
        $comparisons = array_filter($comparisons, function (SimilarityComparison $comparison) use ($element) {
            return $comparison->getSourceElement()->getId() === $element->getId();
        });
        return $comparisons;
    }
    
    public function getSimilarityComparisonsToTargetElement(Element $element, $threshold = 0.5)
    {
        $comparisons = $this->getSimilarityComparisons($threshold);
        $comparisons = array_filter($comparisons, function (SimilarityComparison $comparison) use ($element) {
            return $comparison->getTargetElement()->getId() === $element->getId();
        });
        return $comparisons;
    }
    
    public function getSimilarityComparisons($threshold = 0.5)
    {
        $elements = $this->parsingInstance->getElements()->toArray();
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
                
                $similarity = $comparison->getSimilarity();
                if ($similarity < $threshold) {
                    continue;
                }
                
                $comparisons[$id] = $comparison;
            }
        }
        $comparisons = array_values($comparisons);
        
        // sort comparisons by getSimilarity
        usort($comparisons, function (SimilarityComparison $comparison1, SimilarityComparison $comparison2) {
            return $comparison2->getSimilarity() <=> $comparison1->getSimilarity();
        });
        
        return $comparisons;
    }
}