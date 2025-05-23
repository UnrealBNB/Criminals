<?php

declare(strict_types=1);

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FlightController extends Controller
{
    private array $countries = [
        1 => 'Belgium',
        2 => 'Germany',
        3 => 'England',
        4 => 'France',
        5 => 'Italy',
        6 => 'Netherlands',
        7 => 'Sweden',
    ];

    private const FLIGHT_COST = 250;

    public function index(): Response
    {
        $user = $this->auth()->user();
        $currentCountry = $this->countries[$user->country_id] ?? 'Unknown';

        return $this->view('game.flight', [
            'user' => $user,
            'countries' => $this->countries,
            'currentCountry' => $currentCountry,
            'flightCost' => self::FLIGHT_COST,
        ]);
    }

    public function fly(Request $request): Response
    {
        $user = $this->auth()->user();
        $countryId = (int) $request->request->get('country');

        // Validate
        if (!isset($this->countries[$countryId])) {
            flash('error', 'This country does not exist!');
            return $this->back();
        }

        if ($countryId === $user->country_id) {
            flash('error', 'You are already in ' . $this->countries[$countryId] . '!');
            return $this->back();
        }

        if ($user->cash < self::FLIGHT_COST) {
            flash('error', 'A ticket costs €' . self::FLIGHT_COST . ' cash');
            return $this->back();
        }

        // Process flight
        $user->cash -= self::FLIGHT_COST;
        $user->country_id = $countryId;

        if ($user->save()) {
            flash('success', 'You paid ' . self::FLIGHT_COST . ' and are now in ' . $this->countries[$countryId] . '!');
        } else {
            flash('error', 'Flight failed. Please try again.');
        }

        return $this->back();
    }
}