Cloudinary Bundle
=================

[![Build](https://github.com/protung/cloudinary-bundle/actions/workflows/build.yml/badge.svg?branch=1.x)](https://github.com/protung/cloudinary-bundle/actions/workflows/build.yml?query=branch%3A1.x)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg)](LICENSE.md)

Symfony bundle for the [Cloudinary PHP SDK](https://github.com/cloudinary/cloudinary_php).

## Installation

Require using composer:

```shell
$ composer require speicher210/cloudinary-bundle
```

Symfony Flex registers the bundle. Without Flex, add it to `config/bundles.php`:

```php
return [
    // ...
    Speicher210\CloudinaryBundle\Speicher210CloudinaryBundle::class => ['all' => true],
    // ...
];
```

## Configuration

Configure the cloud and the credentials with the `CLOUDINARY_URL` from the Cloudinary console, in the form `cloudinary://api_key:api_secret@cloud_name`:

```yaml
# config/packages/speicher210_cloudinary.yaml
speicher210_cloudinary:
    url: '%env(CLOUDINARY_URL)%'
```

Or set them one by one. Values from the URL take precedence:

```yaml
speicher210_cloudinary:
    cloud_name: my-cloud
    access_identifier:
        api_key: my-key
        api_secret: my-secret
    secure: true # HTTPS URLs, the default
```

## Usage

### Services

| Service id                          | Class                                                | Autowired by                                       |
|-------------------------------------|------------------------------------------------------|----------------------------------------------------|
| `speicher210_cloudinary.cloudinary` | `Speicher210\CloudinaryBundle\Cloudinary\Cloudinary` | `Cloudinary\Cloudinary`                            |
| `speicher210_cloudinary.admin`      | `Speicher210\CloudinaryBundle\Cloudinary\Admin`      | `Speicher210\CloudinaryBundle\Cloudinary\Admin`    |
| `speicher210_cloudinary.uploader`   | `Speicher210\CloudinaryBundle\Cloudinary\Uploader`   | `Speicher210\CloudinaryBundle\Cloudinary\Uploader` |

The classes extend the SDK's `Cloudinary\Cloudinary`, `Cloudinary\Api\Admin\AdminApi` and
`Cloudinary\Api\Upload\UploadApi`, configured by the bundle:

```php
use Speicher210\CloudinaryBundle\Cloudinary\Uploader;

final readonly class AvatarStorage
{
    public function __construct(private Uploader $uploader)
    {
    }

    public function store(string $file, string $userId): string
    {
        return $this->uploader->upload($file, ['public_id' => 'avatars/' . $userId])['secure_url'];
    }
}
```

### Twig

With TwigBundle enabled, the bundle adds Twig functions, also available as filters, for the URL of an image and for
image, picture and video tags. The options of `cloudinary_url` are
[transformation parameters](https://cloudinary.com/documentation/transformation_reference):

```twig
{{ cloudinary_url('sample') }}
{{ 'sample'|cloudinary_url({'width': 100, 'height': 100, 'crop': 'fill'}) }}

{{ cloudinary_image_tag('sample') }}
{{ cloudinary_picture_tag('sample') }}
{{ cloudinary_video_tag('dog') }}
```

### Console commands

```shell
# Upload the files of a directory, subdirectories included. The public ID of a file is the prefix followed by its
# name without the extension; an existing asset with the same public ID is overwritten.
$ bin/console sp210:cloudinary:upload path/to/images --prefix=products/ --filter='*.jpg'

# Show the properties of an asset, with -v also its derived resources.
$ bin/console sp210:cloudinary:info products/shoe -v

# Delete the assets with a public ID prefix, or one asset. Asks for confirmation first.
$ bin/console sp210:cloudinary:delete --prefix=products/
$ bin/console sp210:cloudinary:delete --resource=products/shoe
```

## Development

The tools are run through [just](https://github.com/casey/just) (`just --list` shows every recipe):

```shell
$ just check             # coding standard, static analysis, composer audit and tests
$ just test              # tests
$ just update-snapshots  # regenerate the expected output of the console commands
```

## License

This package is released under the [MIT license](LICENSE.md).
