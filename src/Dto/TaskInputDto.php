<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class TaskInputDto
{
    #[Assert\NotBlank(message: 'O nome da tarefa é obrigatório.')]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: 'O nome deve ter pelo menos {{ limit }} caracteres.',
        maxMessage: 'O nome não pode ter mais de {{ limit }} caracteres.'
    )]
    public string $name;

    #[Assert\NotBlank(message: 'A descrição da tarefa é obrigatória.')]
    public string $description;

    #[Assert\NotNull(message: 'A data limite é obrigatória.')]
    #[Assert\Type(
        type: \DateTimeInterface::class,
        message: 'A data limite deve ser uma data válida.'
    )]
    #[Assert\GreaterThan('today', message: 'A data limite deve ser hoje ou no futuro.')]
    public \DateTimeInterface $deadline;
}