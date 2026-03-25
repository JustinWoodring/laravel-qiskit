<?php

namespace App\Http\Controllers;

use JustinWoodring\LaravelQiskit\Facades\Qiskit;

class BackendController extends Controller
{
    public function index()
    {
        try {
            $backends = Qiskit::backends()->all();
        } catch (\Throwable $e) {
            $backends = [];
            session()->flash('error', 'Could not fetch backends: ' . $e->getMessage());
        }

        return view('backends.index', compact('backends'));
    }
}
