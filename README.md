# Wordle Assignment
<img width="531" height="795" alt="image" src="https://github.com/user-attachments/assets/9d259366-f3c5-44cd-9609-b44a2feda58b" />

## Get Started

```
brew install php
brew install composer
git clone https://github.com/RK-DEV-4/wordle.git
cd wordle
composer install --no-interaction --prefer-dist
brew install node@18
npm ci --legacy-peer-deps
cp .env.example .env
php artisan key:generate
```

From here, you can play Wordle in the terminal using the command 
`php artisan wordle:play`

To access the client side running ```npm run dev``` will start the server and client. Visit http://localhost:8000


## Project Overview

Tasks 1 & 2 complete.

I opted to build with Laravel (php) and Svelte.js with tailwind to quickly apply styling as these are what I use daily in my current role. I am happy to learn/adopt new languages. 

I used a service - `GameLogicService` - to handle the logic for the terminal and client versions. I approached it this way to utilise the same logic for both and avoid unnecessary duplication.

I opted to have `$words` passed in to the Service as a prop since the task dictated that the 'answer should be selected from a predefined list (configurable)'. I took this to mean the list might be edited at a point in future. Building out a complete app I would likely utilise a database to store words and build an admin portal for someone to log in and update the word list. As a short term, with more time I was going to build a command in the terminal game that would allow the user to add new words.

The main functionality is the `compareAnswer` method.

I determined that the letter being in the correct location was the most important step and shouldn't be overwritten by duplicates later in the answer or guess.

I opted to use two `for` loops for readability and maintainability. One pass through would be more efficient but it would become difficult to understand the information as an onlooker. As I mention in the comments in the code, I felt the nesting in the second loop where I am comparing indices different data sources was beginning to become more complex.

The second loop checks all remaining places - 'misses' - in the result. It loops through to see if the answer letter can match any position in the guess, setting it as 'present' if it does and 'allocating' it to accommodate duplicate issues - a user guessing a letter twice and being shown as present twice but there being only one.

```php
public function compareAnswer(string $userGuess): string
{
    $this->currentRound++;

    $userGuess = strtoupper($userGuess);
    $result = str_repeat('_', 5);

    $allocated = array_fill(0, 5, false); 

    for ($i = 0; $i < 5; $i++) {
        if ($userGuess[$i] === $this->answer[$i]) {
            $result[$i] = '0'; 
            $allocated[$i] = true; 
        } 
    }

    for ($i = 0; $i < 5; $i++) {
        if ($result[$i] === '_') { 
            for ($j = 0; $j < 5; $j++) {
                if (!$allocated[$j] && $userGuess[$i] === $this->answer[$j]) {
                    $result[$i] = '?';
                    $allocated[$j] = true;
                    break;
                } 
            }
        }
    }
    return $result;
}
```

Validation is handled in the Command file - ensuring 5 letters in a guess and only alphabetic characters. The user is determined to have won if they guess the answer correctly within the `$maxRounds`. This too is passed as prop in order to be configurable.

In task 2 there is validation on the client and server - in the controller. The assignment 

The design is largely styled on the Wordle website with reactivity built in.

I opted to have the input grid be `<div>` elements over segmented inputs to avoid the complication of autofocusing and focusing in general. The guess string is parsed into the divs as the user types.

I opted to build my own keyboard instead of using a package in order to have control over styling - particularly dynamic styles. The onscreen and physical keyboard utilise the same input function.

My approach was to build the initial route and page before adding a `submit` route to guess. The information returned is used to give the user visual feedback. Like Wordle, I then passed this to the keyboard component to allow the user to see what letters had been used on the keyboard instead of having to repeatedly revisit the grid.

The functionality here takes the same data from 'parent' `Wordle.svelte` file and reduces it and remove duplicates. Again, I determined correct position to be most important. The key should be green in the user EVER had it in the correct place, it should not be overwritten if they move it or omit it.

```js
function applyKeyStyling() {
  setTimeout(() => {
    const keysSortedByStatus = keyStates.flat().sort((a, b) => statusPriorty[b.value] - statusPriorty[a.value]);
    
    usedKeys = keysSortedByStatus.reduce((acc, {letter, value}) => {
      if (!(letter in acc)) {
        acc[letter] = value;
      }
      return acc;
    }, []);
  }, 1000);
}
```

The same gameplay logic exists, the use wins if they guess in the max guess timeline or losses if they don't. A message - based on Wordle responses - displays for a win. The answer displays for a loss.

In order to mimic Wordle, where you can refresh the page and still have your progress or can only play once per day - I implemented storage in the browser.

Data is stored there, and when the page mounts, a request is made to local storage to see if the game has been played/is being played. If the date does not match, local storage is cleared and a new game can begin.

```js
function getLocalStorage() {
    const storedData = localStorage.getItem(storageKey);
    if (!storedData) return;

    const data = JSON.parse(storedData);
    const date = new Date().toDateString();

    if (date !== data.expires) {
        clearLocalStorage();
        return;
    }
    results = data.results;
    guesses = data.guesses;
    currentRound = data.currentRound;
    answer = data.answer;
    gameStatus = data.gameStatus;
    setCurrentRound();
}
```

Something I discovered here was that I needed to pass information back to the GameLogicService to ensure the client and server were synced. A new route added with some new methods allowed this. Now when refreshing the user's progress is persisted.

Initially I was only passing the current round from client to server on refresh, however, I discovered a further complication. The session storage I am using in the server controller doesn't persist on refresh and re-instantiates the service - essentially a new game. To accommodate this I am having to pass the answer to the client for storage in the client local storage so that if the page is refreshed the answer being compared to that point is consistent. In a production app I would never do this, I would have a DB where the word for that day was stored.

The controller is set up of a get request that does the initial config and styling. A post endpoint to send information if the client finds valid data. A further post endpoint that received and validates the user's guess.

There is a slight delay on the keyboard colour classes applying to mimic Wordle.

There is a button in the header that allows the user to reset the game for the purpose of testing different cases.
