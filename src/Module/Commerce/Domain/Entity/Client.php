<?php

declare(strict_types=1);

namespace App\Module\Commerce\Domain\Entity;

use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

class Client
{
    private readonly string $id;
    private string $email;
    private string $name;
    private string $surname;
    private DateTimeImmutable $updatedAt;
    private readonly DateTimeImmutable $createdAt;

    public function __construct(
        string $email,
        string $name,
        string $surname
    ) {
        $this->id = (string) Uuid::v1();
        $this->email = $email;
        $this->name = $name;
        $this->surname = $surname;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
