<?php

namespace App\Entity;

use App\Repository\QuizQuestionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: QuizQuestionRepository::class)]
#[ORM\Table(name: 'quiz_question')]
class QuizQuestion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 60, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank]
    private string $question = '';

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private string $optionA = '';

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private string $optionB = '';

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private string $optionC = '';

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private string $optionD = '';

    #[ORM\Column(length: 1)]
    #[Assert\Choice(choices: ['A', 'B', 'C', 'D'])]
    private string $correctOption = 'A';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function setQuestion(string $question): static
    {
        $this->question = $question;

        return $this;
    }

    public function getOptionA(): string
    {
        return $this->optionA;
    }

    public function setOptionA(string $optionA): static
    {
        $this->optionA = $optionA;

        return $this;
    }

    public function getOptionB(): string
    {
        return $this->optionB;
    }

    public function setOptionB(string $optionB): static
    {
        $this->optionB = $optionB;

        return $this;
    }

    public function getOptionC(): string
    {
        return $this->optionC;
    }

    public function setOptionC(string $optionC): static
    {
        $this->optionC = $optionC;

        return $this;
    }

    public function getOptionD(): string
    {
        return $this->optionD;
    }

    public function setOptionD(string $optionD): static
    {
        $this->optionD = $optionD;

        return $this;
    }

    public function getCorrectOption(): string
    {
        return $this->correctOption;
    }

    public function setCorrectOption(string $correctOption): static
    {
        $this->correctOption = $correctOption;

        return $this;
    }

    /**
     * @return array<string, string> option letter => option text
     */
    public function getOptions(): array
    {
        return [
            'A' => $this->optionA,
            'B' => $this->optionB,
            'C' => $this->optionC,
            'D' => $this->optionD,
        ];
    }
}
