<?php

namespace App\FileManager;

use App\Entity\ParsingInstance;
use Symfony\Component\Filesystem\Filesystem;

class ParsingInstanceFileManager extends FileManagerAbstract
{
    
    const INPUT_FILE_NAME = 'input.json';
    
    /**
     * The ParsingInstance object.
     *
     * @var ParsingInstance
     */
    protected $parsingInstance;
    
    /**
     * Gets the ParsingInstance object.
     *
     * @return ParsingInstance The ParsingInstance object.
     */
    public function getParsingInstance(): ParsingInstance
    {
        return $this->parsingInstance;
    }
    
    /**
     * Sets the ParsingInstance object.
     *
     * @param ParsingInstance $parsingInstance The ParsingInstance object.
     *
     * @return void
     */
    public function setParsingInstance(ParsingInstance $parsingInstance): void
    {
        $this->parsingInstance = $parsingInstance;
    }
    
    /**
     * Gets the target directory where files will be stored.
     * Appends the ParsingInstance id as an additional directory.
     *
     * @return string The target directory.
     */
    public function getTargetDirectory(): string
    {
        if (!$this->parsingInstance) {
            throw new \Exception('ParsingInstance not set.');
        }
        return sprintf('%s/parsing-instance/%s', $this->targetDirectory, $this->parsingInstance->getId());
    }
    
    
}