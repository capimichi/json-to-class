<?php

namespace App\Controller;

use App\Entity\ParsingInstance;
use App\Factory\ParsingInstanceFactory;
use App\Form\ParsingInstanceFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'app_index')]
    public function index(Request $request, ParsingInstanceFactory $parsingInstanceFactory)
    {
        $parsingInstance = new ParsingInstance();
        $form = $this->createForm(ParsingInstanceFormType::class, $parsingInstance);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            
            $parsingInstanceFactory->createParsingInstanceFromForm($form);
            
            return $this->redirectToRoute('app_parsing_instance_single', [
                'slug' => $parsingInstance->getSlug(),
            ]);
        }
        
        return $this->render('Default/page/index.html.twig', [
            'controller_name' => 'DefaultController',
            'form'            => $form->createView(),
        ]);
    }
}
