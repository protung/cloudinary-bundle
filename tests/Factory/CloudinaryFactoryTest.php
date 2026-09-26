<?php

declare(strict_types=1);

namespace Speicher210\CloudinaryBundle\Tests\Factory;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Speicher210\CloudinaryBundle\Factory\CloudinaryFactory;
use Speicher210\CloudinaryBundle\Factory\InvalidCloudinaryUrlException;

#[CoversClass(CloudinaryFactory::class)]
#[CoversClass(InvalidCloudinaryUrlException::class)]
final class CloudinaryFactoryTest extends TestCase
{
    public function testReadsTheCloudAndCredentialsFromTheUrl(): void
    {
        $configuration = (new CloudinaryFactory(['url' => 'cloudinary://my-key:my-secret@my-cloud']))
            ->createCloudinary()
            ->configuration;

        self::assertSame('my-cloud', $configuration->cloud->cloudName);
        self::assertSame('my-key', $configuration->cloud->apiKey);
        self::assertSame('my-secret', $configuration->cloud->apiSecret);
        self::assertTrue($configuration->url->secure);
    }

    public function testSecureUrlsCanBeTurnedOff(): void
    {
        $configuration = (new CloudinaryFactory(['url' => 'cloudinary://my-key:my-secret@my-cloud', 'secure' => false]))
            ->createCloudinary()
            ->configuration;

        self::assertFalse($configuration->url->secure);
    }

    /**
     * @param array{url?: string, cloud_name?: string} $config
     */
    #[DataProvider('incompleteConfigurations')]
    public function testRejectsAnIncompleteConfiguration(array $config): void
    {
        $this->expectException(InvalidCloudinaryUrlException::class);
        $this->expectExceptionMessage('Cloudinary URL must be in the form: cloudinary://api_key:api_secret@cloud_name');

        new CloudinaryFactory($config);
    }

    /**
     * @return iterable<string, array{array{url?: string, cloud_name?: string}}>
     */
    public static function incompleteConfigurations(): iterable
    {
        yield 'URL without a scheme' => [['url' => 'my-cloud']];
        yield 'malformed URL' => [['url' => 'cloudinary:///my-cloud']];
        yield 'URL without the API secret' => [['url' => 'cloudinary://my-key@my-cloud']];
        yield 'cloud name without the API key and secret' => [['cloud_name' => 'my-cloud']];
        yield 'nothing' => [[]];
    }
}
