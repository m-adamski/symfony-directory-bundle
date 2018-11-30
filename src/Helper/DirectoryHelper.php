<?php

namespace Adamski\Symfony\DirectoryBundle\Helper;

use Adamski\Symfony\DirectoryBundle\Model\Directory;
use Adamski\Symfony\DirectoryBundle\Model\File;
use DirectoryIterator;
use Exception;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class DirectoryHelper {

    const RECURSIVE_ALL = 0;
    const RECURSIVE_FILES_ONLY = 1;
    const RECURSIVE_DIRECTORIES_ONLY = 2;

    /**
     * @var string
     */
    protected $projectDirectory;

    /**
     * @var string
     */
    protected $sourceDirectory;

    /**
     * @var string
     */
    protected $cacheDirectory;

    /**
     * @var string
     */
    protected $logsDirectory;

    /**
     * @var string
     */
    protected $publicDirectory;

    /**
     * @var string
     */
    protected $publicDirectoryName = "public";

    /**
     * DirectoryHelper constructor.
     *
     * @param string $projectDir
     * @param string $rootDir
     * @param string $cacheDir
     * @param string $logsDir
     */
    public function __construct(string $projectDir, string $rootDir, string $cacheDir, string $logsDir) {
        $this->projectDirectory = $projectDir;
        $this->sourceDirectory = $rootDir;
        $this->cacheDirectory = $cacheDir;
        $this->logsDirectory = $logsDir;
        $this->publicDirectory = $this->projectDirectory . DIRECTORY_SEPARATOR . $this->publicDirectoryName;
    }

    /**
     * Get Kernel Project directory.
     *
     * @return string
     */
    public function getProjectDirectory() {
        return $this->projectDirectory;
    }

    /**
     * Get Kernel Source directory.
     *
     * @return string
     */
    public function getSourceDirectory() {
        return $this->sourceDirectory;
    }

    /**
     * Get Kernel Cache directory.
     *
     * @return string
     */
    public function getCacheDirectory() {
        return $this->cacheDirectory;
    }

    /**
     * Get Kernel Logs directory.
     *
     * @return string
     */
    public function getLogsDirectory() {
        return $this->logsDirectory;
    }

    /**
     * @return string
     */
    public function getPublicDirectory() {
        return $this->publicDirectory;
    }

    /**
     * Create path based on specified items.
     *
     * @param string ...$items
     * @return string
     */
    public function joinPath(string ...$items) {
        return implode(DIRECTORY_SEPARATOR, $items);
    }

    /**
     * Generate a recursive list of items from specified directory.
     * Collection can be limited to files or directories only.
     *
     * @param string|Directory $directory
     * @param int              $mode
     * @return array
     */
    public function getRecursiveList($directory, int $mode = self::RECURSIVE_ALL) {
        if ($directory instanceof Directory) {
            $directory = $directory->getRealPath();
        }

        // Define response array
        $responseData = [];

        // Define iterator
        $directoryIterator = new RecursiveDirectoryIterator($directory);
        $itemsCollection = new RecursiveIteratorIterator($directoryIterator, RecursiveIteratorIterator::SELF_FIRST);

        foreach ($itemsCollection as $item) {
            if ($item->getFilename() !== "." && $item->getFilename() !== "..") {
                if (
                    $mode !== self::RECURSIVE_ALL && (
                        ($item->isDir() && $mode === self::RECURSIVE_FILES_ONLY) ||
                        ($item->isFile() && $mode === self::RECURSIVE_DIRECTORIES_ONLY)
                    )
                ) {
                    continue;
                }

                if (null !== ($currentItem = $this->parse($item))) {
                    $responseData[] = $currentItem;
                }
            }
        }

        return $responseData;
    }

    /**
     * Get collection with directories from specified path.
     *
     * @param string $path
     * @return Directory[]
     */
    public function getDirectories(string $path) {

        // Define array with directories to response
        $responseDirectories = [];

        // Define iterator
        $directoryIterator = new DirectoryIterator($path);

        foreach ($directoryIterator as $currentItem) {
            if ($currentItem->isDir() && !$currentItem->isDot()) {
                if (null !== ($currentDirectory = $this->parse($currentItem))) {
                    $responseDirectories[] = $currentDirectory;
                }
            }
        }

        return $responseDirectories;
    }

    /**
     * Get collection with files stored in provided directory.
     *
     * @param string|Directory $directory
     * @return File[]
     */
    public function getFiles($directory) {
        if ($directory instanceof Directory) {
            $directory = $directory->getRealPath();
        }

        // Define array with files to response
        $responseFiles = [];

        // Define iterator
        $directoryIterator = new DirectoryIterator($directory);

        foreach ($directoryIterator as $currentItem) {
            if ($currentItem->isFile() && !$currentItem->isDot()) {
                if (null !== ($currentFile = $this->parse($currentItem))) {
                    $responseFiles[] = $currentFile;
                }
            }
        }

        return $responseFiles;
    }

    /**
     * Create temporary file in specified path.
     * Windows use only the first three characters of prefix.
     *
     * @param string $directoryPath
     * @param string $prefix
     * @return bool|string
     */
    public function createTemporaryFile(string $directoryPath, string $prefix) {
        if (file_exists($directoryPath) && is_dir($directoryPath)) {
            return tempnam($directoryPath, $prefix);
        }

        return false;
    }

    /**
     * Parse provided item into matching object.
     *
     * @param SplFileInfo $fileInfo
     * @return Directory|File|null
     */
    public function parse(SplFileInfo $fileInfo) {
        if ($fileInfo->isDir()) {
            $currentDirectory = Directory::parse($fileInfo);

            // Define additional parameters
            $filesCounter = 0;
            $directoriesCounter = 0;
            $summarySize = 0;

            foreach ($this->getRecursiveList($currentDirectory) as $item) {
                if ($item instanceof Directory) {
                    $directoriesCounter++;
                } else if ($item instanceof File) {
                    $filesCounter++;
                    $summarySize += $item->getSize();
                }
            }

            $currentDirectory->setFilesCounter($filesCounter);
            $currentDirectory->setDirectoriesCounter($directoriesCounter);
            $currentDirectory->setSummarySize($summarySize);

            return $currentDirectory;
        } else if ($fileInfo->isFile()) {
            return File::parse($fileInfo);
        }

        return null;
    }

    /**
     * Parse item from specified path into matching object.
     *
     * @param string $path
     * @return Directory|File|null
     */
    public function parseFromPath(string $path) {
        if (file_exists($path)) {
            return $this->parse(
                new SplFileInfo($path)
            );
        }

        return null;
    }

    /**
     * Write content into specified file.
     *
     * @param string|File $file
     * @param string      $content
     * @return bool
     */
    public function writeFile($file, string $content) {
        if ($file instanceof File) {
            $file = $file->getRealPath();
        }

        return file_put_contents($file, $content) !== false;
    }

    /**
     * Remove file.
     *
     * @param string|File $file
     * @return bool
     */
    public function removeFile($file) {
        if ($file instanceof File) {
            $file = $file->getRealPath();
        }

        if (file_exists($file)) {
            return unlink($file);
        }

        return false;
    }

    /**
     * Attempts to create the directory specified by pathname with provided mode.
     *
     * @param string $pathname
     * @param int    $mode
     * @param bool   $recursive
     * @return string|null
     */
    public function createDirectory(string $pathname, int $mode = 0775, bool $recursive = true) {
        try {
            if (!file_exists($pathname)) {
                mkdir($pathname, $mode, $recursive);
            }

            return $pathname;
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * Remove directory.
     *
     * @param string|Directory $directory
     * @param bool             $recursive
     * @return bool
     */
    public function removeDirectory($directory, bool $recursive = false) {
        if ($directory instanceof Directory) {
            $directory = $directory->getRealPath();
        }

        if (file_exists($directory) && is_dir($directory)) {

            // Get collection of children
            $childrenCollection = $this->getRecursiveList($directory);

            if (false === $recursive) {
                if (is_array($childrenCollection) && count($childrenCollection) <= 0) {
                    return rmdir($directory);
                }

                return false;
            }

            // Reverse children collection
            $childrenCollection = array_reverse($childrenCollection);

            // Define final status
            $responseStatus = true;

            // Move every item and remove it
            foreach ($childrenCollection as $item) {
                if (false === $responseStatus) {
                    break;
                }

                if ($item instanceof Directory) {
                    $responseStatus = $this->removeDirectory($item);
                } else if ($item instanceof File) {
                    $responseStatus = $this->removeFile($item);
                }
            }

            // Remove base path
            if (true === $responseStatus) {
                return rmdir($directory);
            }

            return $responseStatus;
        }

        return false;
    }

    /**
     * Get real-path for specified path.
     *
     * @param string $path
     * @return bool|string
     */
    public function getRealPath(string $path) {
        return realpath($path);
    }
}
