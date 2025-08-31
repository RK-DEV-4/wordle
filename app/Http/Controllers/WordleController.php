<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class WordleController extends Controller
{
    public function fetch()
    {
        return Inertia::render('Wordle', [
            'title' => 'Wordle'
        ]);
    }
}
