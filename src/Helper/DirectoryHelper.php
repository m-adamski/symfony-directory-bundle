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
     * @param string $cacheDir
     * @param string $logsDir
     */
    public function __construct(string $projectDir, string $cacheDir, string $logsDir) {
        $this->projectDirectory = $projectDir;
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
     * Generate name from provided items and extension.
     *
     * @param string|null $extension
     * @param string      ...$items
     * @return string
     */
    public function joinName(?string $extension, string ...$items) {
        $responseName = implode("", $items);

        return null !== $extension && "" !== $extension ? $responseName . "." . $extension : $responseName;
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
     * @param string|Directory $directory
     * @return Directory[]
     */
    public function getDirectories($directory) {
        if ($directory instanceof Directory) {
            $directory = $directory->getRealPath();
        }

        // Define array with directories to response
        $responseDirectories = [];

        // Define iterator
        $directoryIterator = new DirectoryIterator($directory);

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
     * @param string|Directory      $directory
     * @param string|Directory|null $baseDirectory
     * @param string|null           $baseHost
     * @return File[]
     */
    public function getFiles($directory, $baseDirectory = null, ?string $baseHost = null) {
        if ($directory instanceof Directory) {
            $directory = $directory->getRealPath();
        }

        if (null !== $baseDirectory && $baseDirectory instanceof Directory) {
            $baseDirectory = $baseDirectory->getRealPath();
        }

        // Define array with files to response
        $responseFiles = [];

        // Define iterator
        $directoryIterator = new DirectoryIterator($directory);

        foreach ($directoryIterator as $currentItem) {
            if ($currentItem->isFile() && !$currentItem->isDot()) {
                if (null !== ($currentFile = $this->parse($currentItem, $baseDirectory, $baseHost))) {
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
     * @param string|null $baseDirectory
     * @param string|null $baseHost
     * @return Directory|File|null
     */
    public function parse(SplFileInfo $fileInfo, ?string $baseDirectory = null, ?string $baseHost = null) {
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
            return File::parse($fileInfo, $baseDirectory, $baseHost);
        }

        return null;
    }

    /**
     * Parse item from specified path into matching object.
     *
     * @param string      $path
     * @param string|null $baseDirectory
     * @return Directory|File|null
     */
    public function parseFromPath(string $path, ?string $baseDirectory = null) {
        if (file_exists($path)) {
            return $this->parse(
                new SplFileInfo($path), $baseDirectory
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
     * Copy provided file to new specified directory.
     *
     * @param string|File      $file
     * @param string|Directory $directory
     * @param bool             $overwrite
     * @param bool             $rename
     * @param string           $renamePostfix
     * @return bool
     */
    public function copyFile($file, $directory, bool $overwrite = false, bool $rename = true, string $renamePostfix = "_copy") {
        $filePath = $file instanceof File ? $file->getRealPath() : $file;
        $directoryPath = $directory instanceof Directory ? $directory->getRealPath() : $directory;

        if (file_exists($filePath) && file_exists($directoryPath) && is_dir($directoryPath)) {
            $currentFile = $this->parseFromPath($filePath);
            $currentFileName = $currentFile->getNameWithoutExtension();
            $currentFileExtension = $currentFile->getExtension();

            // Generate destination file path
            $destinationPath = $this->joinPath($directoryPath, $currentFile->getName());

            // Generate file name with postfix until generated name
            // will be not used in destination directory
            while (file_exists($destinationPath) && true === $rename) {
                $currentFileName .= $renamePostfix;
                $destinationPath = $this->joinPath($directoryPath, $this->joinName($currentFileExtension, $currentFileName));
            }

            // Check if file with the same name do not exist in final path
            // or if overwrite parameter is set to true
            if (!file_exists($destinationPath) || (file_exists($destinationPath) && $overwrite)) {
                return copy($filePath, $destinationPath);
            }
        }

        return false;
    }

    /**
     * Rename provided file with specified name.
     *
     * @param string|File $file
     * @param string      $newName
     * @return bool
     */
    public function renameFile($file, string $newName) {
        $filePath = $file instanceof File ? $file->getRealPath() : $file;

        if (file_exists($filePath)) {
            $file = $this->parseFromPath($filePath);

            // Generate final file path with new name
            $fileFinalPath = $this->joinPath($file->getPath(), $newName . "." . $file->getExtension());

            if (!file_exists($fileFinalPath)) {
                return rename($filePath, $fileFinalPath);
            }
        }

        return false;
    }

    /**
     * Move provided file to new specified directory.
     *
     * @param string|File      $file
     * @param string|Directory $directory
     * @param bool             $overwrite
     * @param bool             $rename
     * @param string           $renamePostfix
     * @return bool
     */
    public function moveFile($file, $directory, bool $overwrite = false, bool $rename = true, string $renamePostfix = "_copy") {
        $filePath = $file instanceof File ? $file->getRealPath() : $file;

        if (true === $this->copyFile($file, $directory, $overwrite, $rename, $renamePostfix)) {
            return $this->removeFile($filePath);
        }

        return false;
    }

    /**
     * Find files which specified parameter matching provided pattern.
     *
     * @param string|Directory $directory
     * @param string           $pattern
     * @param string           $param
     * @return array|null
     * @throws Exception
     */
    public function findFilesByRegex($directory, string $pattern, string $param = "name") {
        $directoryPath = $directory instanceof Directory ? $directory->getRealPath() : $directory;

        if (file_exists($directoryPath) && is_dir($directoryPath)) {
            $directory = $this->parseFromPath($directoryPath);

            // Define collection of matching files
            $responseCollection = [];

            // Find file which parameter matching specified pattern
            foreach ($this->getFiles($directory) as $currentFile) {
                if (method_exists($currentFile, "get" . $param)) {
                    if (true === (bool)preg_match($pattern, $currentFile->{"get" . $param}())) {
                        $responseCollection[] = $currentFile;
                    }
                } else {
                    throw new Exception("The file object has no function defined to retrieve the value of '" . $param . "' parameter");
                }
            }

            // Move recursive
            foreach ($this->getDirectories($directory) as $currentDirectory) {
                if (null !== ($recursiveResponse = $this->findFilesByRegex($currentDirectory, $pattern, $param))) {
                    $responseCollection = array_merge($responseCollection, $recursiveResponse);
                }
            }

            // Return collection
            return $responseCollection;
        }

        return null;
    }

    /**
     * Find one file which specified parameter matching provided pattern.
     * The function will return null if more than one matching object is found.
     *
     * @param string|Directory $directory
     * @param string           $pattern
     * @param string           $param
     * @return mixed|null
     * @throws Exception
     */
    public function findOneFileByPattern($directory, string $pattern, string $param = "name") {
        $matchingCollection = $this->findFilesByRegex($directory, $pattern, $param);

        if (is_array($matchingCollection) && count($matchingCollection) === 1) {
            return $matchingCollection[0];
        }

        return null;
    }

    /**
     * Attempts to create the directory specified by pathname with provided mode.
     *
     * @param string $pathname
     * @param int    $mode
     * @param bool   $recursive
     * @return bool|null
     */
    public function createDirectory(string $pathname, int $mode = 0775, bool $recursive = true) {
        try {
            if (!file_exists($pathname)) {
                return mkdir($pathname, $mode, $recursive);
            }

            return false;
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
     * Rename provided directory with specified name.
     *
     * @param string|Directory $directory
     * @param string           $newName
     * @return bool
     */
    public function renameDirectory($directory, string $newName) {
        $directoryPath = $directory instanceof Directory ? $directory->getRealPath() : $directory;

        if (file_exists($directoryPath) && is_dir($directoryPath)) {
            $directory = $this->parseFromPath($directoryPath);

            // Generate final directory path with new name
            $directoryFinalPath = $this->joinPath($directory->getPath(), $newName);

            if (!file_exists($directoryFinalPath)) {
                return rename($directoryPath, $directoryFinalPath);
            }
        }

        return false;
    }

    /**
     * Find directories which specified parameter matching provided pattern.
     *
     * @param string|Directory $directory
     * @param string           $pattern
     * @param string           $param
     * @return array|null
     * @throws Exception
     */
    public function findDirectoriesByPattern($directory, string $pattern, string $param = "name") {
        $directoryPath = $directory instanceof Directory ? $directory->getRealPath() : $directory;

        if (file_exists($directoryPath) && is_dir($directoryPath)) {
            $directory = $this->parseFromPath($directoryPath);

            // Define collection of matching directories
            $responseCollection = [];

            // Check if exist directory in current path matching specified conditions
            foreach ($this->getDirectories($directory) as $currentDirectory) {
                if (method_exists($currentDirectory, "get" . $param)) {
                    if (true === (bool)preg_match($pattern, $currentDirectory->{"get" . $param}())) {
                        $responseCollection[] = $currentDirectory;
                    }
                } else {
                    throw new Exception("The directory object has no function defined to retrieve the value of '" . $param . "' parameter");
                }
            }

            // Move recursive to find matching directory
            foreach ($this->getDirectories($directory) as $currentDirectory) {
                if (null !== ($recursiveResponse = $this->findDirectoriesByPattern($currentDirectory, $pattern, $param))) {
                    $responseCollection = array_merge($responseCollection, $recursiveResponse);
                }
            }

            // Return collection
            return $responseCollection;
        }

        return null;
    }

    /**
     * Find one directory which specified parameter matching provided pattern.
     * The function will return null if more than one matching object is found.
     *
     * @param string|Directory $directory
     * @param string           $pattern
     * @param string           $param
     * @return mixed|null
     * @throws Exception
     */
    public function findOneDirectoryByPattern($directory, string $pattern, string $param = "name") {
        $matchingCollection = $this->findDirectoriesByPattern($directory, $pattern, $param);

        if (is_array($matchingCollection) && count($matchingCollection) === 1) {
            return $matchingCollection[0];
        }

        return null;
    }

    /**
     * Copy provided directory into destination.
     * Depending on the settings, the files will be overwritten or the folder name will be changed.
     *
     * @param string|Directory $directory
     * @param string|Directory $destination
     * @param bool             $overwrite
     * @param bool             $rename
     * @param string           $renamePostfix
     * @return bool
     */
    public function copyDirectory($directory, $destination, bool $overwrite = false, bool $rename = true, string $renamePostfix = "_copy") {
        $directoryPath = $directory instanceof Directory ? $directory->getRealPath() : $directory;
        $destinationPath = $destination instanceof Directory ? $destination->getRealPath() : $destination;

        if (file_exists($directoryPath) && is_dir($directoryPath) && file_exists($destinationPath) && is_dir($destinationPath)) {
            $currentDirectory = $this->parseFromPath($directoryPath);
            $currentDirectoryName = $currentDirectory->getName();

            // Define destination directory path
            $directoryDestinationPath = $this->joinPath($destinationPath, $currentDirectoryName);

            // Generate directory name with postfix until generated name
            // will be not used in destination directory
            while (file_exists($directoryDestinationPath) && true === $rename) {
                $currentDirectoryName .= $renamePostfix;
                $directoryDestinationPath = $this->joinPath($destinationPath, $currentDirectoryName);
            }

            // Create directory if not already exist
            if (!file_exists($directoryDestinationPath) || !is_dir($directoryDestinationPath)) {
                if (false === $this->createDirectory($directoryDestinationPath)) {
                    return false;
                }
            }

            // Copy files
            foreach ($this->getFiles($directoryPath) as $currentFile) {
                $this->copyFile($currentFile, $directoryDestinationPath, $overwrite, $rename, $renamePostfix);
            }

            // Copy children directories
            foreach ($this->getDirectories($directoryPath) as $currentDirectory) {
                $this->copyDirectory($currentDirectory, $directoryDestinationPath, $rename, $renamePostfix);
            }

            return true;
        }

        return false;
    }

    /**
     * Move provided directory into destination.
     * Depending on the settings, the files will be overwritten or the folder name will be changed.
     *
     * @param string|Directory $directory
     * @param string|Directory $destination
     * @param bool             $overwrite
     * @param bool             $rename
     * @param string           $renamePostfix
     * @return bool
     */
    public function moveDirectory($directory, $destination, bool $overwrite = false, bool $rename = true, string $renamePostfix = "_copy") {
        if (true === $this->copyDirectory($directory, $destination, $overwrite, $rename, $renamePostfix)) {
            return $this->removeDirectory($directory, true);
        }

        return false;
    }

    /**
     * Generate tree with directories and files stored in provided directory.
     * Depending on the settings, the root directory can also be included.
     *
     * @param string|Directory $directory
     * @param string|null      $baseDirectory
     * @param bool             $includeRoot
     * @return array
     */
    public function tree($directory, ?string $baseDirectory = null, bool $includeRoot = false) {
        $directoryPath = $directory instanceof Directory ? $directory->getRealPath() : $directory;

        // Define response array
        $responseArray = [];

        foreach ($this->getDirectories($directoryPath) as $currentDirectory) {
            $responseArray[] = [
                "item"     => $currentDirectory,
                "children" => $this->tree($currentDirectory)
            ];
        }

        foreach ($this->getFiles($directory) as $currentFile) {
            $responseArray[] = [
                "item" => $currentFile
            ];
        }

        if (true === $includeRoot) {
            return [[
                "item"     => $this->parseFromPath($directoryPath, $baseDirectory),
                "children" => $responseArray
            ]];
        }

        return $responseArray;
    }

    /**
     * Rename file or directory with specified name.
     *
     * @param string|File|Directory $item
     * @param string                $newName
     * @return bool
     */
    public function rename($item, string $newName) {
        if ($item instanceof File || (file_exists($item) && !is_dir($item))) {
            return $this->renameFile($item, $newName);
        } else if ($item instanceof Directory || (file_exists($item) && is_dir($item))) {
            return $this->renameDirectory($item, $newName);
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
