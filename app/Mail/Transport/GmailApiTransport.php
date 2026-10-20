<?php
namespace App\Mail\Transport;

use App\Services\Mail\GmailOAuth;
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
   catch(\Throwable){throw new RuntimeException('Koneksi pengiriman Gmail API gagal.');}
   if($response->status()===401&&$attempt===0)continue;
   if(!$response->successful()||!filled($response->json('id'))) throw new RuntimeException('Gmail API menolak pengiriman email (HTTP '.$response->status().').');
   return;
  }
 }
}