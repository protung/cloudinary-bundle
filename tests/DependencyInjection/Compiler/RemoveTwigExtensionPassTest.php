<?php

declare(strict_types=1);

namespace Speicher210\CloudinaryBundle\Tests\DependencyInjection\Compiler;

use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Speicher210\CloudinaryBundle\DependencyInjection\Compiler\RemoveTwigExtensionPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class RemoveTwigExtensionPassTest extends TestCase
{
    private ContainerBuilder&MockObject $container;

    private RemoveTwigExtensionPass $compilerPass;

    #[Override]
    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerBuilder::class);

        $this->compilerPass = new RemoveTwigExtensionPass();
    }

    public function testProcessWithTwig(): void
    {
        $this->container
            ->expects($this->once())
            ->method('hasDefinition')
            ->with('twig')
            ->willReturn(true);

        $this->container
            ->expects($this->never())
            ->method('removeDefinition')
            ->with('twig.extension.cloudinary');

        $this->compilerPass->process($this->container);
    }

    public function testProcessWithoutTwig(): void
    {
        $this->container
            ->expects($this->once())
            ->method('hasDefinition')
            ->with('twig')
            ->willReturn(false);

        $this->container
            ->expects($this->once())
            ->method('removeDefinition')
            ->with('twig.extension.cloudinary');

        $this->compilerPass->process($this->container);
    }
}
