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
        'contact_name', // <--- IMPORTANT: Să fie aici
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
    // 👤 LOGICĂ NUME AUTOR (SMART ACCESSOR)
    // ==========================================
    public function getAuthorNameAttribute()
    {
        // 1. UTILIZATOR ÎNREGISTRAT
        if ($this->user) {
            if (!empty($this->user->name) && $this->user->name !== 'Anonymous' && $this->user->name !== 'Vizitator') {
                return $this->user->name;
            }
            // Fallback la email
            $parts = explode('@', $this->user->email);
            return ucfirst($parts[0]);
        }

        // 2. VIZITATOR (Citim din coloana contact_name)
        // Folosim getAttribute pentru siguranță
        $guestName = $this->getAttribute('contact_name');
        if (!empty($guestName)) {
            return $guestName;
        }

        // 3. Fallback: Email de contact al anunțului
        if (!empty($this->email)) {
            $parts = explode('@', $this->email);
            return ucfirst($parts[0]);
        }

        // 4. Ultimul resort
        return 'Vizitator';
    }

    // Helper pentru inițiala numelui (Avatar)
    public function getAuthorInitialAttribute()
    {
        $name = $this->author_name; // Apelează funcția de mai sus
        return strtoupper(substr($name, 0, 1));
    }

    // ==========================================
    // 🚀 SEO: SMART SLUG
    // ==========================================
    public function getSmartSlugAttribute()
    {
        $cleanTitle = trim(preg_replace('/\s+/', ' ', $this->title));
        $words = explode(' ', $cleanTitle);
        $firstThreeWords = array_slice($words, 0, 3);
        $slugString = implode(' ', $firstThreeWords);
        return Str::slug($slugString);
    }

    // ==========================================
    // 🔗 SEO: PUBLIC URL
    // ==========================================
    public function getPublicUrlAttribute()
    {
        $catSlug = $this->category ? $this->category->slug : 'diverse';
        $countySlug = $this->county ? $this->county->slug : 'romania';

        return route('service.show', [
            'category' => $catSlug,
            'county'   => $countySlug,
            'slug'     => $this->smart_slug,
            'id'       => $this->id
        ]);
    }

    // ==========================================
    // 🖼️ IMAGINI
    // ==========================================
    public function getMainImageUrlAttribute()
    {
        if (!empty($this->images) && is_array($this->images) && isset($this->images[0])) {
            return asset('storage/services/' . $this->images[0]);
        }

        if ($this->category && $this->category->default_image) {
            return asset('images/defaults/' . $this->category->default_image);
        }

        return asset('images/defaults/placeholder.png');
    }
}