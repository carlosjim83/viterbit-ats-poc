<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Application\Handler;

use App\Application\Application\DTO\ApplicationDetailDTO;
use App\Application\Application\Handler\GetApplicationDetailHandler;
use App\Application\Application\Query\GetApplicationDetail;
use App\Application\Domain\Model\ValueObject\ApplicationId;
use App\Application\Domain\Repository\JobApplicationRepository;
use App\Tests\Helpers\Mother\JobApplicationMother;
use PHPUnit\Framework\TestCase;

final class GetApplicationDetailHandlerTest extends TestCase
{
    public function testItReturnsDetailDtoWhenApplicationExists(): void
    {
        $repository = $this->createMock(JobApplicationRepository::class);
        $handler = new GetApplicationDetailHandler($repository);

        $application = JobApplicationMother::create();

        $repository->method('findById')
            ->with($this->equalTo($application->id))
            ->willReturn($application);

        $dto = $handler->handle(new GetApplicationDetail((string) $application->id));

        self::assertInstanceOf(ApplicationDetailDTO::class, $dto);
        self::assertSame((string) $application->id, $dto->id);
        self::assertSame($application->fullName->value, $dto->fullName);
        self::assertSame($application->email->value, $dto->email);
        self::assertSame($application->position->value, $dto->position);
        self::assertSame($application->phone->value, $dto->phone);
        self::assertSame($application->notes->value, $dto->notes);
        self::assertSame($application->cvText->value, $dto->cvText);
        self::assertSame($application->status->value, $dto->status);
    }

    public function testItReturnsNullWhenApplicationDoesNotExist(): void
    {
        $repository = $this->createMock(JobApplicationRepository::class);
        $handler = new GetApplicationDetailHandler($repository);

        $repository->method('findById')
            ->with($this->equalTo(ApplicationId::fromString('550e8400-e29b-41d4-a716-446655440000')))
            ->willReturn(null);

        $dto = $handler->handle(new GetApplicationDetail('550e8400-e29b-41d4-a716-446655440000'));

        self::assertNull($dto);
    }
}
