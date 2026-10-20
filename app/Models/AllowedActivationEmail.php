<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AllowedActivationEmail extends Model
{
    protected $table = 'activation_email_allowlist';
    protected $primaryKey = 'email';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['email', 'role', 'enabled'];
    protected function casts(): array { return ['enabled' => 'boolean']; }
    public function setEmailAttribute(string $value): void { $this->attributes['email'] = mb_strtolower(trim($value)); }

    public static function approved(string $email): ?self
    {
        return static::query()->whereKey(mb_strtolower(trim($email)))->where('enabled', true)->whereIn('role', ['admin', 'editor'])->first();
    }
}