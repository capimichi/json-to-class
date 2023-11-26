<?php

namespace App\Factory;

use App\Entity\Element;
use App\Entity\ParsingInstance;
use App\Enum\ElementTypeEnum;
use App\Repository\ElementRepository;
use Doctrine\ORM\EntityManagerInterface;

class ElementFactory
{
    
    const TYPE_REPLACEMENTS = [
        ElementTypeEnum::TYPE_UNKNOWN => [
            ElementTypeEnum::TYPE_STRING,
            ElementTypeEnum::TYPE_INTEGER,
            ElementTypeEnum::TYPE_FLOAT,
            ElementTypeEnum::TYPE_BOOLEAN,
            ElementTypeEnum::TYPE_ARRAY,
            ElementTypeEnum::TYPE_OBJECT,
        ],
        ElementTypeEnum::TYPE_INTEGER => [
            ElementTypeEnum::TYPE_STRING,
            ElementTypeEnum::TYPE_FLOAT,
        ],
        ElementTypeEnum::TYPE_FLOAT   => [
            ElementTypeEnum::TYPE_STRING,
        ],
        ElementTypeEnum::TYPE_BOOLEAN => [
            ElementTypeEnum::TYPE_STRING,
        ],
    ];
    
    /**
     * @var ElementRepository
     */
    protected $elementRepository;
    
    /**
     * @var EntityManagerInterface
     */
    protected $em;
    
    /**
     * ElementFactory constructor.
     *
     * @param ElementRepository      $elementRepository
     * @param EntityManagerInterface $em
     */
    public function __construct(ElementRepository $elementRepository, EntityManagerInterface $em)
    {
        $this->elementRepository = $elementRepository;
        $this->em = $em;
    }
    
    
    public function createOrUpdateElement(
        ParsingInstance $parsingInstance,
        $path,
        $name,
        $type,
        $nullable,
        $parent = null
    )
    {
        $em = $this->em;
        $element = $this->elementRepository->getElementByParsingInstancePath(
            $parsingInstance,
            $path
        );
        $shouldFlush = false;
        
        if (!$element) {
            $element = new Element();
            $element->setParsingInstance($parsingInstance);
            $element->setPath($path);
            $element->setName($name);
            $element->setType($type);
            $element->setNullable($nullable);
            $em->persist($element);
            $shouldFlush = true;
        }
        
        if (!$element->getParent() || $element->getParent()->getId() !== $parent->getId()) {
            $element->setParent($parent);
            $shouldFlush = true;
        }
        
        if ($nullable && !$element->isNullable()) {
            $element->setNullable($nullable);
            $shouldFlush = true;
        }
        
        if (array_key_exists($element->getType(), self::TYPE_REPLACEMENTS)) {
            if (in_array($type, self::TYPE_REPLACEMENTS[$element->getType()])) {
                $element->setType($type);
                $shouldFlush = true;
            }
        }
        
        if ($shouldFlush) {
            $em->flush();
        }
        
        return $element;
    }
}