<?php

declare(strict_types=1);

namespace Speicher210\CloudinaryBundle\Command;

use Cloudinary\Api\ApiResponse;
use Override;
use Psl\Str;
use Psl\Type;
use Speicher210\CloudinaryBundle\Cloudinary\Admin;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class DeleteCommand extends Command
{
    private readonly Admin $cloudinary;

    public function __construct(Admin $cloudinary)
    {
        $this->cloudinary = $cloudinary;

        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this
            ->setName('sp210:cloudinary:delete')
            ->setDescription('Remove resources from Cloudinary based on criteria.')
            ->addOption(
                'prefix',
                null,
                InputOption::VALUE_REQUIRED,
                'The prefix for the resources to remove.',
            )
            ->addOption(
                'resource',
                null,
                InputOption::VALUE_REQUIRED,
                'Remove one resource by public ID.',
            );
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $symfonyStyle = new SymfonyStyle($input, $output);

        $prefix   = Type\nullable(Type\string())->coerce($input->getOption('prefix'));
        $resource = Type\nullable(Type\string())->coerce($input->getOption('resource'));

        if ($prefix === '' || $resource === '') {
            $symfonyStyle->error('--prefix and --resource can not be empty.');

            return Command::INVALID;
        }

        if ($prefix === null && $resource === null) {
            $symfonyStyle->error('Choose the resources to remove with --prefix or --resource.');

            return Command::INVALID;
        }

        $confirm = $symfonyStyle->confirm('Are you sure you want to remove all resources based on your criteria?', false);

        if ($confirm !== true) {
            return Command::SUCCESS;
        }

        if ($prefix !== null) {
            $this->removeByPrefix($prefix, $symfonyStyle);
        }

        if ($resource !== null) {
            $this->removeResource($resource, $symfonyStyle);
        }

        return Command::SUCCESS;
    }

    private function removeByPrefix(string $prefix, SymfonyStyle $symfonyStyle): void
    {
        $symfonyStyle->writeln(
            Str\format('<comment>Removing all resources from <info>%s</info></comment>', $prefix),
        );

        $response = $this->cloudinary->deleteAssetsByPrefix($prefix);
        $this->outputApiResponse($response, $symfonyStyle);
    }

    private function removeResource(string $resource, SymfonyStyle $symfonyStyle): void
    {
        $symfonyStyle->writeln(
            Str\format('<comment>Removing resource <info>%s</info></comment>', $resource),
        );

        $response = $this->cloudinary->deleteAssets($resource);
        $this->outputApiResponse($response, $symfonyStyle);
    }

    private function outputApiResponse(ApiResponse $response, SymfonyStyle $symfonyStyle): void
    {
        $table = $symfonyStyle->createTable();
        $table->setHeaders(['Resource', 'Status']);

        $deleted = Type\shape(['deleted' => Type\dict(Type\array_key(), Type\string())], true)
            ->coerce($response->getArrayCopy())['deleted'];

        foreach ($deleted as $publicId => $status) {
            $table->addRow([$publicId, $status]);
        }

        $table->render();
    }
}
