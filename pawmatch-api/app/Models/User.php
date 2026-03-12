<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    // Instancia con atributos
    protected $fillable = [
        'nombre',
        'email',
        'password',
        'telefono',
        'direccion',
        'rol',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relaciones

    public function isAdmin(): bool
    {
        return $this->rol === 'ADMINISTRADOR';
    }

    public function isUsuario(): bool
    {
        return $this->rol === 'USUARIO';
    }

    public function solicitudes()
    {
        return $this->hasMany(SolicitudAdopcion::class);
    }
}
