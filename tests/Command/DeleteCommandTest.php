<?php

declare(strict_types=1);

namespace Speicher210\CloudinaryBundle\Tests\Command;

use Cloudinary\Api\ApiResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Speicher210\CloudinaryBundle\Cloudinary\Admin;
use Speicher210\CloudinaryBundle\Command\DeleteCommand;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(DeleteCommand::class)]
final class DeleteCommandTest extends TestCase
{
    use DisplaySnapshots;

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
