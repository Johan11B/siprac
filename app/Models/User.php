<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
    protected $fillable = [
        'name',
        'telefono',
        'email',
        'password',
        'role_id',
        'activo',
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
            'activo' => 'boolean',
        ];
    }

    // ========================================
    // Relaciones
    // ========================================

    /**
     * Rol global del usuario.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * Fincas que posee este usuario (como propietario/agricultor).
     */
    public function fincas(): HasMany
    {
        return $this->hasMany(Finca::class, 'propietario_id');
    }

    /**
     * Fincas a las que está asociado (como trabajador o técnico).
     * Incluye nivel de permiso y quién lo asignó.
     */
    public function fincasAsociadas(): BelongsToMany
    {
        return $this->belongsToMany(Finca::class, 'finca_usuario', 'usuario_id', 'finca_id')
            ->withPivot(['nivel_permiso', 'asignado_por', 'activo'])
            ->withTimestamps();
    }

    /**
     * Fincas asociadas que están activas.
     */
    public function fincasAsociadasActivas(): BelongsToMany
    {
        return $this->fincasAsociadas()->wherePivot('activo', true);
    }

    // ========================================
    // Helpers de rol
    // ========================================

    /**
     * Verifica si el usuario tiene un rol específico por nombre.
     */
    public function tieneRol(string $nombreRol): bool
    {
        return $this->role->nombre === $nombreRol;
    }

    /**
     * Verifica si el usuario tiene alguno de los roles dados.
     */
    public function tieneAlgunRol(array $roles): bool
    {
        return in_array($this->role->nombre, $roles);
    }

    public function esSolicitante(): bool
    {
        return $this->tieneRol('solicitante');
    }

    public function esAgricultor(): bool
    {
        return $this->tieneRol('agricultor');
    }

    public function esTecnico(): bool
    {
        return $this->tieneRol('tecnico');
    }

    public function esAdmin(): bool
    {
        return $this->tieneRol('administrador');
    }

    /**
     * Obtiene el nombre del rol en formato legible.
     */
    public function getNombreRolAttribute(): string
    {
        return ucfirst($this->role->nombre ?? 'sin rol');
    }

    // ========================================
    // Helpers de permisos por finca
    // ========================================

    /**
     * Verifica si el usuario tiene acceso a una finca específica.
     * Un propietario siempre tiene acceso.
     */
    public function tieneAccesoAFinca(Finca $finca): bool
    {
        // Si es propietario, siempre tiene acceso
        if ($finca->propietario_id === $this->id) {
            return true;
        }

        // Si es admin, tiene acceso a todo
        if ($this->esAdmin()) {
            return true;
        }

        // Si está asociado a la finca y activo
        return $this->fincasAsociadasActivas()
            ->where('fincas.id', $finca->id)
            ->exists();
    }

    /**
     * Obtiene el nivel de permiso del usuario en una finca.
     * Retorna: 1 (ver), 2 (alertas), 3 (control), o null si no tiene acceso.
     * Propietarios y admins retornan 3 (acceso total).
     */
    public function nivelPermisoEnFinca(Finca $finca): ?int
    {
        if ($finca->propietario_id === $this->id || $this->esAdmin()) {
            return 3; // Acceso total
        }

        $asociacion = $this->fincasAsociadasActivas()
            ->where('fincas.id', $finca->id)
            ->first();

        return $asociacion?->pivot->nivel_permiso;
    }
}
