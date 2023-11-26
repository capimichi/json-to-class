<?php

namespace App\Command;

use App\Entity\Element;
use App\Entity\JoinGroup;
use App\Entity\ParsingInstance;
use App\Model\SimilarityComparison;
use App\Parser\ParserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:parsing-instance:element:join',
    description: 'Add a short description for your command',
)]
class ParsingInstanceElementJoinCommand extends Command
{
    
    /**
     * @var ParserInterface
     */
    protected $parser;
    
    /**
     * @var EntityManagerInterface
     */
    protected $em;
    
    public function __construct(
        ParserInterface $parser,
        EntityManagerInterface $em
    )
    {
        $this->parser = $parser;
        $this->em = $em;
        parent::__construct();
    }
    
    protected function configure(): void
    {
        $this
//            ->addArgument('parsingInstance', InputArgument::REQUIRED, 'Parsing instance')
            ->addArgument('sourceElement', InputArgument::REQUIRED, 'Source element')
            ->addArgument('targetElement', InputArgument::REQUIRED, 'Target element')
            ->addArgument('name', InputArgument::REQUIRED, 'Name')
            ->addArgument('path', InputArgument::REQUIRED, 'Path');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $em = $this->em;

//        $parsingInstanceId = $input->getArgument('parsingInstance');
//        $parsingInstance = $em->getRepository(ParsingInstance::class)->find($parsingInstanceId);
//        if (!$parsingInstance) {
//            $io->error('Parsing instance not found');
//            return Command::FAILURE;
//        }
        
        $sourceElementId = $input->getArgument('sourceElement');
        /** @var Element $sourceElement */
        $sourceElement = $em->getRepository(Element::class)->find($sourceElementId);
        
        $targetElementId = $input->getArgument('targetElement');
        /** @var Element $targetElement */
        $targetElement = $em->getRepository(Element::class)->find($targetElementId);
        
        $name = $input->getArgument('name');
        $path = $input->getArgument('path');
        
        $existingGroups = [
            $sourceElement->getJoinGroup(),
            $targetElement->getJoinGroup(),
        ];
        $existingGroups = array_filter($existingGroups);
        
        if (count($existingGroups) > 1) {
            $io->error('Both elements already have a join group');
            return Command::FAILURE;
        }
        
        $joinGroup = array_shift($existingGroups);
        if (!$joinGroup) {
            $joinGroup = new JoinGroup();
            $em->persist($joinGroup);
        }
        $joinGroup->setName($name);
        $joinGroup->setPath($path);
        $em->flush();
        
        $sourceElement->setJoinGroup($joinGroup);
        $targetElement->setJoinGroup($joinGroup);
        $em->flush();
        
        return Command::SUCCESS;
    }
}
