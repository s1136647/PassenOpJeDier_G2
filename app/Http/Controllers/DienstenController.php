<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Advertisements;
use App\Models\CareRequests;

class DienstenController extends Controller
{
    public function index(Advertisements $advertisements) {
        $userID = auth::user()->id; //ID van de geathenticeerde gebruiker| bv 2
        $available_ads = $advertisements->get()->where('user_id', '!=', $userID);
        // dd($available_ads->where('id', '=', 1));
        // dd($advertisements->get()->where('user_id', '!=', $userID));
        return view('diensten.opdrachten', [
            "advertisements" => $available_ads
        ]);
    }

    public function filter(Request $request, Advertisements $advertisements) {
        $userID = auth()->user()->id;
        $animal = $request->input('animal');
        $price = $request->input('price_range');
        $search = $request->input('search');
        $query = Advertisements::where('user_id', '!=', $userID);
    
        if ($animal && $animal != 'other') {
            $query->where('animal', $animal);
        }
    
        if ($price) {
            $query->where('price', '<=', $price);
        }
    
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'LIKE', '%' . strtolower($search) . '%')
                  ->orWhere('name', 'LIKE', '%' . strtolower($search) . '%')
                  ->orWhere('animal', 'LIKE', '%' . strtolower($search) . '%');
            });
        }
    
        $advertisements = $query->get();
    
        return view('diensten.opdrachten', [
            'advertisements' => $advertisements
        ]);
    }

    public function details(Advertisements $advertisements) {
        // dd($advertisements->img);
        return view('diensten.details', [
            "advertisements" => $advertisements
        ]);
    }

    public function request(Advertisements $advertisements, User $user) {
        $careRequests= new CareRequests;
        $careRequests->care_taker_id = auth::user()->id;
        $careRequests->user_id = $advertisements->user_id;
        $careRequests->advertisement_id = $advertisements->id;
        $careRequests->user_name = $advertisements->user_name;
        $careRequests->care_taker_name = auth::user()->name;
        $careRequests->advertisement_name_animal = $advertisements->name;
        $careRequests->advertisement_animal = $advertisements->animal;
        $careRequests->advertisement_price = $advertisements->price;
        $careRequests->advertisement_date_start = $advertisements->date_start;
        $careRequests->advertisement_date_end = $advertisements->date_end;
        $careRequests->is_accepted;
        $careRequests->save();

        // dd($advertisements->id);
        return redirect('/diensten/opdrachten');
    }

    public function myRequests(CareRequests $careRequests, Advertisements $advertisements) {
        $userID = auth::user()->id;
        // dd($userID);
        return view('diensten.mijn-diensten', [
            "careRequests" => $careRequests->get()->where('care_taker_id', '==', $userID)
        ]);
    }

    public function accept($id) {
        $careRequests = CareRequests::where('id', '=', e($id))->first();
        if ($careRequests) {
            $careRequests->is_accepted = 0;
            $careRequests->save();
            return redirect()->back();
        }
    }

    public function refuse($id) {
        $careRequest = CareRequests::where('id', '=', e($id));
        $careRequest->delete();
        return redirect()->back();
    }
}
