<?php

declare(strict_types=1);

namespace Speicher210\CloudinaryBundle\Tests\Command;

use Cloudinary\Api\ApiResponse;
use Cloudinary\Api\Exception\BadRequest;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psl\Type;
use Speicher210\CloudinaryBundle\Cloudinary\Uploader;
use Speicher210\CloudinaryBundle\Command\UploadCommand;
use Symfony\Component\Console\Tester\CommandTester;

use function mkdir;
use function realpath;
use function rmdir;
use function sort;
use function sys_get_temp_dir;
use function touch;
use function uniqid;
use function unlink;

/**
 * The table shows absolute paths, so its column widths depend on where the tests run: the display is not compared with
 * a snapshot, only checked for the values it has to show.
 */
#[CoversClass(UploadCommand::class)]
final class UploadCommandTest extends TestCase
{
    private const array FILE_NAMES = ['document.pdf', 'logo.png', 'photo.jpg'];

    private string $directory;

    #[Override]
    protected function setUp(): void
    {
        $directory = sys_get_temp_dir() . '/' . uniqid('cloudinary-bundle-upload-', true);
        mkdir($directory);
        foreach (self::FILE_NAMES as $fileName) {
            touch($directory . '/' . $fileName);
        }

        // The command shows real paths, and the temporary directory may be behind a symbolic link.
        $this->directory = Type\string()->assert(realpath($directory));
    }

    #[Override]
    protected function tearDown(): void
    {
        foreach (self::FILE_NAMES as $fileName) {
            unlink($this->directory . '/' . $fileName);
        }

        rmdir($this->directory);
    }

    public function testUploadsEveryFileWithThePrefixedFileNameAsPublicId(): void
    {
        $uploads  = [];
        $uploader = $this->createMock(Uploader::class);
        $uploader
            ->expects($this->exactly(3))
            ->method('upload')
            ->willReturnCallback(
                static function (mixed $file, array $options) use (&$uploads): ApiResponse {
                    $uploads[] = [$file, $options];

                    return new ApiResponse(['public_id' => $options['public_id']], []);
                },
            );

        $commandTester = new CommandTester(new UploadCommand($uploader));
        $this->execute($commandTester, ['--prefix' => 'uploads/']);

        $commandTester->assertCommandIsSuccessful();

        sort($uploads);
        self::assertSame(
            [
                [$this->directory . '/document.pdf', ['public_id' => 'uploads/document']],
                [$this->directory . '/logo.png', ['public_id' => 'uploads/logo']],
                [$this->directory . '/photo.jpg', ['public_id' => 'uploads/photo']],
            ],
            $uploads,
        );

        $display = $commandTester->getDisplay();
        self::assertStringContainsString('uploads/document', $display);
        self::assertStringContainsString('uploads/logo', $display);
        self::assertStringContainsString('uploads/photo', $display);
    }

    public function testUploadsOnlyTheFilesMatchingTheFilter(): void
    {
        $uploader = $this->createMock(Uploader::class);
        $uploader
            ->expects($this->once())
            ->method('upload')
            ->with($this->directory . '/photo.jpg', ['public_id' => 'uploads/photo'])
            ->willReturn(new ApiResponse(['public_id' => 'uploads/photo'], []));

        $commandTester = new CommandTester(new UploadCommand($uploader));
        $this->execute($commandTester, ['--prefix' => 'uploads/', '--filter' => '*.jpg']);

        $commandTester->assertCommandIsSuccessful();
        self::assertStringContainsString('uploads/photo', $commandTester->getDisplay());
    }

    public function testShowsTheErrorOfAFailedUpload(): void
    {
        $uploader = $this->createMock(Uploader::class);
        $uploader
            ->expects($this->once())
            ->method('upload')
            ->willThrowException(new BadRequest('Invalid image file'));

        $commandTester = new CommandTester(new UploadCommand($uploader));
        $this->execute($commandTester, ['--prefix' => 'uploads/', '--filter' => '*.png']);

        $commandTester->assertCommandIsSuccessful();

        $display = $commandTester->getDisplay();
        self::assertStringContainsString($this->directory . '/logo.png', $display);
        self::assertStringContainsString('Invalid image file', $display);
    }

    /**
     * The command appends table rows, which needs a console output with sections.
     *
     * @param array<string, string> $options
     */
    private function execute(CommandTester $commandTester, array $options): void
    {
        $commandTester->execute(
            ['directory' => $this->directory, ...$options],
            ['capture_stderr_separately' => true],
        );
    }
}
