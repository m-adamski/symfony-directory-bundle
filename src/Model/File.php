<?php

namespace Adamski\Symfony\DirectoryBundle\Model;

use JsonSerializable;

class File implements JsonSerializable {
    protected string $name;
    protected string $path;
    protected ?string $basePath;
    protected ?string $baseHost;
    protected string $pathName;
    protected string $extension;
    protected int $owner;
    protected int $permissions;
    protected int $accessTime;
    protected int $modificationTime;
    protected int $changeTime;
    protected bool $writable;
    protected bool $readable;
    protected int $size;
    protected ?string $mimeType;

    public function __construct() {
        $this->basePath = null;
        $this->baseHost = null;
        $this->mimeType = null;
    }

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

    public function getBasePath(): ?string {
        return $this->basePath;
    }

    public function setBasePath(?string $basePath): void {
        $this->basePath = $basePath;
    }

    public function getBaseHost(): ?string {
        return $this->baseHost;
    }

    public function setBaseHost(?string $baseHost): void {
        $this->baseHost = $baseHost;
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

    public function getRelativePath(): ?string {
        if (null !== ($basePath = $this->getBasePath())) {
            if (false !== ($realPath = $this->getRealPath())) {
                return preg_replace("/^" . preg_quote($basePath, "/") . "/", "", $realPath);
            }
        }

        return null;
    }

    public function getExtension(): string {
        return $this->extension;
    }

    public function setExtension(string $extension): void {
        $this->extension = $extension;
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

    public function getSize(): int {
        return $this->size;
    }

    public function setSize(int $size): void {
        $this->size = $size;
    }

    public function getHumanSize(): ?string {
        $sizeCheck = [
            1             => "B",
            1024          => "kB",
            1048576       => "MB",
            1073741824    => "GB",
            1099511627776 => "TB"
        ];

        // Get summary size
        $summarySize = $this->getSize();

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

    public function getMimeType(): ?string {
        return $this->mimeType;
    }

    public function setMimeType(?string $mimeType): void {
        $this->mimeType = $mimeType;
    }

    public function getNameWithoutExtension(): ?string {
        if (null !== ($currentExtension = $this->getExtension())) {
            if ("" !== $currentExtension) {
                return preg_replace("/" . preg_quote("." . $currentExtension, "/") . "$/", "", $this->getName());
            }
        }

        return null;
    }

    public function getUrl(): ?string {
        if (null !== ($baseHost = $this->getBaseHost())) {
            if (null !== ($relativePath = $this->getRelativePath())) {
                return rtrim($this->getBaseHost(), "/") . "/" . ltrim(str_replace("\\", "/", $this->getRelativePath()), "/");
            }
        }

        return null;
    }

    public function getHash(): string {
        return md5($this->getPathName());
    }

    public function jsonSerialize(): array {
        return [
            "type"                 => "file",
            "name"                 => $this->getName(),
            "nameWithoutExtension" => $this->getNameWithoutExtension(),
            "extension"            => $this->getExtension(),
            "relativePath"         => $this->getRelativePath(),
            "owner"                => $this->getOwner(),
            "permissions"          => $this->getPermissions(),
            "accessTime"           => $this->getAccessTime(),
            "modificationTime"     => $this->getModificationTime(),
            "changeTime"           => $this->getChangeTime(),
            "isWritable"           => $this->isWritable(),
            "isReadable"           => $this->isReadable(),
            "size"                 => $this->getSize(),
            "humanSize"            => $this->getHumanSize(),
            "mimeType"             => $this->getMimeType(),
            "hash"                 => $this->getHash(),
            "url"                  => $this->getUrl()
        ];
    }

    public static function parse(\SplFileInfo $fileInfo, ?string $baseDirectory = null, ?string $baseHost = null): File {
        $currentFile = new File();
        $currentFile->setName($fileInfo->getFilename());
        $currentFile->setPath($fileInfo->getPath());
        $currentFile->setBasePath($baseDirectory);
        $currentFile->setBaseHost($baseHost);
        $currentFile->setPathName($fileInfo->getPathname());
        $currentFile->setExtension($fileInfo->getExtension());
        $currentFile->setOwner($fileInfo->getOwner());
        $currentFile->setPermissions($fileInfo->getPerms());
        $currentFile->setAccessTime($fileInfo->getATime());
        $currentFile->setModificationTime($fileInfo->getMTime());
        $currentFile->setChangeTime($fileInfo->getCTime());
        $currentFile->setWritable($fileInfo->isWritable());
        $currentFile->setReadable($fileInfo->isReadable());
        $currentFile->setSize($fileInfo->getSize());
        $currentFile->setMimeType(
            mime_content_type($fileInfo->getRealPath())
        );

        return $currentFile;
    }
}
