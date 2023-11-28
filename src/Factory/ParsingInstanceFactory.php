<?php

namespace App\Factory;

use App\Entity\Element;
use App\Entity\ParsingInstance;
use App\Enum\ElementTypeEnum;
use App\Enum\ParsingInstanceStatusEnum;
use App\FileManager\ParsingInstanceFileManager;
use App\KeyGenerator\ParsingInstanceKeyGenerator;
use App\Repository\ElementRepository;
use App\Repository\ParsingInstanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;

class ParsingInstanceFactory
{
    
    /**
     * @var ParsingInstanceRepository
     */
    protected $parsingInstanceRepository;
    
    /**
     * @var EntityManagerInterface
     */
    protected $em;
    
    /**
     * @var ParsingInstanceFileManager
     */
    protected $parsingInstanceFileManager;
    
    /**
     * ParsingInstanceFactory constructor.
     *
     * @param ParsingInstanceRepository  $parsingInstanceRepository
     * @param EntityManagerInterface     $em
     * @param ParsingInstanceFileManager $parsingInstanceFileManager
     */
    public function __construct(ParsingInstanceRepository $parsingInstanceRepository, EntityManagerInterface $em, ParsingInstanceFileManager $parsingInstanceFileManager)
    {
        $this->parsingInstanceRepository = $parsingInstanceRepository;
        $this->em = $em;
        $this->parsingInstanceFileManager = $parsingInstanceFileManager;
    }
    
    public function createParsingInstanceFromForm(FormInterface $form)
    {
        /** @var ParsingInstance $parsingInstance */
        $parsingInstance = $form->getData();
        
        if (!$parsingInstance->getId()) {
            $parsingInstance->setSlug(ParsingInstanceKeyGenerator::createSlugKey($parsingInstance));
            $parsingInstance->setStatus(ParsingInstanceStatusEnum::STATUS_NEW);
            $this->em->persist($parsingInstance);
        }
        $this->em->flush();
        
        $jsonInput = $form->get('jsonInput')->getData();
        if (!empty($jsonInput)) {
            $this->parsingInstanceFileManager->setParsingInstance($parsingInstance);
            $this->parsingInstanceFileManager->saveDataToFile(ParsingInstanceFileManager::INPUT_FILE_NAME, $jsonInput);
        }
        
        return $parsingInstance;
    }
}