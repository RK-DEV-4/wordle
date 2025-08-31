<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\GameLogicService;

class PlayWordle extends Command
{
    protected $signature = 'wordle:play';

    protected $description = 'Play Wordle in the terminal using the GameLogicService';

    public function handle()
    {
        // words to be passed through to service.
        $words = ['HELLO','WORLD','QUITE','FANCY','FRESH','PANIC','CRAZY','BUGGY','SCARE'];

        // create new isntance of game
        $game = new GameLogicService($words);

        $this->info("Play Wordle");

        // while the current round remains below the maximum allow user entry
        while (!$game->isLoss()) {
            $guess = $this->ask('Type your guess');

            // ensure the correct number of letters is input
            if (strlen($guess) !== 5) {
                $this->error("Word must be 5 letters.");
                continue;
            }
            // ensure only english alphabet is used
            if (!preg_match('/^[A-Z]+$/i', $guess)) {
                $this->error("Please only use alphabetic characters");
                continue;
            }

            // Get result to display
            $result = $game->compareAnswer($guess);

            // Check to see if the user has won
            if ($game->isWin($guess)) {
                $this->info($game->getAnswer() . PHP_EOL);
                $this->info($game->getWinResponse() . PHP_EOL);
                return 0;
            }

            $this->line($result . PHP_EOL);
            $this->info("Guesses remaining: " .$game->getRemainingRounds() . PHP_EOL);

        }

        $this->info("Thanks for playing today! The word was: " . $game->getAnswer());
    }
}
