<?php

namespace App\Controller;

use App\Entity\ParsingInstance;
use App\Factory\ParsingInstanceFactory;
use App\Form\ExportParsingInstanceFormType;
use App\Form\ParsingInstanceFormType;
use App\Model\ParsingInstance\SimilarElementAnalysis;
use App\Model\ParsingInstance\UnknownTypeAnalysis;
use App\Repository\ParsingInstanceRepository;
use App\Service\ExporterService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ParsingInstanceController extends AbstractController
{
    #[Route('/parsing-instances/{slug}', name: 'app_parsing_instance_single')]
    public function singleAction(
        $slug,
        ParsingInstanceRepository $repository,
        Request $request,
        ExporterService $exporterService
    )
    {
        $parsingInstance = $repository->findOneBy(['slug' => $slug]);
        $unknownTypeAnalysis = new UnknownTypeAnalysis($parsingInstance);
        
        $exportForm = $this->createForm(ExportParsingInstanceFormType::class, []);
        $exportForm->handleRequest($request);
        if ($exportForm->isSubmitted() && $exportForm->isValid()) {
            $exportData = $exportForm->getData();
            $exportType = $exportData['exportType'];
            
            $path = $exporterService->export($exportType, $parsingInstance, [
                'prefix' => $exportData['prefix'],
            ]);
            
            return $this->file($path);
        }
        
        
        return $this->render('Default/page/parsing_instance_single.html.twig', [
            'parsingInstance'     => $parsingInstance,
            'unknownTypeAnalysis' => $unknownTypeAnalysis,
            'exportForm'          => $exportForm->createView(),
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
    
    #[Route('/parsing-instances/{slug}/similar-elements', name: 'app_parsing_instance_single_similar_elements')]
    public function similarElementsAction($slug, ParsingInstanceRepository $repository)
    {
        $parsingInstance = $repository->findOneBy(['slug' => $slug]);
        $similarElementAnalysis = new SimilarElementAnalysis($parsingInstance);
        
        return $this->render('Default/page/parsing_instance_similar_elements_single.html.twig', [
            'parsingInstance'        => $parsingInstance,
            'similarElementAnalysis' => $similarElementAnalysis,
            'form'                   => $this->createForm(ParsingInstanceFormType::class, null)->createView(),
        ]);
    }
}
