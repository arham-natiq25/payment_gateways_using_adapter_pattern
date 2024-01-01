<?php

namespace App\Http\Controllers;

use App\Models\CustomerProfile;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GetUserCardsController extends Controller
{
    function index() {
        $setting = Setting::find(1);
        if ($setting===null || $setting->active_gateway===1) {
            $cards = CustomerProfile::where('user_id',Auth::user()->id)->where('method', 'stripe')->get();
        }elseif ($setting->active_gateway===0) {
            $cards = CustomerProfile::where('user_id',Auth::user()->id)->where('method', 'authorizenet')->get();
        }else{
            $cards = CustomerProfile::where('user_id',Auth::user()->id)->where('method', 'stripe')->get();
        }

        return response()->json($cards);
    }
}
