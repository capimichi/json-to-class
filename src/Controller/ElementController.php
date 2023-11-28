<?php

namespace App\Controller;

use App\Entity\ParsingInstance;
use App\Factory\ParsingInstanceFactory;
use App\Form\ParsingInstanceFormType;
use App\Model\ParsingInstance\UnknownTypeAnalysis;
use App\Repository\ElementRepository;
use App\Repository\ParsingInstanceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ElementController extends AbstractController
{
    #[Route('/parsing-instances/{slug}/elements/{id}', name: 'app_element_single')]
    public function singleAction($slug, $id, ParsingInstanceRepository $parsingInstanceRepository, ElementRepository $elementRepository)
    {
        $parsingInstance = $parsingInstanceRepository->findOneBy(['slug' => $slug]);
        if (!$parsingInstance) {
            throw $this->createNotFoundException();
        }
        $element = $elementRepository->find($id);
        
        return $this->render('Default/page/element_single.html.twig', [
            'parsingInstance' => $parsingInstance,
            'element'         => $element,
        ]);
    }
}
