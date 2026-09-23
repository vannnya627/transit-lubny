<?php

declare(strict_types=1);

namespace App\Entity\Route;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
final class PatternStop
{
    #[ORM\Id]
    #[ORM\ManyToOne(targetEntity: Pattern::class, inversedBy: 'patternStops')]
    #[ORM\JoinColumn(name: 'pattern_id', nullable: false, onDelete: 'CASCADE')]
    public private(set) Pattern $pattern;

    #[ORM\Id]
    #[ORM\Column(type: 'smallint')]
    public private(set) int $stopSequence;

    #[ORM\Column]
    public private(set) int $stopId;

    private function __construct() {}

    public static function create(Pattern $pattern, int $stopSequence, int $stopId): static
    {
        $patternStop = new self();
        $patternStop->pattern = $pattern;
        $patternStop->stopSequence = $stopSequence;
        $patternStop->stopId = $stopId;

        return $patternStop;
    }
}
