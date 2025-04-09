<?php

namespace App\Enum;

enum TaskStatus: string
{
    case SCHEDULED = 'Agendada';
    case COMPLETED = 'Completo';
    case OVERDUE = 'Atrasada';
}
