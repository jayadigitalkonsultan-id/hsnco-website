<?php
namespace App\Mail\Transport;

use App\Services\Mail\GmailOAuth;
use App\Services\Mail\GmailDeliveryException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use RuntimeException;

class GmailApiTransport extends AbstractTransport
{
 public function __construct(private GmailOAuth $oauth){parent::__construct();}
 public function __toString(): string{return 'gmail-api';}
 protected function doSend(SentMessage $message): void
 {
  if(mb_strtolower($message->getEnvelope()->getSender()->getAddress())!==mb_strtolower(config('gmail.sender'))) throw new RuntimeException('MAIL_FROM_ADDRESS harus sama dengan GMAIL_SENDER_EMAIL.');
  $raw=rtrim(strtr(base64_encode($message->toString()),'+/','-_'),'=');
  for($attempt=0;$attempt<2;$attempt++){
   $token=$this->oauth->accessToken($attempt===1);
   try{$response=Http::withToken($token)->timeout(20)->post('https://gmail.googleapis.com/gmail/v1/users/me/messages/send',['raw'=>$raw]);}
   catch(\Throwable){throw new GmailDeliveryException('Koneksi pengiriman Gmail API gagal. Periksa DNS, TLS dan akses HTTPS keluar server.');}
   if($response->status()===401&&$attempt===0)continue;
   if(!$response->successful()||!filled($response->json('id'))) {
    $hint = match ($response->status()) {
     401 => 'Otorisasi tidak diterima setelah refresh. Hubungkan ulang Gmail.',
     403 => 'Periksa Gmail API aktif, izin gmail.send, serta pembatasan akun atau project Google.',
     429 => 'Batas pengiriman tercapai. Tunggu sebelum mencoba kembali.',
     default => 'Periksa ketersediaan layanan dan konfigurasi pengirim.',
    };
    throw new GmailDeliveryException('Gmail API HTTP '.$response->status().': '.$hint);
   }
   return;
  }
 }
}