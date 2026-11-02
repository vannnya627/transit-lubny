<?php

declare(strict_types=1);

namespace App\Domain\Entity\Trip;

use App\Domain\ValueObjects\TimeOfDay;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class StopTime
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Trip::class, inversedBy: 'stopTimes')]
    #[ORM\JoinColumn(name: 'trip_id', nullable: false, onDelete: 'CASCADE')]
    public private(set) Trip $trip;

    #[ORM\Id]
    #[ORM\Column(type: 'smallint')]
    public private(set) int $stopSequence;

    #[ORM\Column]
    public private(set) int $stopId;

    #[ORM\Embedded(class: TimeOfDay::class, columnPrefix: false)]
    public private(set) TimeOfDay $arrivalTime;

    #[ORM\Column(name: 'is_estimated')]
    public private(set) bool $isEstimate;

    private function __construct() {}

    public static function create(Trip $trip, int $stopSequence, int $stopId, TimeOfDay $arrivalTime, bool $isEstimate): static
    {
        $stopTime = new self();
        $stopTime->trip = $trip;
        $stopTime->stopSequence = $stopSequence;
        $stopTime->stopId = $stopId;
        $stopTime->arrivalTime = $arrivalTime;
        $stopTime->isEstimate = $isEstimate;

        return $stopTime;
    }

    public function getMinutesUntil(TimeOfDay $currentTime): int
    {
        return $this->arrivalTime->getMinutesUntil($currentTime);
    }

    public function getFormattedArrivalTime(): string
    {
        return $this->arrivalTime->getFormattedArrivalTime();
    }

    public function getArrivalTimeInSeconds(): int
    {
        return $this->arrivalTime->value;
    }
}
