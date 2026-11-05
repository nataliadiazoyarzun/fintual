<?php

declare(strict_types=1);

namespace App\Domain;

enum Action: string
{
    case BUY = 'BUY';
    case SELL = 'SELL';
}
