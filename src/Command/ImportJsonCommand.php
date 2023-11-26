<?php

namespace App\Command;

use App\Entity\ParsingInstance;
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
    name: 'app:import:json',
    description: 'Add a short description for your command',
)]
class ImportJsonCommand extends Command
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
            ->addArgument('jsonPath', InputArgument::REQUIRED, 'Path to JSON file or URL')
            ->addOption('rootName', null, InputOption::VALUE_OPTIONAL, 'Root name of JSON file', 'root')
            ->addOption('parsingInstance', null, InputOption::VALUE_OPTIONAL, 'Parsing instance', 0);
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $em = $this->em;
        
        $jsonPath = $input->getArgument('jsonPath');
        $rootName = $input->getOption('rootName');
        
        $parsingInstanceId = $input->getOption('parsingInstance');
        $parsingInstance = $em->getRepository(ParsingInstance::class)->find($parsingInstanceId);
        if (!$parsingInstance) {
            $parsingInstance = new ParsingInstance();
            $slug = md5(strtotime('now') . rand(0, 10000000));
            $parsingInstance->setSlug($slug);
            $em->persist($parsingInstance);
        }
        $em->flush();
        
        $content = file_get_contents($jsonPath);
        
        $this->parser->parse(
            $parsingInstance,
            $rootName,
            $content
        );
        
        return Command::SUCCESS;
    }
}
