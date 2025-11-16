<?php

namespace App\Enums;

enum ProductMetric: string
{
    case Kilogram = 'kg';
    case Liter = 'liter';
    case Piece = 'piece';
    case Dozen = 'dozen';
}
