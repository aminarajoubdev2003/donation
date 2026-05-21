<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Traits\GeneralTrait;
use App\Http\Resources\DonaterResource;
use App\Models\Donation;

class IndividualController extends Controller
{
    use GeneralTrait;
    public function Get_Donaters(){
        $donaters = Donation::where( , 'فردي')->get();
        return $this->apiResponse();
    }
}
