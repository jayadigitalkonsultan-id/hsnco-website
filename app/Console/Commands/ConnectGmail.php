<?php
namespace App\Console\Commands;

use App\Models\GmailConnection;
use App\Services\Mail\GmailOAuth;
use Illuminate\Console\Command;

class ConnectGmail extends Command
{
 protected $signature='hsnco:gmail-connect {--status : Show configuration and connection status without requesting authorization}';
 protected $description='Create a one-time Gmail OAuth authorization URL from the server console';
 public function handle(GmailOAuth $oauth): int
 {
  if($this->option('status')){
   try{$connection=GmailConnection::where('email',mb_strtolower(config('gmail.sender')))->first();}
   catch(\Throwable){$this->error('Tabel Gmail belum tersedia. Jalankan migration terlebih dahulu.');return self::FAILURE;}
   $this->table(['Pemeriksaan','Nilai'],[
    ['Pengirim',config('gmail.sender')],['Client ID terisi',filled(config('gmail.client_id'))?'Ya':'Tidak'],
    ['Client Secret terisi',filled(config('gmail.client_secret'))?'Ya':'Tidak'],
    ['Callback',config('gmail.redirect_uri')?:'-'],['Terhubung',$connection?'Ya':'Belum'],
   ]);
   return self::SUCCESS;
  }
  try{$url=$oauth->authorizationUrl();}catch(\Throwable $e){$this->error($e instanceof \RuntimeException ? $e->getMessage() : 'Konfigurasi Gmail atau tabel database belum siap.');return self::FAILURE;}
  $this->info('Daftarkan URL callback ini pada OAuth Web Client Google:');$this->line(config('gmail.redirect_uri'));
  $this->info('Buka tautan berikut dan masuk sebagai '.config('gmail.sender').'. Tautan berlaku 10 menit, sekali pakai; jangan dibagikan.');
  $this->line($url);
  return self::SUCCESS;
 }
}