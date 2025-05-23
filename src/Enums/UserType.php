<?php

declare(strict_types=1);

namespace App\Enums;

enum UserType: int
{
    case DRUG_DEALER = 1;
    case SCIENTIST = 2;
    case POLICE = 3;

    public function label(): string
    {
        return match($this) {
            self::DRUG_DEALER => 'Drugsdealer',
            self::SCIENTIST => 'Wetenschapper',
            self::POLICE => 'Politie',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRUG_DEALER => 'danger',
            self::SCIENTIST => 'info',
            self::POLICE => 'primary',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::DRUG_DEALER => 'cannabis',
            self::SCIENTIST => 'flask',
            self::POLICE => 'shield',
        };
    }

    public function canAttack(self $target): bool
    {
        return $this !== $target;
    }

    public static function options(): array
    {
        return array_map(
            fn(self $type) => [
                'value' => $type->value,
                'label' => $type->label(),
            ],
            self::cases()
        );
    }
}