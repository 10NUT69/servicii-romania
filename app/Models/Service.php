<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

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
    // 🚀 SEO: SMART SLUG (Fix Primele 3 Cuvinte)
    // ==========================================
    public function getSmartSlugAttribute()
    {
        // 1. Curățăm titlul de spații multiple
        $cleanTitle = trim(preg_replace('/\s+/', ' ', $this->title));

        // 2. Spargem în cuvinte
        $words = explode(' ', $cleanTitle);

        // 3. Luăm FIX primele 3 elemente din array
        // (Nu mai filtrăm nimic, luăm exact ce a scris omul la început)
        // Dacă titlul are mai puțin de 3 cuvinte, le ia pe toate.
        $firstThreeWords = array_slice($words, 0, 3);

        // 4. Le unim la loc
        $slugString = implode(' ', $firstThreeWords);

        // 5. Transformăm în slug (litere mici și cratime)
        return Str::slug($slugString);
    }

    // ==========================================
    // 🔗 SEO: PUBLIC URL (Link-ul perfect)
    // ==========================================
    public function getPublicUrlAttribute()
    {
        // Folosim slug-ul categoriei sau un fallback
        $catSlug = $this->category ? $this->category->slug : 'diverse';
        
        // Folosim slug-ul județului sau un fallback
        $countySlug = $this->county ? $this->county->slug : 'romania';

        // Generăm ruta folosind numele pe care îl vom defini în routes/web.php
        return route('service.show', [
            'category' => $catSlug,
            'county'   => $countySlug,
            'slug'     => $this->smart_slug, // Apelează funcția de mai sus automat
            'id'       => $this->id
        ]);
    }

    // ==========================================
    // 🔥 FIX: CALEA CORECTĂ PENTRU IMAGINI 🔥
    // ==========================================
    public function getMainImageUrlAttribute()
    {
        // 1. Verificăm dacă userul a încărcat imagini
        if (!empty($this->images) && is_array($this->images) && isset($this->images[0])) {
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