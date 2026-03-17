<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Asset extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'category',
        'purchase_price',
        'current_value',
        'purchase_date',
        'condition',
        'description',
        'notes',
        'image_url',
        'location',
        'warranty_expiry',
        'serial_number',
        'is_insured',
        'insurance_expiry',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'current_value' => 'decimal:2',
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'is_insured' => 'boolean',
        'insurance_expiry' => 'date',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeElectronics($query)
    {
        return $query->where('type', 'electronics');
    }

    public function scopeVehicles($query)
    {
        return $query->where('type', 'vehicle');
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where('location', 'like', "%{$location}%");
    }

    // Methods
    public function getAppreciation()
    {
        return $this->current_value - $this->purchase_price;
    }

    public function getAppreciationPercentage()
    {
        if ($this->purchase_price == 0) {
            return 0;
        }

        $appreciation = $this->getAppreciation();
        return ($appreciation / $this->purchase_price) * 100;
    }

    public function getAge()
    {
        return Carbon::parse($this->purchase_date)->age;
    }

    public function getAgeInDays()
    {
        return Carbon::parse($this->purchase_date)->diffInDays(Carbon::now());
    }

    public function isWarrantyExpiringSoon($days = 30)
    {
        if (!$this->warranty_expiry) {
            return false;
        }

        return Carbon::parse($this->warranty_expiry)->diffInDays(Carbon::now()) <= $days;
    }

    public function isInsuranceExpiringSoon($days = 30)
    {
        if (!$this->is_insured || !$this->insurance_expiry) {
            return false;
        }

        return Carbon::parse($this->insurance_expiry)->diffInDays(Carbon::now()) <= $days;
    }

    public function getDepreciatedValue($depreciationRate = 0.1)
    {
        $ageInYears = $this->getAge();
        $depreciationFactor = pow(1 - $depreciationRate, $ageInYears);

        return $this->purchase_price * $depreciationFactor;
    }

    public function getStatusColor()
    {
        $appreciation = $this->getAppreciation();

        if ($appreciation > 0) {
            return 'text-emerald-600';
        } elseif ($appreciation < 0) {
            return 'text-rose-600';
        } else {
            return 'text-gray-600';
        }
    }

    public function getConditionColor()
    {
        return match ($this->condition) {
            'Excellent' => 'green',
            'Good' => 'blue',
            'Fair' => 'yellow',
            'Poor' => 'red',
            default => 'gray',
        };
    }

    public function getFormattedPurchasePrice()
    {
        return 'Rp ' . number_format($this->purchase_price, 0, ',', '.');
    }

    public function getFormattedCurrentValue()
    {
        return 'Rp ' . number_format($this->current_value, 0, ',', '.');
    }
}
