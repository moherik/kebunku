<?php

namespace App\Http\Controllers;

use App\Models\Garden;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GardenController extends Controller
{
    /**
     * Display the specified garden.
     */
    public function show(Garden $garden): Response
    {
        $garden->load(['user:id,name,phone,email,address']);
        
        $plantings = $garden->plantings()
            ->with(['crop'])
            ->orderBy('estimated_harvest_at', 'asc')
            ->get();

        return Inertia::render('Public/GardenShow', [
            'garden' => $garden,
            'plantings' => $plantings,
        ]);
    }
}
