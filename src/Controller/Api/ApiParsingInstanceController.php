<?php

namespace App\Controller\Api;

use App\Entity\JoinGroup;
use App\Form\ParsingInstanceFormType;
use App\Model\ParsingInstance\SimilarElementAnalysis;
use App\Model\ParsingInstance\UnknownTypeAnalysis;
use App\Repository\ElementRepository;
use App\Repository\ParsingInstanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ApiParsingInstanceController extends AbstractController
{
    
    
    #[Route('(/api/parsing-instances/{slug}/groups', name: 'app_api_parsing_instance_single_groups')]
    public function apiParsingInstanceGroupsAction(
        $slug,
        ParsingInstanceRepository $repository,
        ElementRepository $elementRepository,
        EntityManagerInterface $em,
        Request $request
    )
    {
        $parsingInstance = $repository->findOneBy(['slug' => $slug]);
        if (!$parsingInstance) {
            throw $this->createNotFoundException();
        }
        
        $outputData = [];
        $dataItems = json_decode($request->getContent(), true);
        
        $alreadyFoundNames = [];
        $groupNames = array_column($dataItems, 'name');
        foreach ($groupNames as $groupName) {
            if (in_array($groupName, $alreadyFoundNames)) {
                return new JsonResponse([
                    'error' => 'Group names must be unique',
                ], 400);
            }
            $alreadyFoundNames[] = $groupName;
        }
        
        $rootElement = $elementRepository->findOneBy([
            'parsingInstance' => $parsingInstance,
            'parent'          => null,
        ]);
        foreach ($groupNames as $groupName) {
            $groupElement = $elementRepository->findOneBy([
                'parsingInstance' => $parsingInstance,
                'name'            => $groupName,
                'parent'          => $rootElement,
            ]);
            if ($groupElement) {
                return new JsonResponse([
                    'error' => 'Found an element with the same name as the group name',
                ], 400);
            }
        }
        
        foreach ($dataItems as $dataItem) {
            $groupName = $dataItem['name'];
            $elementIds = $dataItem['elementIds'];
            
            $dropGroupIds = [];
            foreach ($elementIds as $elementId) {
                $element = $elementRepository->find($elementId);
                if ($element->getJoinGroup()) {
                    $dropGroupIds[] = $element->getJoinGroup()->getId();
                }
                $element->setJoinGroup(null);
            }
            $em->flush();
            
            foreach ($dropGroupIds as $dropGroupId) {
                $dropGroup = $em->getRepository(JoinGroup::class)->find($dropGroupId);
                $em->remove($dropGroup);
            }
            $em->flush();
            
            $joinGroup = new JoinGroup();
            $joinGroup->setName($groupName);
            foreach ($elementIds as $elementId) {
                $element = $elementRepository->find($elementId);
                $joinGroup->addElement($element);
            }
            $em->persist($joinGroup);
            $em->flush();
            
            $outputData[] = [
                'id'   => $joinGroup->getId(),
                'name' => $joinGroup->getName(),
            ];
        }
        
        return new JsonResponse($outputData);
    }
}