<?php

declare(strict_types=1);

namespace App\ValueObjects;

use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

#[ORM\Embeddable]
final class Coordinates
{
    #[ORM\Column(name: 'lat', nullable: true)]
    public private(set) float $lat {
        set(float $value) => ($value < -90.0 || $value > 90.0)
            ? throw new InvalidArgumentException('Широта (lat) має бути в межах від -90 до 90') : $value;
    }

    #[ORM\Column(name: 'lon', nullable: true)]
    public private(set) float $lon {
        set(float $value) => ($value < -180.0 || $value > 180.0)
            ? throw new InvalidArgumentException('Довгота (lon) має бути в межах від -180 до 180') : $value;
    }

    private function __construct() {}

    public static function create(float $lat, float $lon): static
    {
        $coordinates = new self();
        $coordinates->lat = $lat;
        $coordinates->lon = $lon;

        return $coordinates;
    }
}
