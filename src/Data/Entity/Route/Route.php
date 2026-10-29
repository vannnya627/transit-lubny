<?php

declare(strict_types=1);

namespace App\Data\Entity\Route;

use App\Enum\Direction;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity]
final class Route
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) int $id;
    #[ORM\Column(length: 255)]
    public private(set) string $shortName {
        set(string $value) => (mb_strlen($value) > 255)
            ? throw new InvalidArgumentException('Максимальна довжина 255 символів') : $value;

    }

    #[ORM\Column(length: 255)]
    public private(set) string $longName {
        set(string $value) => (mb_strlen($value) > 255)
            ? throw new InvalidArgumentException('Максимальна довжина 255 символів') : $value;
    }

    /**
     * @var Collection<int, Pattern>
     */
    #[ORM\OneToMany(targetEntity: Pattern::class, mappedBy: 'route', cascade: ['persist', 'remove'], orphanRemoval: true)]
    public private(set) Collection $patterns;

    #[ORM\Column]
    public private(set) ?DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    public private(set) ?DateTimeImmutable $updatedAt = null;

    private function __construct()
    {
        $this->patterns = new ArrayCollection();
    }

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

    public static function create(string $shortName, string $longName): static
    {
        $route = new self();
        $route->shortName = $shortName;
        $route->longName = $longName;

        return $route;
    }

    public function createPattern(Direction $direction, string $name): Pattern
    {
        $pattern = Pattern::create($this, $direction, $name);
        $this->addPattern($pattern);

        return $pattern;
    }

    private function addPattern(Pattern $pattern): void
    {
        if (!$this->patterns->contains($pattern)) {
            $this->patterns->add($pattern);
        }
    }
}
