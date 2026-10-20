<?php
namespace Tests\Feature;

use App\Models\GmailConnection;
use App\Models\GmailOAuthState;
use App\Services\Mail\GmailOAuth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class GmailOAuthTest extends TestCase
{
 use RefreshDatabase;
 protected function setUp(): void {
  parent::setUp();Http::preventStrayRequests();
  config(['gmail.client_id'=>'test-client','gmail.client_secret'=>'test-secret','gmail.sender'=>'hsandcoattorney@gmail.com','gmail.redirect_uri'=>'http://127.0.0.1:8000/auth/google/gmail/callback','mail.from.address'=>'hsandcoattorney@gmail.com','mail.from.name'=>'HSNCO']);
 }
 private function authorization(): array {
  $url=app(GmailOAuth::class)->authorizationUrl();parse_str(parse_url($url,PHP_URL_QUERY),$query);return $query;
 }
 private function tokenResponse(): array {return ['access_token'=>'FAKE_ACCESS','refresh_token'=>'FAKE_REFRESH','expires_in'=>3600,'scope'=>'openid email '.GmailOAuth::SEND_SCOPE];}
 private function fakeConsent(string $email='hsandcoattorney@gmail.com'): void {
  Http::fake(['oauth2.googleapis.com/token'=>Http::response($this->tokenResponse()),'openidconnect.googleapis.com/v1/userinfo'=>Http::response(['email'=>$email,'email_verified'=>true])]);
 }
 private function connection(bool $expired=false): GmailConnection {
  return GmailConnection::create(['email'=>'hsandcoattorney@gmail.com','access_token'=>'OLD_ACCESS','refresh_token'=>'OLD_REFRESH','expires_at'=>$expired?now()->subMinute():now()->addHour()]);
 }
 public function test_authorization_uses_offline_pkce_and_encrypted_one_time_state(): void {
  $query=$this->authorization();$row=GmailOAuthState::firstOrFail();
  $this->assertSame('offline',$query['access_type']);$this->assertSame('S256',$query['code_challenge_method']);$this->assertStringContainsString(GmailOAuth::SEND_SCOPE,$query['scope']);
  $this->assertSame(hash('sha256',$query['state']),$row->state_hash);
  $this->assertNotSame($row->code_verifier,DB::table('gmail_oauth_states')->value('code_verifier'));
  $this->assertSame(rtrim(strtr(base64_encode(hash('sha256',$row->code_verifier,true)),'+/','-_'),'='),$query['code_challenge']);
 }
 public function test_callback_stores_encrypted_tokens_without_logging_into_admin_and_replay_fails(): void {
  $query=$this->authorization();$this->fakeConsent();
  $response=$this->get(route('gmail.callback',['state'=>$query['state'],'code'=>'test-code']));
  $response->assertOk()->assertSee('Gmail terhubung')->assertHeader('Referrer-Policy','no-referrer')->assertDontSee('FAKE_REFRESH')->assertDontSee('test-secret');
  $connection=GmailConnection::firstOrFail();$this->assertSame('FAKE_REFRESH',$connection->refresh_token);
  $this->assertNotSame('FAKE_REFRESH',DB::table('gmail_mail_connections')->value('refresh_token'));
  $this->assertArrayNotHasKey('refresh_token',$connection->toArray());$this->assertGuest();
  $this->get(route('gmail.callback',['state'=>$query['state'],'code'=>'test-code']))->assertStatus(400);Http::assertSentCount(2);
 }
 public function test_wrong_sender_cannot_replace_the_connection(): void {
  $query=$this->authorization();$this->fakeConsent('other@example.test');
  $this->get(route('gmail.callback',['state'=>$query['state'],'code'=>'test-code']))->assertStatus(400);$this->assertSame(0,GmailConnection::count());
 }
 public function test_missing_or_expired_state_cannot_call_google(): void {
  $this->get(route('gmail.callback',['state'=>str_repeat('A',64),'code'=>'test-code']))->assertStatus(400);
  $query=$this->authorization();$this->travel(11)->minutes();
  $this->get(route('gmail.callback',['state'=>$query['state'],'code'=>'test-code']))->assertStatus(400);Http::assertNothingSent();
 }
 public function test_missing_send_scope_does_not_create_connection(): void {
  $query=$this->authorization();Http::fake(['oauth2.googleapis.com/token'=>Http::response(array_replace($this->tokenResponse(),['scope'=>'openid email']))]);
  $this->get(route('gmail.callback',['state'=>$query['state'],'code'=>'test-code']))->assertStatus(400);$this->assertSame(0,GmailConnection::count());
 }
 public function test_mail_transport_refreshes_expired_token_and_sends_real_mime_payload(): void {
  $this->connection(true);
  Http::fake(['oauth2.googleapis.com/token'=>Http::response(['access_token'=>'NEW_ACCESS','expires_in'=>3600]),'gmail.googleapis.com/gmail/v1/users/me/messages/send'=>Http::response(['id'=>'message-1'])]);
  Mail::mailer('gmail')->raw('Activation transport test',fn($message)=>$message->to('hermaliaziah22@gmail.com')->subject('HSNCO transport test'));
  Http::assertSent(fn($request)=>$request->url()==='https://oauth2.googleapis.com/token'&&$request['grant_type']==='refresh_token'&&$request['refresh_token']==='OLD_REFRESH');
  Http::assertSent(function($request){if(!str_contains($request->url(),'messages/send'))return false;$raw=base64_decode(strtr($request['raw'],'-_','+/'));return $request->hasHeader('Authorization','Bearer NEW_ACCESS')&&str_contains($raw,'hermaliaziah22@gmail.com')&&str_contains($raw,'Activation transport test');});
  $this->assertSame('NEW_ACCESS',GmailConnection::first()->access_token);$this->assertSame('OLD_REFRESH',GmailConnection::first()->refresh_token);
 }
 public function test_401_refreshes_once_before_retry_without_repeating_other_errors(): void {
  $this->connection();Http::fake(['oauth2.googleapis.com/token'=>Http::response(['access_token'=>'NEW_ACCESS','expires_in'=>3600]),'gmail.googleapis.com/gmail/v1/users/me/messages/send'=>Http::sequence()->push([],401)->push(['id'=>'message-2'])]);
  Mail::mailer('gmail')->raw('Retry test',fn($message)=>$message->to('hsandcoattorney@gmail.com')->subject('Retry test'));Http::assertSentCount(3);
 }
 public function test_different_from_address_is_rejected_before_google_request(): void {
  $this->connection();config(['mail.from.address'=>'other@example.test']);
  try{Mail::mailer('gmail')->raw('Test',fn($message)=>$message->to('hsandcoattorney@gmail.com'));$this->fail('Sender mismatch accepted');}catch(\RuntimeException $e){$this->assertStringContainsString('MAIL_FROM_ADDRESS',$e->getMessage());}
  Http::assertNothingSent();
 }
 public function test_missing_secret_produces_no_authorization_state(): void {
  config(['gmail.client_secret'=>null]);$this->artisan('hsnco:gmail-connect')->assertFailed();$this->assertSame(0,GmailOAuthState::count());
 }
 public function test_diagnostic_does_not_send_mail_without_recipient(): void {
  $this->connection();config(['mail.default'=>'gmail']);$this->artisan('hsnco:mail-test')->assertSuccessful();Http::assertNothingSent();
 }
}