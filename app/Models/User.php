<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'school_id', 'status', 'profile_photo'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * The school this user belongs to.
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

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

    // public function drivers()
    // {
    //     return $this->hasMany(Driver::class, 'created_by');
    // }

    public function driver()
    {
        return $this->hasOne(Driver::class);
    }

    public function parent()
    {
        return $this->hasOne(ParentProfile::class);
    }

    /**
     * Get the dashboard route for this user's role.
     */
    public function dashboardRoute(): ?string
    {
        if ($this->hasRole('Super Admin')) {
            return route('dashboard', absolute: false);
        }

        if ($this->hasRole('School Admin')) {
            return route('principal.dashboard', absolute: false);
        }

        if ($this->hasRole('Driver')) {
            return route('driver.dashboard', absolute: false);
        }

        if ($this->hasRole('Parent')) {
            return route('parent.dashboard', absolute: false);
        }

        return null;
    }
}
