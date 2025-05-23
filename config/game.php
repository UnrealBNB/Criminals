<?php

return [
    // Game mechanics
    'max_clicks_per_day' => env('GAME_MAX_CLICKS_PER_DAY', 50),
    'protection_hours' => env('GAME_PROTECTION_HOURS', 11),
    'bank_deposits_per_day' => env('GAME_BANK_DEPOSITS_PER_DAY', 5),
    'max_attacks_per_target' => env('GAME_MAX_ATTACKS_PER_TARGET', 5),
    'attack_cooldown_seconds' => env('GAME_ATTACK_COOLDOWN_SECONDS', 10),

    // Countries
    'countries' => [
        1 => 'Belgium',
        2 => 'Germany',
        3 => 'England',
        4 => 'France',
        5 => 'Italy',
        6 => 'Netherlands',
        7 => 'Sweden',
    ],

    // Ranks
    'ranks' => [
        ['name' => 'Zwerver', 'power_low' => 0, 'power_high' => 100],
        ['name' => 'Bedelaar', 'power_low' => 100, 'power_high' => 700],
        ['name' => 'Crimineel', 'power_low' => 700, 'power_high' => 1300],
        ['name' => 'Zakkenroller', 'power_low' => 1300, 'power_high' => 2000],
        ['name' => 'Tuig', 'power_low' => 2000, 'power_high' => 2800],
        ['name' => 'Geweldadig', 'power_low' => 2800, 'power_high' => 3700],
        ['name' => 'Autodief', 'power_low' => 3700, 'power_high' => 4700],
        ['name' => 'Drugsdealer', 'power_low' => 4700, 'power_high' => 5800],
        ['name' => 'Gangster', 'power_low' => 5800, 'power_high' => 7000],
        ['name' => 'Overvaller', 'power_low' => 7000, 'power_high' => 8800],
        ['name' => 'Bendeleider', 'power_low' => 8800, 'power_high' => 12000],
        ['name' => 'Godfather', 'power_low' => 12000, 'power_high' => 999999999],
    ],

    // Bank interest rate (5% daily)
    'bank_interest_rate' => 1.05,

    // Hourly income
    'hourly_cash' => 100,
    'hourly_bank_police' => 200, // Police type gets bank income

    // Clan income from special buildings
    'clan_income' => [
        1 => ['cash' => 50, 'bank' => 150],  // Drug dealer
        2 => ['cash' => 100, 'bank' => 100], // Scientist
        3 => ['cash' => 250, 'bank' => 0],   // Police
    ],
];