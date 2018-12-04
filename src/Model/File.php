<?php

namespace Adamski\Symfony\DirectoryBundle\Model;

use JsonSerializable;
use SplFileInfo;

class File implements JsonSerializable {

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
     * @var string
     */
    protected $extension;

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
    protected $size;

    /**
     * @var string|null
     */
    protected $mimeType;

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
     * @return string
     */
    public function getExtension() {
        return $this->extension;
    }

    /**
     * @param string $extension
     */
    public function setExtension(string $extension) {
        $this->extension = $extension;
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
    public function getSize() {
        return $this->size;
    }

    /**
     * @param int $size
     */
    public function setSize(int $size) {
        $this->size = $size;
    }

    /**
     * @return string|null
     */
    public function getMimeType() {
        return $this->mimeType;
    }

    /**
     * @param string|null $mimeType
     */
    public function setMimeType(?string $mimeType) {
        $this->mimeType = $mimeType;
    }

    /**
     * @return string|null
     */
    public function getNameWithoutExtension() {
        if (null !== ($currentExtension = $this->getExtension())) {
            if ("" !== $currentExtension) {
                return str_replace("." . $currentExtension, "", $this->getName());
            }
        }

        return null;
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
            "type"                 => "file",
            "name"                 => $this->getName(),
            "nameWithoutExtension" => $this->getNameWithoutExtension(),
            "extension"            => $this->getExtension(),
            "owner"                => $this->getOwner(),
            "permissions"          => $this->getPermissions(),
            "accessTime"           => $this->getAccessTime(),
            "modificationTime"     => $this->getModificationTime(),
            "changeTime"           => $this->getChangeTime(),
            "isWritable"           => $this->isWritable(),
            "isReadable"           => $this->isReadable(),
            "size"                 => $this->getSize(),
            "mimeType"             => $this->getMimeType(),
            "hash"                 => $this->getHash()
        ];
    }

    /**
     * @param SplFileInfo $fileInfo
     * @return File
     */
    public static function parse(SplFileInfo $fileInfo) {
        $currentFile = new File();
        $currentFile->setName($fileInfo->getFilename());
        $currentFile->setPath($fileInfo->getPath());
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
