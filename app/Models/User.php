<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

	protected $fillable = ['first_name', 'last_name', 'email', 'password', 'phone', 'role_id', 'franchise_id', 'email_varified_token'];

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

	public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function franchise()
    {
        return $this->belongsTo(Franchise::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }

    public function courses()
    {
        return $this->hasMany(Course::class, 'teacher_id', 'id');
    }

        /**
     * Relationship with course purchases (as a parent or purchaser).
     */
    public function coursePurchases()
    {
        return $this->hasMany(CoursePurchase::class, 'parent_id', 'id');
    }


}
