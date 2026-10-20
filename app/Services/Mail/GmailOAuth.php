<?php
namespace App\Services\Mail;

use App\Models\GmailConnection;
use App\Models\GmailOAuthState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class GmailOAuth
{
 public const SEND_SCOPE='https://www.googleapis.com/auth/gmail.send';

 public function assertConfigured(): void
 {
  if (!filled(config('gmail.client_id')) || !filled(config('gmail.client_secret'))) throw new RuntimeException('Isi GMAIL_CLIENT_ID dan GMAIL_CLIENT_SECRET pengganti terlebih dahulu.');
  if (!filter_var(config('gmail.sender'),FILTER_VALIDATE_EMAIL)) throw new RuntimeException('GMAIL_SENDER_EMAIL tidak valid.');
  $uri=(string)config('gmail.redirect_uri');$parts=parse_url($uri);
  $secure=($parts['scheme']??'')==='https';
  $local=app()->environment('local','testing')&&($parts['scheme']??'')==='http'&&in_array($parts['host']??'', ['127.0.0.1','localhost','::1'],true);
  if (!filter_var($uri,FILTER_VALIDATE_URL)||(!$secure&&!$local)||isset($parts['user'])||isset($parts['pass'])||isset($parts['query'])||isset($parts['fragment'])||($parts['path']??'')!=='/auth/google/gmail/callback') throw new RuntimeException('GMAIL_REDIRECT_URI harus memakai URL callback yang benar; production wajib HTTPS.');
 }

 public function authorizationUrl(): string
 {
  $this->assertConfigured();
  $state=Str::random(64);$verifier=Str::random(96);
  GmailOAuthState::where('expires_at','<',now())->delete();
  GmailOAuthState::create(['state_hash'=>hash('sha256',$state),'expected_email'=>mb_strtolower(config('gmail.sender')),'code_verifier'=>$verifier,'redirect_uri'=>config('gmail.redirect_uri'),'expires_at'=>now()->addMinutes(config('gmail.state_minutes'))]);
  return 'https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query([
   'client_id'=>config('gmail.client_id'),'redirect_uri'=>config('gmail.redirect_uri'),'response_type'=>'code',
   'scope'=>'openid email '.self::SEND_SCOPE,'access_type'=>'offline','prompt'=>'consent',
   'login_hint'=>config('gmail.sender'),'state'=>$state,
   'code_challenge'=>rtrim(strtr(base64_encode(hash('sha256',$verifier,true)),'+/','-_'),'='),'code_challenge_method'=>'S256',
  ],'', '&',PHP_QUERY_RFC3986);
 }

 public function complete(string $state, string $code): void
 {
  $this->assertConfigured();
  $pending=DB::transaction(function()use($state){
   $row=GmailOAuthState::whereKey(hash('sha256',$state))->lockForUpdate()->first();
   if(!$row||$row->used_at||$row->expires_at->isPast()||$row->expected_email!==mb_strtolower(config('gmail.sender'))) throw new RuntimeException('Tautan koneksi tidak berlaku. Buat ulang melalui console aplikasi.');
   $row->update(['used_at'=>now()]);return $row;
  });
  $tokens=$this->tokens(['grant_type'=>'authorization_code','code'=>$code,'redirect_uri'=>$pending->redirect_uri,'code_verifier'=>$pending->code_verifier]);
  if(!in_array(self::SEND_SCOPE,explode(' ',$tokens['scope']??''),true)) throw new RuntimeException('Izin pengiriman Gmail belum diberikan.');
  try{$response=Http::withToken($tokens['access_token'])->timeout(15)->get('https://openidconnect.googleapis.com/v1/userinfo');}
  catch(\Throwable){throw new RuntimeException('Tidak dapat memverifikasi identitas akun Google.');}
  $profile=$response->json();
  if(!$response->successful()||($profile['email_verified']??false)!==true||mb_strtolower($profile['email']??'')!==$pending->expected_email) throw new RuntimeException('Akun Google tidak sesuai dengan email pengirim HSNCO.');
  $connection=GmailConnection::firstOrNew(['email'=>$pending->expected_email]);
  $refresh=$tokens['refresh_token']??$connection->refresh_token;
  if(!filled($refresh)) throw new RuntimeException('Refresh token belum tersedia. Ulangi persetujuan dengan akses offline.');
  $connection->fill(['access_token'=>$tokens['access_token'],'refresh_token'=>$refresh,'expires_at'=>now()->addSeconds(max(1,(int)($tokens['expires_in']??3600)))])->save();
 }

 private function tokens(array $data): array
 {
  try{$response=Http::asForm()->timeout(15)->post('https://oauth2.googleapis.com/token',$data+['client_id'=>config('gmail.client_id'),'client_secret'=>config('gmail.client_secret')]);}
  catch(\Throwable){throw new RuntimeException('Tidak dapat menghubungi layanan token Google.');}
  $tokens=$response->json();
  if(!$response->successful()||!is_array($tokens)||!filled($tokens['access_token']??null)) throw new RuntimeException('Otorisasi Google gagal atau dicabut. Hubungkan ulang Gmail melalui console.');
  return $tokens;
 }

 public function accessToken(bool $force=false): string
 {
  $this->assertConfigured();
  return DB::transaction(function()use($force){
   $connection=GmailConnection::where('email',mb_strtolower(config('gmail.sender')))->lockForUpdate()->first();
   if(!$connection) throw new RuntimeException('Gmail belum dihubungkan. Jalankan php artisan hsnco:gmail-connect.');
   if($force||$connection->expires_at->lte(now()->addMinute())){
    $tokens=$this->tokens(['grant_type'=>'refresh_token','refresh_token'=>$connection->refresh_token]);
    $connection->fill(['access_token'=>$tokens['access_token'],'refresh_token'=>$tokens['refresh_token']??$connection->refresh_token,'expires_at'=>now()->addSeconds(max(1,(int)($tokens['expires_in']??3600)))])->save();
   }
   return $connection->access_token;
  });
 }
}