<?php

namespace App\Services;

class GameLogicService
{ 
    // Not set as instructions say it should be configurable. I'm assuming this means subject to change.
    protected array $words; 

    // Set during game
    protected string $answer; 

    // Not set as instructions say it should be configurable. I'm assuming this means subject to change.
    protected int $maxRounds; 

    protected int $currentRound = 0;

    protected array $winResponses = ["Genius", "Magnificent", "Impressive", "Splendid", "Great", "Phew"];

    public function __construct(array $words, int $maxRounds = 6)
    {
        $this->words = $words;
        $this->maxRounds = $maxRounds;

        // for task 1, answer is set on initiation. Would move for task 3.
        $this->answer = strtoupper($words[array_rand($words)]);
    }

    /*
    * Opted for 2 loops here for readability. 
    * The second loop already becomes a little harder to
    * follow with $i and $j indices.
    */
    public function compareAnswer(string $userGuess): string
    {
        // Increase round number at start of round - set as 0
        $this->currentRound++;

        // Instructions say case insensitive. Actual Wordle uses uppercase 
        $userGuess = strtoupper($userGuess);
        $result = str_repeat('_', 5);

        // create array of indices and boolean values to handle double letters. If a letter is in the correct position the index is flipped to true in order to avoid false placement.
        $allocated = array_fill(0, 5, false); 

        // Starting at first position, 0, compare each letter in the game's user's guess against the game's answer.
        for ($i = 0; $i < 5; $i++) {
            if ($userGuess[$i] === $this->answer[$i]) {
                // First check for exact match. Duplicate letters don't accidentally mark the letter as present at another position. 

                // Correct letter & position
                $result[$i] = '0'; 

                // $allocated index is set to true to skip next pass.
                $allocated[$i] = true; 
            } 
        }

        // Second pass through array to check for letters that are present but in the wrong position.
        for ($i = 0; $i < 5; $i++) {
            // Only continues if the position has not been filled, 
            if ($result[$i] === '_') { 

                // compares letter at index against all letters to see if it exists in the word
                for ($j = 0; $j < 5; $j++) {
                    if (!$allocated[$j] && $userGuess[$i] === $this->answer[$j]) {
                        // Sets the index of that position as present
                        $result[$i] = '?';

                        // Updates the index in the allocated array to be true as not to falsely suggest the letter is present elsewhere if the user has entered it again
                        $allocated[$j] = true;
                        break;
                    } 
                }
            }
        }
        return $result;
    }

    public function isWin(string $userGuess): bool
    {
        return strtoupper($userGuess) === $this->answer;
    }

    public function isLoss(): bool
    {
        return $this->currentRound >= $this->maxRounds;
    }

    public function getAnswer(): string
    {
        return $this->answer;
    }

    public function getWinResponse(): string
    {
        return $this->winResponses[$this->currentRound - 1];
    }

    public function getRemainingRounds(): int
    {
        return $this->maxRounds - $this->currentRound;
    }

    public function getCurrentRound(): int
    {
        return $this->currentRound;
    }

    public function getMaxRounds(): int
    {
        return $this->maxRounds;
    }

    public function setCurrentRound(int $newCurrentRound): void
    {
        $this->currentRound = $newCurrentRound;
    }

    public function setAnswer(string $answer): void
    {
        $this->answer = $answer;
    }
}
