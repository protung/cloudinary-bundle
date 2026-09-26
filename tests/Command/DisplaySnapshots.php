<?php

declare(strict_types=1);

namespace Speicher210\CloudinaryBundle\Tests\Command;

use PHPUnit\Framework\Assert;
use Psl\Env;
use Psl\File;
use Psl\Regex;

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

        if (Env\get_var('UPDATE_SNAPSHOTS') === '1') {
            File\write($file, $display, File\WriteMode::Truncate);
        }

        Assert::assertSame(self::normalizeDisplay(File\read($file)), self::normalizeDisplay($display));
    }

    /**
     * Leaves out trailing spaces, and the table borders: Symfony 6.4 draws some of them differently than later versions.
     */
    private static function normalizeDisplay(string $display): string
    {
        return Regex\replace(Regex\replace($display, '/ +$/m', ''), '/^ *-[ -]*\n/m', '');
    }
}
