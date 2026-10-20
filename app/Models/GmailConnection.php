<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class GmailConnection extends Model {
 protected $table='gmail_mail_connections';
 protected $guarded=['id'];
 protected $hidden=['access_token','refresh_token'];
 protected function casts(): array {return ['access_token'=>'encrypted','refresh_token'=>'encrypted','expires_at'=>'datetime'];}
}