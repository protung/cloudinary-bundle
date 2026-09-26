<?php

declare(strict_types=1);

namespace Speicher210\CloudinaryBundle\Tests\Command;

use PHPUnit\Framework\Assert;
use Psl\Regex;

use function file_get_contents;
use function file_put_contents;
use function getenv;

trait DisplaySnapshots
{
    /**
     * Compares the display of a command with the expected one in the Expected folder.
     *
     * Regenerate the expected displays with `just update-snapshots`, then review the diff before committing it.
     */
    private static function assertDisplayMatchesSnapshot(string $snapshot, string $display): void
    {
        $file = __DIR__ . '/Expected/' . $snapshot . '.txt';

        if (getenv('UPDATE_SNAPSHOTS') === '1') {
            file_put_contents($file, $display);
        }

        $expected = file_get_contents($file);
        Assert::assertIsString($expected, 'Missing snapshot ' . $file);
        Assert::assertSame(self::normalizeDisplay($expected), self::normalizeDisplay($display));
    }

    /**
     * Leaves out trailing spaces, and the table borders: Symfony 6.4 draws some of them differently than later versions.
     */
    private static function normalizeDisplay(string $display): string
    {
        return Regex\replace(Regex\replace($display, '/ +$/m', ''), '/^ *-[ -]*\n/m', '');
    }
}
