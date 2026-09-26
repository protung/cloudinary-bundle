<?php

declare(strict_types=1);

namespace Speicher210\CloudinaryBundle\Tests\DependencyInjection;

use Cloudinary\Configuration\Configuration;
use Override;
use PHPUnit\Framework\TestCase;
use Speicher210\CloudinaryBundle\Cloudinary\Admin;
use Speicher210\CloudinaryBundle\Cloudinary\Cloudinary;
use Speicher210\CloudinaryBundle\Cloudinary\Uploader;
use Speicher210\CloudinaryBundle\DependencyInjection\Speicher210CloudinaryExtension;
use Speicher210\CloudinaryBundle\Twig\Extension\CloudinaryExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

abstract class AbstractSpeicher210CloudinaryExtensionTestCase extends TestCase
{
    private ContainerBuilder $container;

    #[Override]
    protected function setUp(): void
    {
        $this->container = new ContainerBuilder();
        $this->container->registerExtension($extension = new Speicher210CloudinaryExtension());
        $this->container->loadFromExtension($extension->getAlias());
    }

    /**
     * Loads a configuration.
     *
     * @param ContainerBuilder $container     The container.
     * @param string           $configuration The configuration.
     */
    abstract protected function loadConfiguration(ContainerBuilder $container, string $configuration): void;

    public function testURLConfigurationOverwritesParameters(): void
    {
        $this->loadConfiguration($this->container, 'with_url');
        $this->container->compile();

        // Need to trigger a load.
        $service = $this->container->get('speicher210_cloudinary.cloudinary');

        self::assertInstanceOf(Cloudinary::class, $service);

        $this->assertDefaultConfig($service->configuration);
    }

    public function testCloudinaryService(): void
    {
        $this->loadConfiguration($this->container, 'default');
        $this->container->compile();

        $cloudinary = $this->container->get('speicher210_cloudinary.cloudinary');

        self::assertInstanceOf(Cloudinary::class, $cloudinary);

        $this->assertDefaultConfig($cloudinary->configuration);
        self::assertStringNotContainsString('_a=', (string) $cloudinary->image('sample')->toUrl());
    }

    public function testUploaderService(): void
    {
        $this->loadConfiguration($this->container, 'default');
        $this->container->compile();

        $uploader = $this->container->get('speicher210_cloudinary.uploader');

        self::assertInstanceOf(Uploader::class, $uploader);

        $this->assertDefaultConfig($uploader->configuration);
    }

    public function testAdminService(): void
    {
        $this->loadConfiguration($this->container, 'default');
        $this->container->compile();

        $admin = $this->container->get('speicher210_cloudinary.admin');

        self::assertInstanceOf(Admin::class, $admin);

        $this->assertDefaultConfig($admin->configuration);
    }

    public function testTwigExtensionService(): void
    {
        $this->loadConfiguration($this->container, 'default');
        $this->container->compile();

        $service = 'twig.extension.cloudinary';

        self::assertInstanceOf(CloudinaryExtension::class, $this->container->get($service));

        self::assertTrue($this->container->getDefinition($service)->hasTag('twig.extension'));
    }

    public function testAnalyticsCanBeTurnedOn(): void
    {
        $this->loadConfiguration($this->container, 'with_analytics');
        $this->container->compile();

        $cloudinary = $this->container->get('speicher210_cloudinary.cloudinary');

        self::assertInstanceOf(Cloudinary::class, $cloudinary);
        self::assertStringContainsString('?_a=', (string) $cloudinary->image('sample')->toUrl());
    }

    /**
     * Asserts that the default configuration has been populated.
     */
    private function assertDefaultConfig(Configuration $configuration): void
    {
        self::assertTrue($configuration->url->secure);
        self::assertSame('name', $configuration->cloud->cloudName);
        self::assertSame('key', $configuration->cloud->apiKey);
        self::assertSame('secret', $configuration->cloud->apiSecret);
    }
}
