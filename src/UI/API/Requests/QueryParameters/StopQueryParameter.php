<?php

declare(strict_types=1);

namespace App\UI\API\Requests\QueryParameters;

use App\Data\ValueObjects\Coordinates;
use App\DTOs\Input\SearchStopsInput;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final readonly class StopQueryParameter
{
    public private(set) ?Coordinates $coordinates;

    public function __construct(
        #[OA\Property(description: 'Пошук за назвою зупинки', example: 'Лікарня')]
        public ?string $q = null,
        #[OA\Property(description: 'Широта', example: '50.0123')]
        #[Assert\Range(notInRangeMessage: 'Широта має бути від -90 до 90', min: -90, max: 90)]
        public ?float $lat = null,
        #[OA\Property(description: 'Довгота', example: '33.0123')]
        #[Assert\Range(notInRangeMessage: 'Довгота має бути від -180 до 180', min: -180, max: 180)]
        public ?float $lon = null,
        #[OA\Property(description: 'Радіус у метрах', example: '500', default: SearchStopsInput::DEFAULT_RADIUS)]
        #[Assert\Positive(message: 'Радіус має бути додатнім числом')]
        public ?int $radius = null,
    ) {
        $this->coordinates = (null !== $this->lat && null !== $this->lon)
            ? Coordinates::create($this->lat, $this->lon)
            : null;
    }

    #[Assert\Callback]
    public function validateCoordinates(ExecutionContextInterface $context): void
    {
        if ((null === $this->lat) !== (null === $this->lon)) {
            $context->buildViolation('Широта (lat) та довгота (lon) повинні передаватися тільки разом.')
                ->atPath('lat')
                ->addViolation();
        }
    }

    public function toInput(): SearchStopsInput
    {
        return new SearchStopsInput(
            q: $this->q,
            coordinates: $this->coordinates,
            radius: $this->radius,
        );
    }
}
