<?php

namespace Adamski\Symfony\DirectoryBundle\Model;

use JsonSerializable;
use SplFileInfo;

class Directory implements JsonSerializable {

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $pathName;

    /**
     * @var int
     */
    protected $owner;

    /**
     * @var int
     */
    protected $permissions;

    /**
     * @var int
     */
    protected $accessTime;

    /**
     * @var int
     */
    protected $modificationTime;

    /**
     * @var int
     */
    protected $changeTime;

    /**
     * @var bool
     */
    protected $writable;

    /**
     * @var bool
     */
    protected $readable;

    /**
     * @var int
     */
    protected $directoriesCounter;

    /**
     * @var int
     */
    protected $filesCounter;

    /**
     * @var int
     */
    protected $summarySize;

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name) {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath(string $path) {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPathName() {
        return $this->pathName;
    }

    /**
     * @param string $pathName
     */
    public function setPathName(string $pathName) {
        $this->pathName = $pathName;
    }

    /**
     * @return bool|string
     */
    public function getRealPath() {
        return realpath(
            $this->getPathName()
        );
    }

    /**
     * @return int
     */
    public function getOwner() {
        return $this->owner;
    }

    /**
     * @param int $owner
     */
    public function setOwner(int $owner) {
        $this->owner = $owner;
    }

    /**
     * @return int
     */
    public function getPermissions() {
        return $this->permissions;
    }

    /**
     * @param int $permissions
     */
    public function setPermissions(int $permissions) {
        $this->permissions = $permissions;
    }

    /**
     * @return int
     */
    public function getAccessTime() {
        return $this->accessTime;
    }

    /**
     * @param int $accessTime
     */
    public function setAccessTime(int $accessTime) {
        $this->accessTime = $accessTime;
    }

    /**
     * @return int
     */
    public function getModificationTime() {
        return $this->modificationTime;
    }

    /**
     * @param int $modificationTime
     */
    public function setModificationTime(int $modificationTime) {
        $this->modificationTime = $modificationTime;
    }

    /**
     * @return int
     */
    public function getChangeTime() {
        return $this->changeTime;
    }

    /**
     * @param int $changeTime
     */
    public function setChangeTime(int $changeTime) {
        $this->changeTime = $changeTime;
    }

    /**
     * @return bool
     */
    public function isWritable() {
        return $this->writable;
    }

    /**
     * @param bool $writable
     */
    public function setWritable(bool $writable) {
        $this->writable = $writable;
    }

    /**
     * @return bool
     */
    public function isReadable() {
        return $this->readable;
    }

    /**
     * @param bool $readable
     */
    public function setReadable(bool $readable) {
        $this->readable = $readable;
    }

    /**
     * @return int
     */
    public function getDirectoriesCounter() {
        return $this->directoriesCounter;
    }

    /**
     * @param int $directoriesCounter
     */
    public function setDirectoriesCounter(int $directoriesCounter) {
        $this->directoriesCounter = $directoriesCounter;
    }

    /**
     * @return int
     */
    public function getFilesCounter() {
        return $this->filesCounter;
    }

    /**
     * @param int $filesCounter
     */
    public function setFilesCounter(int $filesCounter) {
        $this->filesCounter = $filesCounter;
    }

    /**
     * @return int
     */
    public function getSummaryCounter() {
        return $this->getFilesCounter() + $this->getDirectoriesCounter();
    }

    /**
     * @return int
     */
    public function getSummarySize() {
        return $this->summarySize;
    }

    /**
     * @param int $summarySize
     */
    public function setSummarySize(int $summarySize) {
        $this->summarySize = $summarySize;
    }

    /**
     * @return string
     */
    public function getHash() {
        return md5($this->getPathName());
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize() {
        return [
            "type"               => "directory",
            "name"               => $this->getName(),
            "owner"              => $this->getOwner(),
            "permissions"        => $this->getPermissions(),
            "accessTime"         => $this->getAccessTime(),
            "modificationTime"   => $this->getModificationTime(),
            "changeTime"         => $this->getChangeTime(),
            "isWritable"         => $this->isWritable(),
            "isReadable"         => $this->isReadable(),
            "directoriesCounter" => $this->getDirectoriesCounter(),
            "filesCounter"       => $this->getFilesCounter(),
            "summaryCounter"     => $this->getSummaryCounter(),
            "summarySize"        => $this->getSummarySize(),
            "hash"               => $this->getHash()
        ];
    }

    /**
     * @param SplFileInfo $fileInfo
     * @return Directory
     */
    public static function parse(SplFileInfo $fileInfo) {
        $currentDirectory = new Directory();
        $currentDirectory->setName($fileInfo->getFilename());
        $currentDirectory->setPath($fileInfo->getPath());
        $currentDirectory->setPathName($fileInfo->getPathname());
        $currentDirectory->setOwner($fileInfo->getOwner());
        $currentDirectory->setPermissions($fileInfo->getPerms());
        $currentDirectory->setAccessTime($fileInfo->getATime());
        $currentDirectory->setModificationTime($fileInfo->getMTime());
        $currentDirectory->setChangeTime($fileInfo->getCTime());
        $currentDirectory->setWritable($fileInfo->isWritable());
        $currentDirectory->setReadable($fileInfo->isReadable());

        return $currentDirectory;
    }
}
