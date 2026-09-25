<?php

declare(strict_types=1);

namespace App\Entity\Trip;

use App\ValueObjects\ServiceDays;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LogicException;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity]
final class Trip
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public private(set) int $id;

    #[ORM\Column]
    public private(set) int $patternId;

    #[ORM\Embedded(class: ServiceDays::class, columnPrefix: false)]
    public private(set) ServiceDays $serviceDays;

    /**
     * @var Collection<int, StopTime>
     */
    #[ORM\OneToMany(targetEntity: StopTime::class, mappedBy: 'trip', cascade: ['persist', 'remove'], orphanRemoval: true)]
    public private(set) Collection $stopTimes;

    #[ORM\Column]
    public private(set) ?DateTimeImmutable $createdAt = null;

    #[ORM\Column]
    public private(set) ?DateTimeImmutable $updatedAt = null;

    private function __construct()
    {
        $this->stopTimes = new ArrayCollection();
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

    public static function create(int $patternId, ServiceDays $serviceDays): static
    {
        $trip = new self();
        $trip->patternId = $patternId;
        $trip->serviceDays = $serviceDays;

        return $trip;
    }

    public function getFirstStopTime(): StopTime
    {
        $stopTime = $this->stopTimes->first();
        if (!$stopTime) {
            throw new LogicException('У цього рейсу відсутні зупинки');
        }

        return $stopTime;
    }
}
