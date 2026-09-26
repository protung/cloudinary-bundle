<?php

declare(strict_types=1);

namespace Speicher210\CloudinaryBundle\Factory;

use Cloudinary\Configuration\Configuration;
use Speicher210\CloudinaryBundle\Cloudinary\Cloudinary;

use function parse_url;

final readonly class CloudinaryFactory
{
    private Configuration $configuration;

    /**
     * @param array{url?: string, cloud_name?: string, access_identifier?: array{api_key: string, api_secret: string}, secure?: bool, analytics?: bool} $config
     */
    public function __construct(array $config)
    {
        $cloudName = $config['cloud_name'] ?? null;
        $apiKey    = $config['access_identifier']['api_key'] ?? null;
        $apiSecret = $config['access_identifier']['api_secret'] ?? null;

        // Any value in the URL takes precedence over the one set explicitly.
        $url = $config['url'] ?? null;
        if ($url !== null) {
            $urlParts = parse_url($url);

            if ($urlParts === false || ($urlParts['scheme'] ?? null) === null) {
                throw new InvalidCloudinaryUrlException();
            }

            $cloudName = $urlParts['host'] ?? $cloudName;
            $apiKey    = $urlParts['user'] ?? $apiKey;
            $apiSecret = $urlParts['pass'] ?? $apiSecret;
        }

        if ($cloudName === null || $apiKey === null || $apiSecret === null) {
            throw new InvalidCloudinaryUrlException();
        }

        $this->configuration = Configuration::fromParams(
            [
                'cloud' => [
                    'cloud_name' => $cloudName,
                    'api_key' => $apiKey,
                    'api_secret' => $apiSecret,
                ],
                'url' => [
                    'secure' => $config['secure'] ?? true,
                    'analytics' => $config['analytics'] ?? false,
                ],
            ],
        );
    }

    public function createCloudinary(): Cloudinary
    {
        return new Cloudinary($this->configuration);
    }
}
