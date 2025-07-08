<?php

declare(strict_types=1);

namespace Speicher210\CloudinaryBundle\DependencyInjection\Compiler;

use Override;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Remove the Twig extension if Twig is not available.
 */
final class RemoveTwigExtensionPass implements CompilerPassInterface
{
    #[Override]
    public function process(ContainerBuilder $container): void
    {
        if ($container->hasDefinition('twig')) {
            return;
        }

        $container->removeDefinition('twig.extension.cloudinary');
    }
}
