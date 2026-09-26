<?php

declare(strict_types=1);

namespace Speicher210\CloudinaryBundle\Twig\Extension;

use Cloudinary\Tag\PictureTag;
use Cloudinary\Transformation\ImageTransformation;
use Cloudinary\Transformation\VideoTransformation;
use Override;
use Speicher210\CloudinaryBundle\Cloudinary\Cloudinary;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class CloudinaryExtension extends AbstractExtension
{
    private Cloudinary $cloudinary;

    public function __construct(Cloudinary $cloudinary)
    {
        $this->cloudinary = $cloudinary;
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('cloudinary_url', $this->getUrl(...)),
            new TwigFunction('cloudinary_image_tag', $this->getImageTag(...), ['is_safe' => ['html']]),
            new TwigFunction('cloudinary_picture_tag', $this->getPictureTag(...), ['is_safe' => ['html']]),
            new TwigFunction('cloudinary_video_tag', $this->getVideoTag(...), ['is_safe' => ['html']]),
        ];
    }

    /**
     * {@inheritDoc}
     */
    #[Override]
    public function getFilters(): array
    {
        return [
            new TwigFilter('cloudinary_url', $this->getUrl(...)),
            new TwigFilter('cloudinary_image_tag', $this->getImageTag(...), ['is_safe' => ['html']]),
            new TwigFilter('cloudinary_picture_tag', $this->getPictureTag(...), ['is_safe' => ['html']]),
            new TwigFilter('cloudinary_video_tag', $this->getVideoTag(...), ['is_safe' => ['html']]),
        ];
    }

    /**
     * Get the cloudinary URL.
     *
     * @param string       $id      Public ID.
     * @param array<mixed> $options Transformation parameters.
     */
    public function getUrl(string $id, array $options = []): string
    {
        return (string) $this->cloudinary
            ->image($id)
            ->toUrl(ImageTransformation::fromParams($options));
    }

    /**
     * Get the cloudinary image tag.
     *
     * @param string               $id         Public ID.
     * @param array<mixed>         $options    Transformation parameters.
     * @param array<string, mixed> $attributes HTML attributes of the img tag.
     */
    public function getImageTag(string $id, array $options = [], array $attributes = []): string
    {
        return $this->cloudinary
            ->imageTag($id)
            ->addTransformation(ImageTransformation::fromParams($options))
            ->setAttributes($attributes)
            ->toTag();
    }

    /**
     * Get the cloudinary picture tag.
     *
     * @param string               $id         Public ID.
     * @param array<mixed>         $options    Transformation parameters.
     * @param array<string, mixed> $attributes HTML attributes of the img tag inside the picture tag.
     */
    public function getPictureTag(string $id, array $options = [], array $attributes = []): string
    {
        $pictureTag = new PictureTag(
            $this->cloudinary->image($id)->addTransformation(ImageTransformation::fromParams($options)),
            [],
            $this->cloudinary->configuration,
        );
        $pictureTag->imageTag->setAttributes($attributes);

        return $pictureTag->toTag();
    }

    /**
     * Get the cloudinary video tag.
     *
     * @param string               $id         Public ID.
     * @param array<mixed>         $options    Transformation parameters.
     * @param array<string, mixed> $attributes HTML attributes of the video tag.
     */
    public function getVideoTag(string $id, array $options = [], array $attributes = []): string
    {
        return $this->cloudinary
            ->videoTag($id)
            ->addTransformation(VideoTransformation::fromParams($options))
            ->setAttributes($attributes)
            ->toTag();
    }
}
