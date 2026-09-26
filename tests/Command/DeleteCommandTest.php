<?php

declare(strict_types=1);

namespace Speicher210\CloudinaryBundle\Tests\Command;

use Cloudinary\Api\ApiResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Speicher210\CloudinaryBundle\Cloudinary\Admin;
use Speicher210\CloudinaryBundle\Command\DeleteCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(DeleteCommand::class)]
final class DeleteCommandTest extends TestCase
{
    use DisplaySnapshots;

    public function testRequiresAPrefixOrAResource(): void
    {
        $admin = $this->createMock(Admin::class);
        $admin->expects($this->never())->method('deleteAssetsByPrefix');
        $admin->expects($this->never())->method('deleteAssets');

        $commandTester = new CommandTester(new DeleteCommand($admin));
        $commandTester->execute([]);

        self::assertSame(Command::INVALID, $commandTester->getStatusCode());
        self::assertDisplayMatchesSnapshot('delete-without-criteria', $commandTester->getDisplay());
    }

    /**
     * @return iterable<string, array{array<string, string>}>
     */
    public static function emptyCriteria(): iterable
    {
        yield 'empty prefix' => [['--prefix' => '']];
        yield 'empty resource' => [['--resource' => '']];
        yield 'empty prefix with a resource' => [['--prefix' => '', '--resource' => 'folder/image']];
    }

    /**
     * @param array<string, string> $input
     */
    #[DataProvider('emptyCriteria')]
    public function testRejectsAnEmptyPrefixOrResource(array $input): void
    {
        $admin = $this->createMock(Admin::class);
        $admin->expects($this->never())->method('deleteAssetsByPrefix');
        $admin->expects($this->never())->method('deleteAssets');

        $commandTester = new CommandTester(new DeleteCommand($admin));
        $commandTester->execute($input);

        self::assertSame(Command::INVALID, $commandTester->getStatusCode());
        self::assertDisplayMatchesSnapshot('delete-empty-criteria', $commandTester->getDisplay());
    }

    public function testDeletesNothingWithoutConfirmation(): void
    {
        $admin = $this->createMock(Admin::class);
        $admin->expects($this->never())->method('deleteAssetsByPrefix');
        $admin->expects($this->never())->method('deleteAssets');

        $commandTester = new CommandTester(new DeleteCommand($admin));
        $commandTester->setInputs(['no']);
        $commandTester->execute(['--prefix' => 'folder/', '--resource' => 'folder/image']);

        $commandTester->assertCommandIsSuccessful();
        self::assertDisplayMatchesSnapshot('delete-declined', $commandTester->getDisplay());
    }

    public function testDeletesByPrefix(): void
    {
        $admin = $this->createMock(Admin::class);
        $admin
            ->expects($this->once())
            ->method('deleteAssetsByPrefix')
            ->with('folder/')
            ->willReturn(new ApiResponse(['deleted' => ['folder/image' => 'deleted', 'folder/video' => 'not_found']], []));
        $admin->expects($this->never())->method('deleteAssets');

        $commandTester = new CommandTester(new DeleteCommand($admin));
        $commandTester->setInputs(['yes']);
        $commandTester->execute(['--prefix' => 'folder/']);

        $commandTester->assertCommandIsSuccessful();
        self::assertDisplayMatchesSnapshot('delete-by-prefix', $commandTester->getDisplay());
    }

    public function testDeletesOneResource(): void
    {
        $admin = $this->createMock(Admin::class);
        $admin->expects($this->never())->method('deleteAssetsByPrefix');
        $admin
            ->expects($this->once())
            ->method('deleteAssets')
            ->with('folder/image')
            ->willReturn(new ApiResponse(['deleted' => ['folder/image' => 'deleted']], []));

        $commandTester = new CommandTester(new DeleteCommand($admin));
        $commandTester->setInputs(['yes']);
        $commandTester->execute(['--resource' => 'folder/image']);

        $commandTester->assertCommandIsSuccessful();
        self::assertDisplayMatchesSnapshot('delete-one-resource', $commandTester->getDisplay());
    }
}
