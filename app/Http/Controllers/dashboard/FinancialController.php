<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;
use App\Http\Resources\ExchangeRateResource;
use App\Http\Traits\GeneralTrait;
use App\Models\ExchangeRate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FinancialController extends Controller
{
    use GeneralTrait;

    public function update(Request $request , $uuid)
    {
        try{
        $currency = ['SYP', 'EUR'];

        $request->validate([
            "currency" => ["required", Rule::in($currency)],
            "rate" => "required|numeric",
        ]);

        $exchange = ExchangeRate::where('uuid', $uuid)->firstOrFail();

        $exchange->update([
            'currency' => $request->currency,
            'rate' => $request->rate
        ]);

        return $this->apiResponse( ExchangeRateResource::make($exchange));
        }catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
        }
    }

    public function index(){
        try{
        $exchanges = ExchangeRate::all();
        $exchanges = ExchangeRateResource::collection($exchanges);
        
        if( $exchanges ){
            return $this->apiResponse( $exchanges );
        }else{
            return $this->apiResponse([]);
        }
        } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
    }
}
