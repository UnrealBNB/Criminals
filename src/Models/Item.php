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

    // Item Areas
    const AREA_WEAPONS = 1;
    const AREA_PROTECTION = 2;
    const AREA_DEFENSE = 3;
    const AREA_ACCESSORIES = 4;
    const AREA_SPECIAL = 5;
    const AREA_SCIENTIST_SPECIAL = 6;
    const AREA_POLICE_SPECIAL = 7;
    const AREA_CLAN = 8;
    const AREA_CLAN_DRUG_DEALER = 9;
    const AREA_CLAN_SCIENTIST = 10;
    const AREA_CLAN_POLICE = 11;

    public function getAreaName(): string
    {
        return match($this->item_area) {
            self::AREA_WEAPONS => 'Weapons',
            self::AREA_PROTECTION => 'Protection',
            self::AREA_DEFENSE => 'Defense',
            self::AREA_ACCESSORIES => 'Accessories',
            self::AREA_SPECIAL => 'Special',
            self::AREA_SCIENTIST_SPECIAL => 'Scientist Special',
            self::AREA_POLICE_SPECIAL => 'Police Special',
            self::AREA_CLAN => 'Clan',
            self::AREA_CLAN_DRUG_DEALER => 'Clan Drug Dealer',
            self::AREA_CLAN_SCIENTIST => 'Clan Scientist',
            self::AREA_CLAN_POLICE => 'Clan Police',
            default => 'Unknown',
        };
    }

    public function isUserItem(): bool
    {
        return $this->item_area <= 7;
    }

    public function isClanItem(): bool
    {
        return $this->item_area >= 8;
    }

    public function canBePurchasedByType(int $userType): bool
    {
        // Special items for specific types
        if ($this->item_area === self::AREA_SCIENTIST_SPECIAL && $userType !== 2) {
            return false;
        }

        if ($this->item_area === self::AREA_POLICE_SPECIAL && $userType !== 3) {
            return false;
        }

        // Clan type-specific items
        if ($this->item_area === self::AREA_CLAN_DRUG_DEALER && $userType !== 1) {
            return false;
        }

        if ($this->item_area === self::AREA_CLAN_SCIENTIST && $userType !== 2) {
            return false;
        }

        if ($this->item_area === self::AREA_CLAN_POLICE && $userType !== 3) {
            return false;
        }

        return true;
    }

    public static function getByArea(int $area): array
    {
        return static::query()
            ->where('item_area', $area)
            ->orderBy('item_costs')
            ->get();
    }

    public static function getUserShopItems(int $userType): array
    {
        $areas = [
            self::AREA_WEAPONS,
            self::AREA_PROTECTION,
            self::AREA_DEFENSE,
            self::AREA_ACCESSORIES,
            self::AREA_SPECIAL,
        ];

        // Add type-specific areas
        if ($userType === 2) {
            $areas[] = self::AREA_SCIENTIST_SPECIAL;
        } elseif ($userType === 3) {
            $areas[] = self::AREA_POLICE_SPECIAL;
        }

        return static::query()
            ->whereIn('item_area', $areas)
            ->orderBy('item_area')
            ->orderBy('item_costs')
            ->get();
    }

    public static function getClanShopItems(int $clanType): array
    {
        $areas = [self::AREA_CLAN];

        // Add type-specific clan items
        $areas[] = match($clanType) {
            1 => self::AREA_CLAN_DRUG_DEALER,
            2 => self::AREA_CLAN_SCIENTIST,
            3 => self::AREA_CLAN_POLICE,
            default => 0,
        };

        return static::query()
            ->whereIn('item_area', $areas)
            ->orderBy('item_costs')
            ->get();
    }
}