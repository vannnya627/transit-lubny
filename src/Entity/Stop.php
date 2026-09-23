<?php

declare(strict_types=1);

namespace App\Entity;

use App\ValueObjects\Coordinates;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity]
final class Stop
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) int $id;

    #[ORM\Column(length: 255)]
    public private(set) string $name {
        set(string $value) => (mb_strlen($value) > 255)
            ? throw new InvalidArgumentException('Максимальна довжина 255 символів') : $value;

    }

    #[ORM\Embedded(class: Coordinates::class, columnPrefix: false)]
    public private(set) ?Coordinates $coordinates = null;

    #[ORM\Column]
    public private(set) ?DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    public private(set) ?DateTimeImmutable $updatedAt = null;

    private function __construct() {}

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public static function create(string $name, ?Coordinates $coordinates = null): self
    {
        $stop = new self();
        $stop->name = $name;
        $stop->coordinates = $coordinates;

        return $stop;
    }
}
