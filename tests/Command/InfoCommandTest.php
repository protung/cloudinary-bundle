<?php

declare(strict_types=1);

namespace Speicher210\CloudinaryBundle\Tests\Command;

use Cloudinary\Api\ApiResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Speicher210\CloudinaryBundle\Cloudinary\Admin;
use Speicher210\CloudinaryBundle\Command\InfoCommand;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(InfoCommand::class)]
final class InfoCommandTest extends TestCase
{
    use DisplaySnapshots;

    public function testShowsTheScalarProperties(): void
    {
        $commandTester = new CommandTester(new InfoCommand($this->adminReturningTheAsset()));
        $commandTester->execute(['public_id' => 'folder/image']);

        $commandTester->assertCommandIsSuccessful();
        self::assertDisplayMatchesSnapshot('info', $commandTester->getDisplay());
    }

    public function testShowsTheDerivedResourcesWhenVerbose(): void
    {
        $commandTester = new CommandTester(new InfoCommand($this->adminReturningTheAsset()));
        $commandTester->execute(['public_id' => 'folder/image'], ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

        $commandTester->assertCommandIsSuccessful();
        self::assertDisplayMatchesSnapshot('info-verbose', $commandTester->getDisplay());
    }

    private function adminReturningTheAsset(): Admin
    {
        $admin = $this->createMock(Admin::class);
        $admin
            ->expects($this->once())
            ->method('asset')
            ->with('folder/image')
            ->willReturn(
                new ApiResponse(
                    [
                        'public_id' => 'folder/image',
                        'format' => 'jpg',
                        'bytes' => 2048,
                        'tags' => ['nature'],
                        'derived' => [
                            [
                                'id' => 'd1',
                                'format' => 'webp',
                                'bytes' => 512,
                                'transformation' => 'w_100',
                                'url' => 'https://res.cloudinary.com/demo/image/upload/w_100/folder/image.webp',
                            ],
                            [
                                'id' => 'd2',
                                'format' => 'avif',
                                'bytes' => 2048,
                                'transformation' => 'w_200',
                                'url' => 'https://res.cloudinary.com/demo/image/upload/w_200/folder/image.avif',
                            ],
                            [
                                'id' => 'd3',
                                'format' => 'png',
                                'bytes' => 1_536_000,
                                'transformation' => 'w_2000',
                                'url' => 'https://res.cloudinary.com/demo/image/upload/w_2000/folder/image.png',
                            ],
                            [
                                'id' => 'd4',
                                'format' => 'gif',
                                'bytes' => 1024,
                                'transformation' => 'w_10',
                                'url' => 'https://res.cloudinary.com/demo/image/upload/w_10/folder/image.gif',
                            ],
                            [
                                'id' => 'd5',
                                'format' => 'jpg',
                                'bytes' => 1_048_575,
                                'transformation' => 'w_1500',
                                'url' => 'https://res.cloudinary.com/demo/image/upload/w_1500/folder/image.jpg',
                            ],
                        ],
                    ],
                    [],
                ),
            );

        return $admin;
    }
}
