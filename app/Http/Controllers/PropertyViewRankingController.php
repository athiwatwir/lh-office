<?php

namespace App\Http\Controllers;

use App\Services\ActiveAgentService;
use Illuminate\View\View;

class PropertyViewRankingController extends Controller
{
    public function index(ActiveAgentService $activeAgent): View
    {
        return view('pages.property-views.index', [
            'title' => 'อันดับยอดวิวทรัพย์สิน',
            'activeAgent' => $activeAgent->agent(),
        ]);
    }
}
