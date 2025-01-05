<?php

namespace App\Enums;

enum Currency: string
{
    case Pound = 'pound';
    
    // case Naira = 'naira';
    case Dollar = 'dollar';

    case Euro = 'euro';


    public function symbol(): string
    {
        return match ($this) {
            self::Dollar => '$',
            // self::Naira => '₦',
            self::Pound => '£',
            self::Euro => '€',
        };
    }

    public static function options(): array
    {
        return array_map(fn ($case) => ['label' => $case->name . "({$case->symbol()})", 'value' => $case->value], self::cases());
    }
}