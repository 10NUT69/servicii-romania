<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
        'county_id',
        'title',
        'slug',
        'description',
        'price_value',
        'price_type',
        'currency',
        'phone',
        'email',
        'images',
        'status',
        'views',
        'published_at',
        'expires_at',
    ];

    protected $casts = [
        'images' => 'array',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'price_value' => 'float',
    ];

    public function category() { return $this->belongsTo(Category::class); }
    public function county() { return $this->belongsTo(County::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function favorites() { return $this->hasMany(Favorite::class); }

    public function isFavoritedBy($user)
    {
        if (!$user) return false;
        return $this->favorites()->where('user_id', $user->id)->exists();
    }

    // ==========================================
    // 🔥 FIX: CALEA CORECTĂ PENTRU IMAGINI 🔥
    // ==========================================
    public function getMainImageUrlAttribute()
    {
        // 1. Verificăm dacă userul a încărcat imagini
        if (!empty($this->images) && is_array($this->images) && isset($this->images[0])) {
            // AICI ERA PROBLEMA: Trebuie să includem folderul 'services'
            return asset('storage/services/' . $this->images[0]);
        }

        // 2. Dacă nu, verificăm dacă Categoria are o poză default
        if ($this->category && $this->category->default_image) {
            return asset('images/defaults/' . $this->category->default_image);
        }

        // 3. Fallback final
        return asset('images/defaults/placeholder.png');
    }
}