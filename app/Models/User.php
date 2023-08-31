<?php

namespace App\Models;

use App\Enums\Role;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser
{
    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
        'permissions',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'permissions' => 'array'
    ];

    public function hasRole(string $slug): bool
    {
        return Role::from($this->role_id)->name == $slug;
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }
}
