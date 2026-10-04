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
final class Pattern
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) int $id;

    #[ORM\ManyToOne(targetEntity: Route::class, inversedBy: 'patterns')]
    #[ORM\JoinColumn(name: 'route_id', nullable: false, onDelete: 'CASCADE')]
    public private(set) Route $route;

    #[ORM\Column(enumType: Direction::class)]
    public private(set) Direction $direction;

    #[ORM\Column(length: 255)]
    public private(set) string $name {
        set(string $value) => (mb_strlen($value) > 255)
            ? throw new InvalidArgumentException('Максимальна довжина 255 символів') : $value;

    }

    /**
     * @var Collection<int, PatternStop>
     */
    #[ORM\OneToMany(targetEntity: PatternStop::class, mappedBy: 'pattern', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['stopSequence' => 'ASC'])]
    public private(set) Collection $patternStops;
    #[ORM\Column]
    public private(set) ?DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    public private(set) ?DateTimeImmutable $updatedAt = null;

    private function __construct()
    {
        $this->patternStops = new ArrayCollection();
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

    public static function create(Route $route, Direction $direction, string $name): static
    {
        $pattern = new self();
        $pattern->route = $route;
        $pattern->direction = $direction;
        $pattern->name = $name;

        return $pattern;
    }

    public function addPatternStop(PatternStop $patternStop): void
    {
        if (!$this->patternStops->contains($patternStop)) {
            $this->patternStops->add($patternStop);
        }
    }
}
