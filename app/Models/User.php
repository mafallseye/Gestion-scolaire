<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    public function student()
{
    return $this->hasOne(Student::class);
}

  protected $fillable = [
    'name',
    'email',
    'password',
    'role', // <--- VÉRIFIE QUE CETTE LIGNE EXISTE ICI
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

    // app/Models/User.php

protected static function booted()
{
    static::created(function ($user) {
        // Si on crée un utilisateur qui a le rôle student
        if ($user->role === 'student') {
            $user->student()->create([
                // Tu peux ajouter des valeurs par défaut ici
                'registration_number' => 'MAT-' . now()->format('Y') . '-' . $user->id,
            ]);
        }
    });
}



}
