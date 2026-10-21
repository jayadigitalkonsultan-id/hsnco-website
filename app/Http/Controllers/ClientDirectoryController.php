<?php

namespace App\Http\Controllers;

use App\Models\Client;

class ClientDirectoryController extends Controller
{
    public function __invoke()
    {
        $directoryClients = Client::query()->published()->with('publicImage')->orderBy('sort_order')->orderBy('name')->get();

        return view('frontend.clients', compact('directoryClients'));
    }
}
