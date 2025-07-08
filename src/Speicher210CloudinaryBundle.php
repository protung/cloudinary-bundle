<?php

declare(strict_types=1);

namespace Speicher210\CloudinaryBundle;

use Override;
use Speicher210\CloudinaryBundle\DependencyInjection\Compiler\RemoveTwigExtensionPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class Speicher210CloudinaryBundle extends Bundle
{
    #[Override]
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RemoveTwigExtensionPass());
    }
}
