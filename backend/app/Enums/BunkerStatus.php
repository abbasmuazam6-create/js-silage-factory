<?php

namespace App\Enums;

enum BunkerStatus: string
{
    case Active = 'active';
    case Warning = 'warning';
    case Empty = 'empty';
    case Blocked = 'blocked';
}
