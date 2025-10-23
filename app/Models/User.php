<?php

namespace App\Models;

use App\Notifications\UserCreatedNotification;
use Devaslanphp\FilamentAvatar\Core\HasAvatarUrl;
use DutchCodingCompany\FilamentSocialite\Models\SocialiteUser;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use JeffGreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use ProtoneMedia\LaravelVerifyNewEmail\MustVerifyNewEmail;
use Ramsey\Uuid\Uuid;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail, FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable,
        HasRoles, HasAvatarUrl, SoftDeletes, MustVerifyNewEmail;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'nombres',
        'apellidos',
        'email',
        'celular',
        'password',
        'is_active',
        'creation_token',
        'type',
        'oidc_username',
        'oidc_sub',
        'email_verified_at',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'remember_token',
        'is_vendedor',
        'vendedor_id',
        'fecha_ingreso',
        'fecha_egreso',
        'comision',
        'perfil',
        'id_oficina',
        'id_ruta' // Mantener para compatibilidad
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
        'is_vendedor' => 'boolean',
        'fecha_ingreso' => 'datetime',
        'fecha_egreso' => 'datetime',
        'comision' => 'decimal:2',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function (User $item) {
            if ($item->type == 'db') {
                $item->password = bcrypt(uniqid());
                $item->creation_token = Uuid::uuid4()->toString();
            }
            $item->email_verified_at = now();
        });

        static::created(function (User $item) {
            if ($item->type == 'db') {
                $item->notify(new UserCreatedNotification($item));
            }
        });
    }

    /**
     * Relación muchos-a-muchos con rutas (NUEVA)
     */
    public function rutas()
    {
        return $this->belongsToMany(Ruta::class, 'usuario_ruta', 'user_id', 'id_ruta')
                   ->select('ruta.*')
                   ->withPivot('es_principal')
                   ->withTimestamps();
    }

    /**
     * Relación con clientes creados por este usuario (NUEVA)
     */
    public function clientesCreados()
    {
        return $this->hasMany(Clientes::class, 'id_usuario_creador');
    }

    /**
     * Relación con la oficina asignada
     */
    public function oficina()
    {
        return $this->belongsTo(Oficina::class, 'id_oficina');
    }

    /**
     * Relación con rutas revisables (para supervisores)
     */
    public function rutasRevisables()
    {
        return $this->belongsToMany(Ruta::class, 'revisador_ruta', 'user_id', 'id_ruta')
                   ->withPivot(['permisos']);
    }

    /**
     * Relación con redes sociales (OAuth)
     */
    public function socials()
    {
        return $this->hasMany(SocialiteUser::class, 'user_id', 'id');
    }

    /**
     * Accesor para verificar si es vendedor
     */
    public function isVendedor(): Attribute
    {
        return new Attribute(
            get: fn () => $this->is_vendedor || $this->vendedor_id !== null
        );
    }

    /**
     * Scope para filtrar usuarios vendedores
     */
    public function scopeVendedores($query)
    {
        return $query->where('is_vendedor', true)
                    ->orWhereNotNull('vendedor_id');
    }

    /**
     * Accesor para horas logueadas (ejemplo)
     */
    public function totalLoggedInHours(): Attribute
    {
        return new Attribute(
            get: function () {
                return $this->hours->sum('value');
            }
        );
    }

    /**
     * Verificar si es cobrador de una ruta específica (ACTUALIZADA)
     */
    public function esCobradorDeRuta($rutaId)
    {
        return $this->rutas()->where('ruta.id_ruta', $rutaId)->exists();
    }

    /**
     * Obtener la ruta principal (para compatibilidad)
     */
    public function getRutaPrincipalAttribute()
    {
        return $this->rutas()->wherePivot('es_principal', true)->first();
    }

    /**
     * Acceso a Filament
     */
    public function canAccessFilament(): bool
    {
        return true;
    }

    public function ruta()
    {
        return $this->belongsToMany(Ruta::class, 'usuario_ruta', 'user_id', 'id_ruta');
    }

}