<?php

namespace Adamski\Symfony\DirectoryBundle\Model;

use JsonSerializable;

class Directory implements JsonSerializable {
    protected string $name;
    protected string $path;
    protected string $pathName;
    protected int $owner;
    protected int $permissions;
    protected int $accessTime;
    protected int $modificationTime;
    protected int $changeTime;
    protected bool $writable;
    protected bool $readable;
    protected int $directoriesCounter;
    protected int $filesCounter;
    protected int $summarySize;

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getPath(): string {
        return $this->path;
    }

    public function setPath(string $path): void {
        $this->path = $path;
    }

    public function getPathName(): string {
        return $this->pathName;
    }

    public function setPathName(string $pathName): void {
        $this->pathName = $pathName;
    }

    public function getRealPath(): bool|string {
        return realpath(
            $this->getPathName()
        );
    }

    public function getOwner(): int {
        return $this->owner;
    }

    public function setOwner(int $owner): void {
        $this->owner = $owner;
    }

    public function getPermissions(): int {
        return $this->permissions;
    }

    public function setPermissions(int $permissions): void {
        $this->permissions = $permissions;
    }

    public function getAccessTime(): int {
        return $this->accessTime;
    }

    public function setAccessTime(int $accessTime): void {
        $this->accessTime = $accessTime;
    }

    public function getModificationTime(): int {
        return $this->modificationTime;
    }

    public function setModificationTime(int $modificationTime): void {
        $this->modificationTime = $modificationTime;
    }

    public function getChangeTime(): int {
        return $this->changeTime;
    }

    public function setChangeTime(int $changeTime): void {
        $this->changeTime = $changeTime;
    }

    public function isWritable(): bool {
        return $this->writable;
    }

    public function setWritable(bool $writable): void {
        $this->writable = $writable;
    }

    public function isReadable(): bool {
        return $this->readable;
    }

    public function setReadable(bool $readable): void {
        $this->readable = $readable;
    }

    public function getDirectoriesCounter(): int {
        return $this->directoriesCounter;
    }

    public function setDirectoriesCounter(int $directoriesCounter): void {
        $this->directoriesCounter = $directoriesCounter;
    }

    public function getFilesCounter(): int {
        return $this->filesCounter;
    }

    public function setFilesCounter(int $filesCounter): void {
        $this->filesCounter = $filesCounter;
    }

    public function getSummaryCounter(): int {
        return $this->getFilesCounter() + $this->getDirectoriesCounter();
    }

    public function getSummarySize(): int {
        return $this->summarySize;
    }

    public function setSummarySize(int $summarySize): void {
        $this->summarySize = $summarySize;
    }

    public function getHumanSummarySize(): ?string {
        $sizeCheck = [
            1             => "B",
            1024          => "kB",
            1048576       => "MB",
            1073741824    => "GB",
            1099511627776 => "TB"
        ];

        // Get summary size
        $summarySize = $this->getSummarySize();

        // Reverse array
        $sizeCheck = array_reverse($sizeCheck, true);

        // Move every item backwards
        foreach ($sizeCheck as $currentSize => $currentAbbreviation) {
            if ($summarySize > $currentSize) {
                return round($summarySize / $currentSize) . " " . $currentAbbreviation;
            }
        }

        return null;
    }

    public function getHash(): string {
        return md5($this->getPathName());
    }

    public function jsonSerialize(): array {
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
            "humanSummarySize"   => $this->getHumanSummarySize(),
            "hash"               => $this->getHash()
        ];
    }

    public static function parse(\SplFileInfo $fileInfo): Directory {
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
