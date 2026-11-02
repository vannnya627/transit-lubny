<?php

declare(strict_types=1);

namespace App\Domain\ValueObjects;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

#[ORM\Embeddable]
final class TimeOfDay
{
    private const int SECONDS_IN_DAY = 86400;
    #[ORM\Column(name: 'arrival_time')]
    public private(set) int $value {
        set(int $value) => ($value < 0 || $value >= self::SECONDS_IN_DAY)
            ? throw new InvalidArgumentException('Час доби має бути в межах від 0 до 86399 секунд') : $value;
    }

    private function __construct(int $seconds)
    {
        $this->value = $seconds;
    }

    public static function now(?DateTimeZone $timeZone = null): self
    {
        $zone = $timeZone ?? new DateTimeZone('Europe/Kyiv');

        return self::fromDateTime(new DateTimeImmutable('now', $zone));
    }

    public static function fromDateTime(DateTimeInterface $time): self
    {
        $seconds = ((int) $time->format('H') * 3600) +
            ((int) $time->format('i') * 60) +
            ((int) $time->format('s'));

        return new self($seconds);
    }

    public static function fromString(string $timeString): self
    {
        $parts = explode(':', trim($timeString));
        if (count($parts) < 2) {
            throw new InvalidArgumentException(sprintf('Невірний формат часу: %s', $timeString));
        }

        $hours = (int) $parts[0];
        $minutes = (int) $parts[1];
        $seconds = isset($parts[2]) ? (int) $parts[2] : 0;

        return new self(($hours * 3600) + ($minutes * 60) + $seconds);
    }

    public static function fromSeconds(int $seconds): self
    {
        return new self($seconds);
    }

    public function getMinutesUntil(self $other): int
    {
        return (int) floor($this->value - $other->value / 60);
    }

    public function getFormattedArrivalTime(): string
    {
        $hour = floor($this->value / 3600);
        $minute = floor(($this->value % 3600) / 60);

        return sprintf('%02d:%02d', $hour, $minute);
    }

    public function isPast(int $seconds): bool
    {
        return $this->value > $seconds;
    }
}
