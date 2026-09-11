<?php

use PHPUnit\Framework\TestCase;

final class GalleryTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/gallery-' . bin2hex(random_bytes(6));
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/*') ?: [] as $file) {
            unlink($file);
        }
        rmdir($this->dir);
    }

    private function touchFiles(string ...$names): void
    {
        foreach ($names as $name) {
            file_put_contents($this->dir . '/' . $name, 'x');
        }
    }

    public function testMissingFolderGivesNoPictures(): void
    {
        $this->assertSame([], gallery_images($this->dir . '/nope', 'img/nope'));
    }

    public function testOnlyImagesAreListed(): void
    {
        $this->touchFiles('a.jpg', 'notes.txt', 'b.PNG', 'thumbs.db');

        $this->assertSame(['a.jpg', 'b.PNG'], array_column(gallery_images($this->dir, 'img/art'), 'file'));
    }

    public function testPicturesAreSortedByNameTheWayPeopleCount(): void
    {
        $this->touchFiles('10.jpg', '2.jpg', '1.jpg');

        $this->assertSame(['1.jpg', '2.jpg', '10.jpg'], array_column(gallery_images($this->dir, 'img/art'), 'file'));
    }

    public function testSpacesAndBracketsSurviveInTheUrl(): void
    {
        $this->touchFiles('1 (1).jpg');

        $this->assertSame('img/art/1%20%281%29.jpg', gallery_images($this->dir, 'img/art')[0]['src']);
    }

    public function testEachPictureIsNumberedForItsCaption(): void
    {
        $this->touchFiles('b.jpg', 'a.jpg');

        $this->assertSame([1, 2], array_column(gallery_images($this->dir, 'img/art'), 'number'));
    }
}
