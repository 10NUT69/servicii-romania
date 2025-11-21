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
        'slug', // CRITIC: Fără asta nu merg link-urile
        'description',
        'price_value',
        'price_type',
        'currency',
        'phone',
        'email',
        'images', // CRITIC: Trebuie să fie aici
        'status',
        'views',
        'published_at',
        'expires_at',
    ];

    // 🔥 ACEASTA ESTE LINIA CARE FACE TOTUL SĂ MEARGĂ 🔥
    // Transformă automat JSON-ul din DB în Array PHP și invers.
    protected $casts = [
        'images' => 'array',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'price_value' => 'float',
    ];

    // Relații
    public function category() { return $this->belongsTo(Category::class); }
    public function county() { return $this->belongsTo(County::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function favorites() { return $this->hasMany(Favorite::class); }

    // Helper pentru favorite
    public function isFavoritedBy($user)
    {
        if (!$user) return false;
        return $this->favorites()->where('user_id', $user->id)->exists();
    }
}