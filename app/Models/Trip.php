<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'departure',
        'destination',
        'departure_date',
        'departure_time',
        'return_date',
        'return_time',
        'price',
        'available_seats',
        'status',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'departure_date' => 'date',
            'return_date' => 'date',
            'departure_time' => 'datetime:H:i',
            'return_time' => 'datetime:H:i',
            'price' => 'decimal:2',
        ];
    }

    //Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeByDateRange(Builder $query, $startDate, $endDate = null): Builder
    {
        $query->where('departure_date', '>=', $startDate);
        if ($endDate) {
            $query->where('departure_date', '<=', $endDate);
        }
        return $query;
    }

    public function scopeByDate(Builder $query, $date): Builder
    {
        return $query->whereDate('departure_date', $date);
    }

    public function scopeSearch(Builder $query, $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('departure', 'like', "%{$search}%")
                ->orWhere('destination', 'like', "%{$search}%");
        });
    }
}
