<?php

declare(strict_types=1);


namespace App\Module\Commerce\Domain\Entity;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Uid\Uuid;

class Product
{
    private ?int $id = null;
    private string $name;
    private string $slug;
    private float $price;
    private ?DateTimeImmutable $deletedAt;
    private DateTimeImmutable $updatedAt;
    private readonly DateTimeImmutable $createdAt;

    public function __construct(
        string $name,
        float $price,
    ) {
        // $this->categories = new ArrayCollection();
        $this->name = $name;
        $this->slug = $this->generateSlug($name);
        $this->price = $price;
        $this->deletedAt = null;
        
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function refreshUpdatedAtValue(): void
    {
        $this->slug = $this->generateSlug($this->name);
        $this->updatedAt = new DateTimeImmutable();
    }

    private function generateSlug(string $name): string
    {
        return strtolower(
            (new AsciiSlugger())->slug($name) . '-' . substr((string) Uuid::v1(), 0, 8),
        );
    }

    public function getId(): int
    {
        return $this->id;
    }

    // public function getCategories(): Collection
    // {
    //     return $this->categories;
    // }

    // public function addCategory(Category $category): self
    // {
    //     if (!$this->categories->contains($category)) {
    //         $this->categories->add($category);
    //         $category->addProduct($this); 
    //     }

    //     return $this;
    // }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getDeletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;
        return $this;
    }
} 
