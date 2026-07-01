<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\GovernorateResource;
use App\Http\Traits\GeneralTrait;
use App\Http\Traits\UploadTrait;
use App\Models\Governorate;
use App\Models\Inkind_donation;
use Illuminate\Http\Request;

class Inkind_donationApiController extends Controller
{
    use GeneralTrait , UploadTrait;

    public function get_type(){
    try{

        $type = ['أثاث' ,'أدوات منزلية', 'أجهزة طبية', 'أجهزة إلكترونية', 'ملابس', 'أدوات مدرسية', 'غير ذلك'];

        $onTheOtherHand = Inkind_donation::whereNotNull('on_the_other_hand')->distinct()
        ->pluck('on_the_other_hand')->values();

        $allData = collect($type)->merge($onTheOtherHand)->unique()
        ->reject(fn($item) => $item === 'غير ذلك')->values()->push('غير ذلك');

        return $this->apiResponse($allData);
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }


    public function get_status_of_materail(){
    try{
        $status = ['جديدة','مستعملة'];
        return $this->apiResponse($status);
    } catch (\Exception $ex) {
        return $this->apiResponse(null,false,$ex->getMessage(),400);
    }
    }

    public function getGovernorates(){
    try{
        $governorates = Governorate::all();
        $governorates = GovernorateResource::collection($governorates);
        if( $governorates ){
            return $this->apiResponse( $governorates );
        }else{
            return $this->apiResponse([]);
        }
    } catch (\Exception $ex) {
        return $this->apiResponse(null, false, $ex->getMessage(), 500);
    }
    }



}
