<?php

namespace App\Controller;

use App\Entity\ParsingInstance;
use App\Factory\ParsingInstanceFactory;
use App\Form\ParsingInstanceFormType;
use App\Model\ParsingInstance\UnknownTypeAnalysis;
use App\Repository\ParsingInstanceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ParsingInstanceController extends AbstractController
{
    #[Route('/parsing-instances/{slug}', name: 'app_parsing_instance_single')]
    public function singleAction($slug, ParsingInstanceRepository $repository)
    {
        $parsingInstance = $repository->findOneBy(['slug' => $slug]);
        $unknownTypeAnalysis = new UnknownTypeAnalysis($parsingInstance);
        
        return $this->render('Default/page/parsing_instance_single.html.twig', [
            'parsingInstance'     => $parsingInstance,
            'unknownTypeAnalysis' => $unknownTypeAnalysis,
        ]);
    }
    
    #[Route('/parsing-instances/{slug}/unknown-types', name: 'app_parsing_instance_single_unknown_types')]
    public function unknownTypesAction($slug, ParsingInstanceRepository $repository)
    {
        $parsingInstance = $repository->findOneBy(['slug' => $slug]);
        $unknownTypeAnalysis = new UnknownTypeAnalysis($parsingInstance);
        
        return $this->render('Default/page/parsing_instance_unknown_types_single.html.twig', [
            'parsingInstance'     => $parsingInstance,
            'unknownTypeAnalysis' => $unknownTypeAnalysis,
        ]);
    }
}
