<?php

declare(strict_types=1);

namespace App\Presentation\UI\API\Requests\QueryParameters;

use App\Application\DTOs\Inputs\GetStopDeparturesInput;
use App\Domain\ValueObjects\TimeOfDay;
use DateTimeImmutable;
use DateTimeInterface;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class DeparturesQueryParameter
{
    public function __construct(
        #[OA\Property(description: 'Час', example: '2026-09-25T15:00:00%2B03:00')]
        #[Context([DateTimeNormalizer::FORMAT_KEY => DateTimeInterface::ATOM])]
        public ?DateTimeImmutable $time = null,
        #[OA\Property(description: 'Кількість найближчіх рейсів', example: '10', default: GetStopDeparturesInput::DEFAULT_LIMIT)]
        #[Assert\Positive(message: 'Кількість має бути додатнім числом')]
        #[Assert\LessThanOrEqual(100, message: 'Максимум 100 рейсів')]
        public ?int $limit = null,
    ) {}

    public function toInput(int $stopId): GetStopDeparturesInput
    {
        return new GetStopDeparturesInput(
            stopId: $stopId,
            time: null !== $this->time ? TimeOfDay::fromDateTime($this->time) : TimeOfDay::now(),
            limit: $this->limit,
        );
    }
}
