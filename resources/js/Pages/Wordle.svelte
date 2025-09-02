<script>
    import { onMount, onDestroy } from "svelte";
    import ScreenKeyboard from "@/Pages/ScreenKeyboard.svelte";
    import { router } from "@inertiajs/svelte";

    export let wordLength = 5;
    export let maxRounds = 6;
    export let answer;
    
    // An array of guesses as string
    let guesses = [];
    // Used to hold status of each letter in a guess - passed to on screen keyboard.
    let results = [];
    let currentRound = 0;
    // User's current guess as empty string 
    let currentGuess = "";
    // Used to determined status of game. 'win', 'loss' are end game status', undefined means the game is ongoing
    let gameStatus = undefined;
    let endMessage = undefined;

    // Status key values to assign classes based on reults from backend
    let statuses = {
        '0': 'correct',
        '?': 'present',
        '_': 'missing',
        undefined: 'blank'
    };

    let errors = {};

    const storageKey = "wordle_storage";

    function handleKeyDown(e) {
        // Ensures CMD+key doesn't enter trigger input
        if (e.metaKey || e.ctrlKey || e.altKey) return;
        // Locks gameplay if game has ended
        if (gameStatus) return;

        // Handles the keyboard and onscreen keyboard inputs
        const key = e?.key?.toUpperCase() ? e?.key?.toUpperCase() : e.detail;

        // only alphabetic characters allowed
        if (/^[A-Z]$/.test(key) && currentGuess.length < wordLength) {
            currentGuess += key;
            guesses[currentRound] = currentGuess;
        } else if (key === "BACKSPACE" || key.includes("<svg")) {
            currentGuess = currentGuess.slice(0, -1);
            guesses[currentRound] = currentGuess;
        } else if (key === "ENTER" && currentGuess.length === wordLength) {
            // Submit guess when word is correct length
            submit();
        }
    }

    async function submit() {
        try {
            const { data } = await axios.post('/submit', {guess: currentGuess});

            currentRound = data.currentRound;
            addResult(data.result);

            // check if game has ended after each submission
            gameStatus = data.isWin 
                ? 'win'
                : (data.isLoss
                    ? 'loss'
                    : undefined
                );
            
            endMessage = gameStatus ? data.endMessage : null

            currentGuess = '';
        } catch (err) {
            if (err.response?.status === 422) {
                errors = err.response.data.errors;
                setTimeout(() => {
                    errors = {}
                }, 3000)
            } else {
                console.error(err);
            }
        }
    }

    function addResult(result) {
        let formattedResult = currentGuess.split("").map((letter, i) => ({ letter: letter, value: result[i]}));
        results.push(formattedResult);
        results = results; 
        setLocalStorage();
    }

    function setLocalStorage() {
        // storing object with date to determine if it is valid
        const storageObject = {
            guesses: guesses,
            results: results,
            currentRound: currentRound,
            answer: answer,
            gameStatus: gameStatus,
            expires: new Date().toDateString()
        };
        localStorage.setItem(storageKey, JSON.stringify(storageObject));
    }

    function getLocalStorage() {
        const storedData = localStorage.getItem(storageKey);
        if (!storedData) return;

        const data = JSON.parse(storedData);
        const date = new Date().toDateString();

        // clearing storage if stored date is not today 
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

    async function setCurrentRound() {
        try {
            const { data } = await axios.post('/set-current-round', {guess: guesses[guesses.length - 1], currentRound: currentRound, answer: answer});

            // check if game has ended after each submission
            gameStatus = data.isWin 
                ? 'win'
                : (data.isLoss
                    ? 'loss'
                    : undefined
                );
            
            endMessage = gameStatus ? data.endMessage : null

            currentGuess = '';
        } catch (err) {
            if (err.response?.status === 422) {
                errors = err.response.data.errors;
                setTimeout(() => {
                    errors = {}
                }, 3000)
            } else {
                console.error(err);
            }
        }
    }

    function clearLocalStorage() {
        localStorage.removeItem(storageKey);
    }

    function resetGame() {
        clearLocalStorage();
        router.visit('/');
    }

    onMount(() => {
        getLocalStorage();
        window.addEventListener("keydown", handleKeyDown);
    });

    onDestroy(() => {
        window.removeEventListener("keydown", handleKeyDown);
    });
</script>

<div class="main_container">
    <div 
        class="message_container"
        class:!opacity-100={Object.keys(errors).length > 0 || endMessage}
        class:error={Object.keys(errors).length > 0}
    >
        {#if Object.keys(errors).length > 0}
            {#each Object.entries(errors) as [type, messages]}
                {#each messages as message}
                    <p>{message}</p>
                {/each}
            {/each}
        {/if}
        {#if endMessage}
            <p>{endMessage}</p>
        {/if}
    </div>
    <div class="h-12 md:h-14 w-full border-b">
        <div class="reset_button">
            <button 
                class="size-6 hover:opacity-80"
                on:click={resetGame}
                aria-label="reset game"
            >
                <svg fill="currentColor" class="h-full w-full" height="800px" width="800px" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 324.876 324.876">
                    <path d="M56.938,182.438V78.222c0-5.212,4.906-9.784,10.499-9.784h154.501v16.883c0,6.132,3.768,10.416,9.161,10.416 c2.066,0,4.157-0.649,6.212-1.93l59.314-36.936c3.507-2.185,5.52-5.471,5.52-9.016c0-3.537-2.003-6.813-5.497-8.99L237.312,1.93 C235.256,0.649,233.166,0,231.1,0c-5.395,0-9.162,4.283-9.162,10.415v18.023H67.437c-27.845,0-50.499,22.333-50.499,49.784v104.216 c0,11.046,8.954,20,20,20S56.938,193.484,56.938,182.438z"/>
                    <path d="M287.938,122.438c-11.046,0-20,8.954-20,20v104.216c0,5.304-4.693,9.784-10.248,9.784H102.938v-16.883 c0-7.156-4.699-10.416-9.064-10.416c-2.048,0-4.129,0.65-6.185,1.931l-59.283,36.936c-3.498,2.178-5.503,5.458-5.503,8.997 c0,3.542,2.009,6.826,5.511,9.008l59.275,36.936c2.056,1.281,4.136,1.93,6.184,1.93c4.366,0,9.064-3.259,9.064-10.415v-18.023 H257.69c27.707,0,50.248-22.333,50.248-49.784V142.438C307.938,131.392,298.984,122.438,287.938,122.438z"/>
                </svg>
            </button>
        </div>
    </div>
    <div class="guess_board">
        <div class={`grid grid-rows-${maxRounds} gap-1.5`}>
            {#each Array(maxRounds) as row, i}
                <div class="grid grid-cols-{wordLength} guess_row gap-1.5 row_{i}">
                    {#each Array(wordLength) as col, j}
                        <div class={`guess_tiles status_${statuses[results?.[i]?.[j]?.value]}`} id="{i}-{j}">
                            {guesses?.[i]?.[j] || ""}
                        </div>
                    {/each}
                </div>
            {/each}
        </div>
    </div>
    <ScreenKeyboard 
        keyStates={results}
        {statuses}
        on:keyPress={handleKeyDown}
    />
</div>
