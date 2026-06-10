<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Donation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'password',
        'phone',
        'type',
        'contact_info',
        'profile'
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function donations(): HasMany{
        return $this->hasmany(Donation::class);
    }

     public function inkinds(): HasMany{
        return $this->hasmany(Inkind_donation::class);
    }

    public function getTotalDonationsAttribute()
    {
    $sypRate = ExchangeRate::where(
        'currency',
        'SYP'
    )->value('rate');

    $eurRate = ExchangeRate::where(
        'currency',
        'EUR'
    )->value('rate');

    $total = 0;

    foreach ($this->donations as $donation) {

        // فقط التبرعات المتوافقة
        if ($donation->status !== 'متوافق'||$donation->pending != 1) {
            continue;
        }

        // الدولار
        if ($donation->currency_type === 'USD') {

            $total += $donation->contribution_amount;
        }

        // الليرة السورية
        elseif ($donation->currency_type === 'SYP') {

            if ($sypRate > 0) {

                $total += $donation->contribution_amount / $sypRate;
            }
        }

        // اليورو
        elseif ($donation->currency_type === 'EUR') {

            if ($eurRate > 0) {

                $total +=
                    $donation->contribution_amount / $eurRate;
            }
        }
    }

    return round($total, 2);
    }


    public function getDonationsCountAttribute(){
        return $this->donations()->count();
    }

    public function getAverageDonationsAttribute(){
        $count = $this->donations()->count();

        if ($count == 0) {
             return 0;
        }
        return round( $this->total_donations / $count,2);
    }

    public function getLastDonationAttribute(){
        return $this->donations()->latest()->first();
    }


}
