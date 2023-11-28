<?php

namespace App\FileManager;

use Symfony\Component\Filesystem\Filesystem;

abstract class FileManagerAbstract
{

    /**
     * The target directory where files will be stored.
     *
     * @var string
     */
    protected $targetDirectory;

    /**
     * The filesystem component.
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * ParsingInstanceFileManager constructor.
     *
     * @param string     $targetDirectory
     * @param Filesystem $filesystem
     */
    public function __construct(string $targetDirectory, Filesystem $filesystem)
    {
        $this->targetDirectory = $targetDirectory;
        $this->filesystem = $filesystem;
    }

    /**
     * Saves the given data to a file with the given filename.
     * The file is saved in a directory structure based on the filename.
     * If the directories do not exist, they are created.
     *
     * @param string $filename The name of the file.
     * @param string $data The data to save to the file.
     * @return string The filename of the saved file.
     */
    public function saveDataToFile(string $filename, string $data): string
    {
        $this->filesystem->dumpFile($this->getPath($filename), $data);

        return $filename;
    }

    /**
     * Reads data from a file with the given filename.
     * The file is expected to be in a directory structure based on the filename.
     *
     * @param string $filename The name of the file.
     * @return string The data read from the file.
     */
    public function readDataFromFile(string $filename): string
    {
        return file_get_contents($this->getPath($filename));
    }

    /**
     * Checks if a file with the given filename exists.
     * The file is expected to be in a directory structure based on the filename.
     *
     * @param string $filename The name of the file.
     * @return bool True if the file exists, false otherwise.
     */
    public function fileExists(string $filename): bool
    {
        return $this->filesystem->exists($this->getPath($filename));
    }

    /**
     * Gets the filesystem component.
     *
     * @return Filesystem The filesystem component.
     */
    public function getFilesystem(): Filesystem
    {
        return $this->filesystem;
    }

    /**
     * Sets the filesystem component.
     *
     * @param Filesystem $filesystem The filesystem component.
     * @return void
     */
    public function setFilesystem(Filesystem $filesystem): void
    {
        $this->filesystem = $filesystem;
    }

    /**
     * Gets the target directory where files will be stored.
     *
     * @return string The target directory.
     */
    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }

    /**
     * Sets the target directory where files will be stored.
     *
     * @param string $targetDirectory The target directory.
     * @return void
     */
    public function setTargetDirectory(string $targetDirectory): void
    {
        $this->targetDirectory = $targetDirectory;
    }

    /**
     * Returns the full path to the file, creating necessary subdirectories.
     *
     * @param string $filename
     *
     * @return string
     */
    public function getPath(string $filename): string
    {
        $directory1 = substr($filename, 0, 2);
        $directory2 = substr($filename, 2, 2);
        $path = sprintf('%s/%s/%s', $this->getTargetDirectory(), $directory1, $directory2);

        if (!$this->filesystem->exists($path)) {
            $this->filesystem->mkdir($path);
        }

        return sprintf('%s/%s', $path, $filename);
    }

    
}