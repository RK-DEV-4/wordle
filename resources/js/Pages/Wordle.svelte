<script>
    import { onMount, onDestroy } from "svelte";
    import ScreenKeyboard from "@/Pages/ScreenKeyboard.svelte";

    export let wordLength = 5;
    export let maxRounds = 6;
    
    // An array of guesses as string
    let guesses = [];
    // Used to hold status of each letter in a guess - passed to on screen keyboard.
    let results = [];
    let currentRound = 0;
    // User's current guess as empty string 
    let currentGuess = "";
    // Used to determined status of game. 'win', 'loss' are end game status', undefined means the game is ongoing
    let gameStatus = undefined;

    // Status key values to assign classes based on reults from backend
    let statuses = {
        '0': 'correct',
        '?': 'present',
        '_': 'missing',
        undefined: 'blank'
    };

    let errors = {};

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

            addResult(data.result);

            // check if game has ended after each submission
            gameStatus = data.isWin 
                ? 'win'
                : (data.isLoss
                    ? 'loss'
                    : undefined
                )

            currentGuess = '';
            currentRound = data.currentRound;
        } catch (err) {
            if (err.response?.status === 422) {
                errors = err.response.data.errors
            } else {
                console.error(err);
            }
        }
    }

    function addResult(result) {
        let formattedResult = currentGuess.split("").map((letter, i) => ({ letter: letter, value: result[i]}));
        results.push(formattedResult);
        results = results; 
    }

    onMount(() => {
        window.addEventListener("keydown", handleKeyDown);
    });

    onDestroy(() => {
        window.removeEventListener("keydown", handleKeyDown);
    });
</script>

<div class="main_container">
    {#if Object.keys(errors).length > 0}
        <div 
            class="message_container"
            class:hidden={Object.keys(errors).length === 0 || gameStatus}
            class:error={Object.keys(errors).length > 0}
        >
            {#each Object.entries(errors) as [type, messages]}
                {#each messages as message}
                    <p>{message}</p>
                {/each}
            {/each}
        </div>
    {/if}
    <div class="h-12 md:h-14 w-full border-b"></div>
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
        on:keyPress={handleKeyDown}
    />
</div>
