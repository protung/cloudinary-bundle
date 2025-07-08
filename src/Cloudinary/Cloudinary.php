<?php

declare(strict_types=1);

namespace Speicher210\CloudinaryBundle\Cloudinary;

use Override;

class Cloudinary extends \Cloudinary\Cloudinary
{
    #[Override]
    public function adminApi(): Admin
    {
        return new Admin($this->configuration);
    }

    #[Override]
    public function uploadApi(): Uploader
    {
        return new Uploader($this->configuration);
    }
}
