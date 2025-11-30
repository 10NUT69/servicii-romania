<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str; // <--- AM ADĂUGAT ASTA PENTRU SLUG

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
    // 🚀 SEO: SMART SLUG (Fără cuvinte inutile)
    // ==========================================
    public function getSmartSlugAttribute()
    {
        // Lista de cuvinte de ignorat (Stopwords) pentru limba română
        $stopWords = [
            'de', 'la', 'si', 'in', 'cu', 'o', 'un', 'ofera', 'pentru', 'pt', 
            'fara', 'cel', 'cea', 'care', 'vand', 'execut', 'rog', 'seriozitate',
            'prestez', 'servicii', 'ieftin', 'rapid', 'urgenta', 'non-stop', 'ofer'
        ];

        // Facem titlul mic
        $title = Str::lower($this->title);

        // Spargem în cuvinte
        $words = explode(' ', $title);

        // Filtrăm cuvintele
        $filteredWords = array_filter($words, function($word) use ($stopWords) {
            // Păstrăm cuvântul doar dacă NU e în lista neagră și are mai mult de 2 litere
            return !in_array($word, $stopWords) && strlen($word) > 2;
        });

        // Reconstruim titlul
        $cleanTitle = implode(' ', $filteredWords);

        // Fallback: Dacă am șters tot din greșeală, revenim la titlul original
        if (empty(trim($cleanTitle))) {
            return Str::slug($this->title);
        }

        return Str::slug($cleanTitle);
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