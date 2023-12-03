<?php

namespace App\Command;

use App\Entity\Element;
use App\Entity\JoinGroup;
use App\Entity\ParsingInstance;
use App\Enum\ParsingInstanceStatusEnum;
use App\FileManager\ParsingInstanceFileManager;
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
    name: 'app:parsing-instance:elaborate',
    description: 'Add a short description for your command',
)]
class ParsingInstanceElaborateCommand extends Command
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
     * @var ParsingInstanceFileManager
     */
    protected $parsingInstanceFileManager;
    
    public function __construct(
        ParserInterface $parser,
        EntityManagerInterface $em,
        ParsingInstanceFileManager $parsingInstanceFileManager
    )
    {
        $this->parser = $parser;
        $this->em = $em;
        $this->parsingInstanceFileManager = $parsingInstanceFileManager;
        parent::__construct();
    }
    
    protected function configure(): void
    {
        $this
            ->addOption('parsingInstance', 'p', InputOption::VALUE_REQUIRED, 'Parsing instance', null);
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $em = $this->em;
        
        $parsingInstanceId = $input->getOption('parsingInstance');
        if ($parsingInstanceId) {
            $parsingInstance = $em->getRepository(ParsingInstance::class)->find($parsingInstanceId);
        } else {
            $parsingInstance = $em->getRepository(ParsingInstance::class)->findOneBy(['status' => ParsingInstanceStatusEnum::STATUS_NEW]);
        }
        
        if (!$parsingInstance) {
            $io->error('Parsing instance not found');
            return Command::FAILURE;
        }
        
        $parsingInstance->setStatus(ParsingInstanceStatusEnum::STATUS_PROCESSING);
        
        $rootName = $parsingInstance->getRootName();
        
        $this->parsingInstanceFileManager->setParsingInstance($parsingInstance);
        $jsonContent = $this->parsingInstanceFileManager->readDataFromFile(ParsingInstanceFileManager::INPUT_FILE_NAME);
        
        try {
            $this->parser->parse($parsingInstance, $rootName, $jsonContent, function ($element) use ($io) {
                $io->writeln('Element: ' . $element->getName());
            });
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            $parsingInstance->setStatus(ParsingInstanceStatusEnum::STATUS_ERROR);
            $em->flush();
            return Command::FAILURE;
        }
        
        $parsingInstance->setStatus(ParsingInstanceStatusEnum::STATUS_COMPLETED);
        $em->flush();
        
        return Command::SUCCESS;
    }
}
