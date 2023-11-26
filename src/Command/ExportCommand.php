<?php

namespace App\Command;

use App\Entity\ParsingInstance;
use App\Parser\ParserInterface;
use App\Service\ExporterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:export',
    description: 'Add a short description for your command',
)]
class ExportCommand extends Command
{
    
    /**
     * @var ParserInterface
     */
    protected $parser;
    
    /**
     * @var EntityManagerInterface
     */
    protected $em;
    
    /**
     * @var ExporterService
     */
    protected $exporterService;
    
    public function __construct(
        ParserInterface $parser,
        EntityManagerInterface $em,
        ExporterService $exporterService
    )
    {
        $this->parser = $parser;
        $this->em = $em;
        $this->exporterService = $exporterService;
        parent::__construct();
    }
    
    protected function configure(): void
    {
        $this
            ->addArgument('exporter', InputArgument::REQUIRED, 'Exporter')
            ->addArgument('parsingInstance', InputArgument::REQUIRED, 'Parsing instance')
            ->addArgument('exportPath', InputArgument::REQUIRED, 'Export path');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $em = $this->em;
        
        $exporter = $input->getArgument('exporter');
        $parsingInstanceId = $input->getArgument('parsingInstance');
        $exportPath = $input->getArgument('exportPath');
        
        $parsingInstance = $em->getRepository(ParsingInstance::class)->find($parsingInstanceId);
        if (!$parsingInstance) {
            $io->error('Parsing instance not found');
            return Command::FAILURE;
        }
        
        $this->exporterService->export(
            $exporter,
            $parsingInstance,
            $exportPath
        );
        
        return Command::SUCCESS;
    }
}
