<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Filesystem;

use App\Application\Import\Exceptions\InvalidRouteImportException;
use App\Infrastructure\Filesystem\RouteImportFileReader;
use App\Tests\AbstractTestCase;
use App\Tests\Fixtures\FailingReadStreamWrapper;
use PHPUnit\Framework\Attributes\Test;

final class RouteImportFileReaderTest extends AbstractTestCase
{
    private RouteImportFileReader $reader;

    protected function setUp(): void
    {
        $this->reader = new RouteImportFileReader();
    }

    #[Test]
    public function itReadsExistingFile(): void
    {
        $path = __FILE__;

        self::assertSame((string) file_get_contents($path), $this->reader->read($path));
    }

    #[Test]
    public function itRejectsMissingFile(): void
    {
        $path = sys_get_temp_dir() . '/missing-route-import-' . uniqid('', true) . '.json';

        $this->expectException(InvalidRouteImportException::class);
        $this->expectExceptionMessageIsOrContains('Файл імпорту не знайдено');

        $this->reader->read($path);
    }

    #[Test]
    public function itRejectsDirectory(): void
    {
        $this->expectException(InvalidRouteImportException::class);
        $this->expectExceptionMessageIsOrContains('Файл імпорту не знайдено');

        $this->reader->read(sys_get_temp_dir());
    }

    #[Test]
    public function itRejectsUnreadableFile(): void
    {
        if (function_exists('posix_geteuid') && 0 === posix_geteuid()) {
            self::markTestSkipped('Root can read any file, so the unreadable-file branch cannot be tested.');
        }

        $path = tempnam(sys_get_temp_dir(), 'route-import-unreadable-');
        self::assertNotFalse($path);
        self::assertTrue(chmod($path, 0o000));

        try {
            $this->expectException(InvalidRouteImportException::class);
            $this->expectExceptionMessageIsOrContains('Файл імпорту недоступний для читання');

            $this->reader->read($path);
        } finally {
            chmod($path, 0o600);
            unlink($path);
        }
    }

    #[Test]
    public function itRejectsFileWhenContentsCannotBeRead(): void
    {
        FailingReadStreamWrapper::register();

        set_error_handler(static fn(): bool => true);

        try {
            $this->expectException(InvalidRouteImportException::class);
            $this->expectExceptionMessageIsOrContains('Не вдалося прочитати файл імпорту');

            $this->reader->read(FailingReadStreamWrapper::path());
        } finally {
            restore_error_handler();
            FailingReadStreamWrapper::unregister();
        }
    }
}
