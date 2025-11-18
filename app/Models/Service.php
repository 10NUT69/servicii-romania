<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Favorite;
use Illuminate\Support\Facades\Auth;

class Service extends Model
{
    use HasFactory;

   protected $fillable = [
    'user_id',
    'category_id',
    'county_id',
    'title',
    'description',
    'city',
    'phone',
    'email',
    'images',
    'status',
    'published_at',
    'expires_at',
    'views',

    // 🔥 ADĂUGAT – FĂRĂ ASTEA NU SE SALVEAZĂ PREȚUL
    'price_value',
    'price_type',
    'currency',
];

    protected $casts = [
        'images' => 'array',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // 🔹 Relații existente
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function county()
    {
        return $this->belongsTo(County::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ⭐ PASUL 4 — Relația cu Favorite
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    // ⭐ PASUL 4 — Verifică dacă un user a dat la favorite
    public function isFavoritedBy($user)
    {
        if (!$user) {
            return false;
        }

        return $this->favorites()->where('user_id', $user->id)->exists();
    }
}
