<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Support\Facades\Hash;
use Filament\Panel;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'fcm_token'])]
#[Hidden(['password', 'remember_token'])]

class User extends Authenticatable implements FilamentUser
{

    protected $fillable = [
        'name',
        'email',
        'password',
        'workshop_id',
        'role', // Tambahkan baris ini
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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

    public function canAccessPanel(Panel $panel): bool
    {
        return true; // Mengizinkan user ini masuk ke panel admin Filament
    }
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::needsRehash($value) ? Hash::make($value) : $value;
    }

    public function workshop()
    {
        return $this->belongsTo(Workshop::class);
    }
}


