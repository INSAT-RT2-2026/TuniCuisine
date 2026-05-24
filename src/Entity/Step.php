<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\StepRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StepRepository::class)]
#[ORM\Table(name: 'steps')]
class Step
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'steps')]
    #[ORM\JoinColumn(nullable: false)]
    private Recipe $recipe;

    #[ORM\Column]
    private int $stepOrder;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tip = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRecipe(): Recipe
    {
        return $this->recipe;
    }

    public function setRecipe(Recipe $recipe): self
    {
        $this->recipe = $recipe;
        return $this;
    }

    public function getStepOrder(): int
    {
        return $this->stepOrder;
    }

    public function setStepOrder(int $stepOrder): self
    {
        $this->stepOrder = $stepOrder;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getTip(): ?string
    {
        return $this->tip;
    }

    public function setTip(?string $tip): self
    {
        $this->tip = $tip;
        return $this;
    }
}
