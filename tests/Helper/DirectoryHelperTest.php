<?php

namespace Adamski\Symfony\DirectoryBundleTests\Helper;

use Adamski\Symfony\DirectoryBundle\Helper\DirectoryHelper;
use Adamski\Symfony\DirectoryBundle\Model\Directory;
use Adamski\Symfony\DirectoryBundle\Model\File;
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
            $this->joinPath("structure"),
            $this->joinPath("structure"),
            $this->joinPath("structure", "var", "cache"),
            $this->joinPath("structure", "var", "logs")
        );

        // Prepare structure
        $pathOne = $this->joinPath("structure");
        $pathTwo = $this->joinPath("structure", "path", "two");

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
            $this->joinPath("structure")
        );
    }

    /**
     * Test of joinPath method.
     */
    public function testJoinPath() {
        $pathOne = $this->directoryHelper->joinPath("one", "two", "three");

        $this->assertEquals("one" . DIRECTORY_SEPARATOR . "two" . DIRECTORY_SEPARATOR . "three", $pathOne);
    }

    /**
     * Test of getRecursiveList method.
     */
    public function testGetRecursiveList() {
        $itemsCollection = $this->directoryHelper->getRecursiveList(
            $this->joinPath("structure")
        );

        $expectedCollection = [
            $this->joinPath("structure", "one.txt"),
            $this->joinPath("structure", "path"),
            $this->joinPath("structure", "path", "two"),
            $this->joinPath("structure", "path", "two", "two.txt")
        ];

        $this->assertEquals($expectedCollection, $this->mapParameter($itemsCollection));
    }

    /**
     * Test of getRecursiveList method.
     */
    public function testGetRecursiveListFilesOnly() {
        $itemsCollection = $this->directoryHelper->getRecursiveList(
            $this->joinPath("structure"), DirectoryHelper::RECURSIVE_FILES_ONLY
        );

        $expectedCollection = [
            $this->joinPath("structure", "one.txt"),
            $this->joinPath("structure", "path", "two", "two.txt")
        ];

        $this->assertEquals($expectedCollection, $this->mapParameter($itemsCollection));
    }

    /**
     * Test of getRecursiveList method.
     */
    public function testGetRecursiveListDirectoriesOnly() {
        $itemsCollection = $this->directoryHelper->getRecursiveList(
            $this->joinPath("structure"), DirectoryHelper::RECURSIVE_DIRECTORIES_ONLY
        );

        $expectedCollection = [
            $this->joinPath("structure", "path"),
            $this->joinPath("structure", "path", "two")
        ];

        $this->assertEquals($expectedCollection, $this->mapParameter($itemsCollection));
    }

    /**
     * Test of getDirectories method.
     */
    public function testGetDirectories() {
        $itemsCollection = $this->directoryHelper->getDirectories(
            $this->joinPath("structure")
        );

        $expectedCollection = [
            $this->joinPath("structure", "path")
        ];

        $this->assertEquals($expectedCollection, $this->mapParameter($itemsCollection));
    }

    /**
     * Test of getFiles method.
     */
    public function testGetFiles() {
        $itemsCollection = $this->directoryHelper->getFiles(
            $this->joinPath("structure")
        );

        $expectedCollection = [
            $this->joinPath("structure", "one.txt")
        ];

        $this->assertEquals($expectedCollection, $this->mapParameter($itemsCollection));
    }

    /**
     * Test of parseFromPath method.
     */
    public function testParseFromPath() {
        $currentFile = $this->directoryHelper->parseFromPath(
            $this->joinPath("structure", "one.txt")
        );

        $currentDirectory = $this->directoryHelper->parseFromPath(
            $this->joinPath("structure")
        );

        $this->assertInstanceOf(File::class, $currentFile);
        $this->assertInstanceOf(Directory::class, $currentDirectory);

        $this->assertEquals(
            $this->joinPath("structure", "one.txt"),
            $currentFile->getPathName()
        );

        $this->assertEquals(
            $this->joinPath("structure"),
            $currentDirectory->getPathName()
        );
    }

    /**
     * Test of writeFile method.
     */
    public function testWriteFile() {
        $this->directoryHelper->writeFile(
            $this->joinPath("structure", "one.txt"),
            "TEST ABC"
        );

        $this->assertEquals(
            "TEST ABC",
            file_get_contents(
                $this->joinPath("structure", "one.txt")
            )
        );
    }

    /**
     * Test of removeFile method.
     */
    public function testRemoveFile() {
        $this->directoryHelper->removeFile(
            $this->joinPath("structure", "one.txt")
        );

        $itemsCollection = $this->directoryHelper->getFiles(
            $this->joinPath("structure")
        );

        $this->assertEmpty($itemsCollection);
    }

    /**
     * Test of createDirectory method.
     */
    public function testCreateDirectory() {
        $this->directoryHelper->createDirectory(
            $this->joinPath("structure", "test")
        );

        $itemsCollection = $this->directoryHelper->getDirectories(
            $this->joinPath("structure")
        );

        $expectedCollection = [
            $this->joinPath("structure", "path"),
            $this->joinPath("structure", "test")
        ];

        $this->assertEquals($expectedCollection, $this->mapParameter($itemsCollection));
    }

    /**
     * Test of removeDirectory method.
     */
    public function testRemoveDirectory() {
        $this->directoryHelper->removeDirectory(
            $this->joinPath("structure", "path"), true
        );

        $itemsCollection = $this->directoryHelper->getDirectories(
            $this->joinPath("structure")
        );

        $this->assertEmpty($itemsCollection);
    }

    /**
     * Test of removeDirectory method.
     */
    public function testRemoveDirectoryAssertFalse() {
        $response = $this->directoryHelper->removeDirectory(
            $this->joinPath("structure", "path")
        );

        $this->assertFalse($response);
    }

    /**
     * Test of copyFile method.
     */
    public function testCopyFile() {
        $this->directoryHelper->copyFile(
            $this->joinPath("structure", "one.txt"),
            $this->joinPath("structure", "path", "two")
        );

        $itemsCollection = $this->directoryHelper->getFiles(
            $this->joinPath("structure", "path", "two")
        );

        $expectedCollection = [
            $this->joinPath("structure", "path", "two", "one.txt"),
            $this->joinPath("structure", "path", "two", "two.txt")
        ];

        $this->assertEquals($expectedCollection, $this->mapParameter($itemsCollection));
    }

    /**
     * Test of renameFile method.
     */
    public function testRenameFile() {
        $this->directoryHelper->renameFile(
            $this->joinPath("structure", "one.txt"),
            "test"
        );

        $itemsCollection = $this->directoryHelper->getFiles(
            $this->joinPath("structure")
        );

        $expectedCollection = [
            $this->joinPath("structure", "test.txt")
        ];

        $this->assertEquals($expectedCollection, $this->mapParameter($itemsCollection));
    }

    /**
     * Test of moveFile method.
     */
    public function testMoveFile() {
        $this->directoryHelper->moveFile(
            $this->joinPath("structure", "one.txt"),
            $this->joinPath("structure", "path", "two")
        );

        $itemsCollection = $this->directoryHelper->getFiles(
            $this->joinPath("structure")
        );

        $this->assertEmpty($itemsCollection);

        $itemsCollection = $this->directoryHelper->getFiles(
            $this->joinPath("structure", "path", "two")
        );

        $expectedCollection = [
            $this->joinPath("structure", "path", "two", "one.txt"),
            $this->joinPath("structure", "path", "two", "two.txt")
        ];

        $this->assertEquals($expectedCollection, $this->mapParameter($itemsCollection));
    }

    /**
     * Test of renameDirectory method.
     */
    public function testRenameDirectory() {
        $this->directoryHelper->renameDirectory(
            $this->joinPath("structure", "path", "two"),
            "three"
        );

        $itemsCollection = $this->directoryHelper->getDirectories(
            $this->joinPath("structure", "path")
        );

        $expectedCollection = [
            $this->joinPath("structure", "path", "three")
        ];

        $this->assertEquals($expectedCollection, $this->mapParameter($itemsCollection));
    }

    /**
     * Test of findFilesByRegex method.
     */
    public function testFindFilesByRegex() {
        $itemsCollection = $this->directoryHelper->findFilesByRegex(
            $this->joinPath("structure"),
            "/^one/",
            "name"
        );

        $expectedCollection = [
            $this->joinPath("structure", "one.txt")
        ];

        $this->assertEquals($expectedCollection, $this->mapParameter($itemsCollection));
    }

    /**
     * Test of findOneFileByPattern method.
     */
    public function testFindOneFileByPattern() {
        $resultItem = $this->directoryHelper->findOneFileByPattern(
            $this->joinPath("structure"),
            "/^one/",
            "name"
        );

        $this->assertInstanceOf(File::class, $resultItem);
        $this->assertEquals($this->joinPath("structure", "one.txt"), $resultItem->getPathName());
    }

    /**
     * Test of findDirectoriesByPattern method.
     */
    public function testFindDirectoriesByPattern() {
        $itemsCollection = $this->directoryHelper->findDirectoriesByPattern(
            $this->joinPath("structure"),
            "/^two/",
            "name"
        );

        $expectedCollection = [
            $this->joinPath("structure", "path", "two")
        ];

        $this->assertEquals($expectedCollection, $this->mapParameter($itemsCollection));
    }

    /**
     * Test of findOneDirectoryByPattern method.
     */
    public function testFindOneDirectoryByPattern() {
        $resultItem = $this->directoryHelper->findOneDirectoryByPattern(
            $this->joinPath("structure"),
            "/^two/",
            "name"
        );

        $this->assertInstanceOf(Directory::class, $resultItem);
        $this->assertEquals($this->joinPath("structure", "path", "two"), $resultItem->getPathName());
    }

    /**
     * @param string ...$args
     * @return string
     */
    private function joinPath(string ...$args) {
        return implode(DIRECTORY_SEPARATOR, array_merge([realpath(__DIR__ . "/../../")], $args));
    }

    /**
     * @param array  $collection
     * @param string $getter
     * @return array
     */
    private function mapParameter(array $collection, string $getter = "getPathName") {
        return array_map(function ($item) use ($getter) {
            return $item->{$getter}();
        }, $collection);
    }
}
