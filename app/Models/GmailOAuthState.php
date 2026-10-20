<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class GmailOAuthState extends Model {
 protected $table='gmail_oauth_states';
 protected $primaryKey='state_hash';
 public $incrementing=false;
 protected $keyType='string';
 protected $guarded=[];
 protected $hidden=['state_hash','code_verifier'];
 protected function casts(): array {return ['code_verifier'=>'encrypted','expires_at'=>'datetime','used_at'=>'datetime'];}
}