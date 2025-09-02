<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\GameLogicService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WordleController extends Controller
{
    public function fetch()
    {
        // Setup gameplay
        $wordLengths = 5;
        $maxRounds = 6;
        $words = ['HELLO','WORLD','QUITE','FANCY','FRESH','PANIC','CRAZY','BUGGY','SCARE'];

        $game = new GameLogicService($words, $maxRounds);
        
        // Setting to session so axios calls have same instance of GameLogicService
        session(['game' => $game]);

        return Inertia::render('Wordle', [
            'wordLength' => $wordLengths,
            'maxRounds' => $maxRounds,
        ]);
    }

    public function submit(Request $request)
    {
        // Validate input in the event frontend request has been manipulated
        $validated = $request->validate([
            'guess' => ['required', 'string', 'alpha', 'size:5'],
        ]);

        $guess = strtoupper($validated['guess']);

        $game = session('game');

        $result = $game->compareAnswer($guess);

        session(['game' => $game]);

        return response()->json([
            'result' => $result,
            'currentRound' => $game->getCurrentRound(),
            'isWin' => $game->isWin($guess),
            'isLoss' => $game->isLoss($guess),
        ]);
    }
}
