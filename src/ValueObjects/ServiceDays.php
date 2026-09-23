<?php

declare(strict_types=1);

namespace App\ValueObjects;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class ServiceDays
{
    public function __construct(
        #[ORM\Column]
        public bool $monday = false,
        #[ORM\Column]
        public bool $tuesday = false,
        #[ORM\Column]
        public bool $wednesday = false,
        #[ORM\Column]
        public bool $thursday = false,
        #[ORM\Column]
        public bool $friday = false,
        #[ORM\Column]
        public bool $saturday = false,
        #[ORM\Column]
        public bool $sunday = false,
    ) {}

    public static function weekdaysOnly(): self
    {
        return new self(true, true, true, true, true, false, false);
    }

    public static function weekendsOnly(): self
    {
        return new self(false, false, false, false, false, true, true);
    }

    public static function everyday(): self
    {
        return new self(true, true, true, true, true, true, true);
    }
}
