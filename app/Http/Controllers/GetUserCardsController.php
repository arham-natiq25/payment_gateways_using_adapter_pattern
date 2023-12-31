<?php

namespace App\Http\Controllers;

use App\Models\CustomerProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GetUserCardsController extends Controller
{
    function index() {
        $cards = CustomerProfile::where('user_id',Auth::user()->id)->get();

        return response()->json($cards);
    }
}
