<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;

class ShamCashService
{
    public function verifyTransaction($tx)
    {
        /*return Http::get('https://apisyria.com/api/v1', [
            'resource' => 'shamcash',
            'action' => 'find_tx',
            'tx' => $tx,
            'account_address' => env('SHAMCASH_ACCOUNT'),
            'api_key' => env('SHAMCASH_API_KEY'),
        ])->json();*/
        return [
        'data' => [
            'found' => true,
            'to' => env('SHAMCASH_ACCOUNT'),
            'amount' => 100000
        ]
    ];
    }
}
