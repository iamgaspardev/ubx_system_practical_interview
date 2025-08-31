<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiamondData extends Model
{
    protected $table = 'diamond_data';

    protected $fillable = [
        'upload_id',
        'cut',
        'color',
        'clarity',
        'carat_weight',
        'cut_quality',
        'lab',
        'symmetry',
        'polish',
        'eye_clean',
        'culet_size',
        'culet_condition',
        'depth_percent',
        'table_percent',
        'meas_length',
        'meas_width',
        'meas_depth',
        'girdle_min',
        'girdle_max',
        'fluor_color',
        'fluor_intensity',
        'fancy_color_dominant_color',
        'fancy_color_secondary_color',
        'fancy_color_overtone',
        'fancy_color_intensity',
        'total_sales_price'
    ];

    protected $casts = [
        'carat_weight' => 'decimal:3',
        'depth_percent' => 'decimal:2',
        'table_percent' => 'decimal:2',
        'meas_length' => 'decimal:3',
        'meas_width' => 'decimal:3',
        'meas_depth' => 'decimal:3',
        'total_sales_price' => 'integer'
    ];

    public function upload()
    {
        return $this->belongsTo(Upload::class);
    }

    // Scope for filtering
    public function scopeFilter($query, array $filters)
    {
        return $query->when($filters['cut'] ?? null, function ($query, $cut) {
            $query->where('cut', $cut);
        })->when($filters['color'] ?? null, function ($query, $color) {
            $query->where('color', $color);
        })->when($filters['clarity'] ?? null, function ($query, $clarity) {
            $query->where('clarity', $clarity);
        })->when($filters['min_carat'] ?? null, function ($query, $minCarat) {
            $query->where('carat_weight', '>=', $minCarat);
        })->when($filters['max_carat'] ?? null, function ($query, $maxCarat) {
            $query->where('carat_weight', '<=', $maxCarat);
        })->when($filters['min_price'] ?? null, function ($query, $minPrice) {
            $query->where('total_sales_price', '>=', $minPrice);
        })->when($filters['max_price'] ?? null, function ($query, $maxPrice) {
            $query->where('total_sales_price', '<=', $maxPrice);
        })->when($filters['lab'] ?? null, function ($query, $lab) {
            $query->where('lab', $lab);
        })->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($q) use ($search) {
                $q->where('cut', 'like', "%{$search}%")
                    ->orWhere('color', 'like', "%{$search}%")
                    ->orWhere('clarity', 'like', "%{$search}%")
                    ->orWhere('lab', 'like', "%{$search}%");
            });
        });
    }

    // Format price for display
    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->total_sales_price);
    }
}