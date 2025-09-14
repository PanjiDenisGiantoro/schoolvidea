<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Officer;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles;

    /**
     * Get the officer associated with the user.
     */
    public function officers()
    {
        return $this->hasOne(Officer::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
    public function officer()
    {
        return $this->hasOne(Officer::class);
    }

    public function siswa()
    {
        return $this->hasOne(Siswa::class);
    }

    public function scopeHasRole($query, $role)
    {
        return $query->whereHas('role', function ($q) use ($role) {
            $q->where('name', $role);
        });
    }
    public function role()
    {
        return $this->belongsTo(Roles_petugas::class);
    }
    public function userRole()
    {
        return $this->belongsTo(Roles_petugas::class, 'id');
    }





}
