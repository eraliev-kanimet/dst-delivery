<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements FilamentUser
{
    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
        'stores_permission',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'stores_permission' => 'array'
    ];

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole(string $slug): bool
    {
        return $this->role->slug == $slug;
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    public function canAccessFilament(): bool
    {
        return true;
    }
}
