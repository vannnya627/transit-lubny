<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

/**
 * Stream wrapper that looks like a readable regular file but fails to open,
 * so file_get_contents() returns false.
 */
final class FailingReadStreamWrapper
{
    public const string PROTOCOL = 'route-import-fail';

    /** @var resource|null */
    public mixed $context = null;

    public static function register(): void
    {
        if (!in_array(self::PROTOCOL, stream_get_wrappers(), true)) {
            stream_wrapper_register(self::PROTOCOL, self::class);
        }
    }

    public static function unregister(): void
    {
        if (in_array(self::PROTOCOL, stream_get_wrappers(), true)) {
            stream_wrapper_unregister(self::PROTOCOL);
        }
    }

    public static function path(): string
    {
        return self::PROTOCOL . '://file.json';
    }

    /**
     * @return array<string, int>
     */
    public function url_stat(string $path, int $flags): array
    {
        return [
            'dev' => 0,
            'ino' => 0,
            'mode' => 0100644,
            'nlink' => 1,
            'uid' => 0,
            'gid' => 0,
            'rdev' => 0,
            'size' => 1,
            'atime' => 0,
            'mtime' => 0,
            'ctime' => 0,
            'blksize' => -1,
            'blocks' => -1,
        ];
    }

    public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
    {
        return false;
    }
}
