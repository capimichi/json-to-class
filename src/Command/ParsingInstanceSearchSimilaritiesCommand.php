<?php

namespace App\Command;

use App\Entity\Element;
use App\Entity\ParsingInstance;
use App\Model\SimilarityAnalysis;
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
    name: 'app:parsing-instance:search-similarities',
    description: 'Add a short description for your command',
)]
class ParsingInstanceSearchSimilaritiesCommand extends Command
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
            ->addArgument('parsingInstance', InputArgument::REQUIRED, 'Parsing instance')
            ->addOption('sortBy', null, InputOption::VALUE_OPTIONAL, 'Sort by', 'similarFields')
        ;
    }
    
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $em = $this->em;
        
        $parsingInstanceId = $input->getArgument('parsingInstance');
        $parsingInstance = $em->getRepository(ParsingInstance::class)->find($parsingInstanceId);
        if (!$parsingInstance) {
            $io->error('Parsing instance not found');
            return Command::FAILURE;
        }
        
        $elements = $em->getRepository(Element::class)->findBy([
            'parsingInstance' => $parsingInstance,
        ]);
        
        $similarityAnalysis = new SimilarityAnalysis($elements);
        $similarities = $similarityAnalysis->getAllSimilarityComparisons();
        
        // filter $similarities without similar names
        $similarities = array_filter($similarities, function (SimilarityComparison $similarity) {
            return count($similarity->getSimilarNames()) > 0;
        });
        
        // sort by similarity desc
        usort($similarities, function (SimilarityComparison $a, SimilarityComparison $b) {
            return count($b->getSimilarNames()) <=> count($a->getSimilarNames());
        });
        
        $io->table(['Source', 'Target', 'Similar names', 'Difference names'], array_map(function (SimilarityComparison $similarity) {
            return [
                $similarity->getSourceName(),
                $similarity->getTargetName(),
                count($similarity->getSimilarNames()),
                count($similarity->getDifferenceNames()),
            ];
        }, $similarities));
        
        return Command::SUCCESS;
    }
}
