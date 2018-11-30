<?php

namespace Adamski\Symfony\DirectoryBundleTests\Helper;

use Adamski\Symfony\DirectoryBundle\Helper\DirectoryHelper;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

class DirectoryHelperTest extends TestCase {

    /**
     * @var DirectoryHelper
     */
    protected $directoryHelper;

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        $this->directoryHelper = new DirectoryHelper(
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure",
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure",
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure" . DIRECTORY_SEPARATOR . "var" . DIRECTORY_SEPARATOR . "cache",
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure" . DIRECTORY_SEPARATOR . "var" . DIRECTORY_SEPARATOR . "logs"
        );

        // Prepare structure
        $pathOne = realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure";
        $pathTwo = realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure" . DIRECTORY_SEPARATOR . "path" . DIRECTORY_SEPARATOR . "two";

        mkdir($pathOne, 0775, true);
        mkdir($pathTwo, 0775, true);

        // Write sample files
        file_put_contents($pathOne . DIRECTORY_SEPARATOR . "one.txt", "one");
        file_put_contents($pathTwo . DIRECTORY_SEPARATOR . "two.txt", "two");
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown() {
        $fileSystem = new Filesystem();
        $fileSystem->remove(
            realpath(__DIR__ . "/../../") . "/structure"
        );
    }

    /**
     * Test of joinPath method.
     */
    public function testJoinPath() {
        $pathOne = $this->directoryHelper->joinPath("one", "two", "three");

        $this->assertEquals("one\\two\\three", $pathOne);
    }

    /**
     * Test of getRecursiveList method.
     */
    public function testGetRecursiveList() {
        $itemsCollection = $this->directoryHelper->getRecursiveList(
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure"
        );

        $expectedCollection = [
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure" . DIRECTORY_SEPARATOR . "one.txt",
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure" . DIRECTORY_SEPARATOR . "path",
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure" . DIRECTORY_SEPARATOR . "path" . DIRECTORY_SEPARATOR . "two",
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure" . DIRECTORY_SEPARATOR . "path" . DIRECTORY_SEPARATOR . "two" . DIRECTORY_SEPARATOR . "two.txt"
        ];

        $this->assertEquals($expectedCollection, array_map(function ($item) {
            return $item->getPathName();
        }, $itemsCollection));
    }

    /**
     * Test of getRecursiveList method.
     */
    public function testGetRecursiveListFilesOnly() {
        $itemsCollection = $this->directoryHelper->getRecursiveList(
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure", DirectoryHelper::RECURSIVE_FILES_ONLY
        );

        $expectedCollection = [
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure" . DIRECTORY_SEPARATOR . "one.txt",
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure" . DIRECTORY_SEPARATOR . "path" . DIRECTORY_SEPARATOR . "two" . DIRECTORY_SEPARATOR . "two.txt"
        ];

        $this->assertEquals($expectedCollection, array_map(function ($item) {
            return $item->getPathName();
        }, $itemsCollection));
    }

    /**
     * Test of getRecursiveList method.
     */
    public function testGetRecursiveListDirectoriesOnly() {
        $itemsCollection = $this->directoryHelper->getRecursiveList(
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure", DirectoryHelper::RECURSIVE_DIRECTORIES_ONLY
        );

        $expectedCollection = [
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure" . DIRECTORY_SEPARATOR . "path",
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure" . DIRECTORY_SEPARATOR . "path" . DIRECTORY_SEPARATOR . "two"
        ];

        $this->assertEquals($expectedCollection, array_map(function ($item) {
            return $item->getPathName();
        }, $itemsCollection));
    }

    /**
     * Test of getDirectories method.
     */
    public function testGetDirectories() {
        $itemsCollection = $this->directoryHelper->getDirectories(
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure"
        );

        $expectedCollection = [
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure" . DIRECTORY_SEPARATOR . "path"
        ];

        $this->assertEquals($expectedCollection, array_map(function ($item) {
            return $item->getPathName();
        }, $itemsCollection));
    }

    /**
     * Test of getFiles method.
     */
    public function testGetFiles() {
        $itemsCollection = $this->directoryHelper->getFiles(
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure"
        );

        $expectedCollection = [
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure" . DIRECTORY_SEPARATOR . "one.txt"
        ];

        $this->assertEquals($expectedCollection, array_map(function ($item) {
            return $item->getPathName();
        }, $itemsCollection));
    }

    /**
     * Test of parseFromPath method.
     */
    public function testParseFromPath() {
        $currentFile = $this->directoryHelper->parseFromPath(
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure" . DIRECTORY_SEPARATOR . "one.txt"
        );

        $this->assertEquals(
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure" . DIRECTORY_SEPARATOR . "one.txt",
            $currentFile->getPathName()
        );
    }

    /**
     * Test of writeFile method.
     */
    public function testWriteFile() {
        $this->directoryHelper->writeFile(
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure" . DIRECTORY_SEPARATOR . "one.txt",
            "TEST ABC"
        );

        $this->assertEquals(
            "TEST ABC", file_get_contents(
                realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure" . DIRECTORY_SEPARATOR . "one.txt"
            )
        );
    }

    /**
     * Test of removeFile method.
     */
    public function testRemoveFile() {
        $this->directoryHelper->removeFile(
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure" . DIRECTORY_SEPARATOR . "one.txt"
        );

        $itemsCollection = $this->directoryHelper->getFiles(
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure"
        );

        $this->assertEquals([], array_map(function ($item) {
            return $item->getPathName();
        }, $itemsCollection));
    }

    /**
     * Test of createDirectory method.
     */
    public function testCreateDirectory() {
        $this->directoryHelper->createDirectory(
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure" . DIRECTORY_SEPARATOR . "testabc"
        );

        $itemsCollection = $this->directoryHelper->getDirectories(
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure"
        );

        $expectedCollection = [
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure" . DIRECTORY_SEPARATOR . "path",
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure" . DIRECTORY_SEPARATOR . "testabc"
        ];

        $this->assertEquals($expectedCollection, array_map(function ($item) {
            return $item->getPathName();
        }, $itemsCollection));
    }

    /**
     * Test of removeDirectory method.
     */
    public function testRemoveDirectory() {
        $this->directoryHelper->removeDirectory(
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure" . DIRECTORY_SEPARATOR . "path", true
        );

        $itemsCollection = $this->directoryHelper->getDirectories(
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure"
        );

        $this->assertEquals([], array_map(function ($item) {
            return $item->getPathName();
        }, $itemsCollection));
    }

    /**
     * Test of removeDirectory method.
     */
    public function testRemoveDirectoryAssertFalse() {
        $response = $this->directoryHelper->removeDirectory(
            realpath(__DIR__ . "/../../") . DIRECTORY_SEPARATOR . "structure" . DIRECTORY_SEPARATOR . "path"
        );

        $this->assertFalse($response);
    }
}
