<?php
namespace App\Services;
use App\Models\ExchangeRate;


class CurrencyConverterService
{
    public function convertToUsd(float $amount,string $currency) {

        if ($currency === 'USD') {
            return $amount;
        }

        $sypRate = ExchangeRate::where('currency','SYP')->value('rate');
        $eurRate = ExchangeRate::where('currency','EUR')->value('rate');

        if($currency === 'SYP'){
            $sypToUsd = $sypRate > 0? $amount / $sypRate: 0;
            return round($sypToUsd,2);
        }
        if($currency === 'EUR'){
            $eurToUsd = $eurRate > 0? $amount / $eurRate: 0;
            return round($eurToUsd,2);
        }

    }
}
