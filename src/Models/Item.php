<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\Database\Model;

class Item extends Model
{
    protected string $table = 'items';
    protected string $primaryKey = 'item_id';

    protected array $fillable = [
        'item_name',
        'item_attack_power',
        'item_defence_power',
        'item_area',
        'item_costs',
        'item_sell',
    ];

    protected array $casts = [
        'item_id' => 'integer',
        'item_attack_power' => 'integer',
        'item_defence_power' => 'integer',
        'item_area' => 'integer',
        'item_costs' => 'integer',
        'item_sell' => 'integer',
    ];

    public function canBePurchasedBy(User $user): bool
    {
        return $user->cash >= $this->item_costs;
    }

    public function isForType(int $userType): bool
    {
        // Special items for specific types
        $typeSpecificAreas = [
            6 => 2,  // Scientists
            7 => 3,  // Police
            9 => 1,  // Drug dealers
        ];

        if (isset($typeSpecificAreas[$this->item_area])) {
            return $typeSpecificAreas[$this->item_area] === $userType;
        }

        // General items (areas 1-5) available to all
        return $this->item_area >= 1 && $this->item_area <= 5;
    }
}