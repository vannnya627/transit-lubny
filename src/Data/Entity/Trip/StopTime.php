<?php

declare(strict_types=1);

namespace App\Data\Entity\Trip;

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

    #[ORM\Column]
    public private(set) int $arrivalTime;

    #[ORM\Column(name: 'is_estimated')]
    public private(set) bool $isEstimate;

    private function __construct() {}

    public static function create(Trip $trip, int $stopSequence, int $stopId, int $arrivalTime, bool $isEstimate): static
    {
        $stopTime = new self();
        $stopTime->trip = $trip;
        $stopTime->stopSequence = $stopSequence;
        $stopTime->stopId = $stopId;
        $stopTime->arrivalTime = $arrivalTime;
        $stopTime->isEstimate = $isEstimate;

        return $stopTime;
    }

    public function getFormattedArrivalTime(): string
    {
        $hour = floor($this->arrivalTime / 3600);
        $minute = floor(($this->arrivalTime % 3600) / 60);

        return sprintf('%02d:%02d', $hour, $minute);
    }

    public function getMinutesUntil(int $secondsFormMidnight): int
    {
        $diffInSeconds = $this->arrivalTime - $secondsFormMidnight;

        return (int) floor($diffInSeconds / 60);
    }
}
