<?php
namespace Tests\Feature;

use App\Enums\UserStatus;
use App\Mail\AccountInvitationMail;
use App\Mail\EmailOtpMail;
use App\Models\AllowedActivationEmail;
use App\Models\User;
use App\Services\InvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ActivationAllowlistTest extends TestCase
{
 use RefreshDatabase;

 public function test_migration_creates_exactly_the_requested_three_roles(): void
 {
  $this->assertSame(3, AllowedActivationEmail::count());
  foreach(['arvinz24wicaksono@gmail.com'=>'admin','hsandcoattorney@gmail.com'=>'admin','hermaliaziah22@gmail.com'=>'editor'] as $email=>$role) $this->assertDatabaseHas('activation_email_allowlist',['email'=>$email,'role'=>$role,'enabled'=>1]);
 }

 public function test_only_approved_email_receives_activation_and_casing_is_normalized(): void
 {
  Mail::fake();
  $this->post(route('activation.send'),['email'=>'unknown@example.test'])->assertRedirect()->assertSessionHas('status');
  Mail::assertNothingSent();$this->assertSame(0,User::count());
  $this->post(route('activation.send'),['email'=>' Arvinz24Wicaksono@gmail.com '])->assertRedirect();
  Mail::assertSent(AccountInvitationMail::class,fn($mail)=>$mail->hasTo('arvinz24wicaksono@gmail.com'));
  $this->assertDatabaseHas('users',['email'=>'arvinz24wicaksono@gmail.com','role'=>'admin','status'=>'invited']);
 }

 public function test_editor_sets_password_then_logs_in_before_receiving_otp(): void
 {
  Mail::fake();$email='hermaliaziah22@gmail.com';
  $this->post(route('activation.send'),['email'=>$email]);
  $mail=Mail::sent(AccountInvitationMail::class)->first();$token=$mail->token;
  $activation = $this->post(route('activation.update',$token),['email'=>$email,'name'=>'Editor','password'=>'Password!12345','password_confirmation'=>'Password!12345','role'=>'super_admin'])->assertRedirect(route('login'));
  $this->assertGuest();
  Mail::assertNotSent(EmailOtpMail::class);
  $this->withCookie(config('session.cookie'), $activation->getCookie(config('session.cookie'))->getValue());
  $login = $this->post(route('login.store'), ['email'=>$email,'password'=>'Password!12345'])->assertRedirect(route('two-factor.challenge'));
  $this->withCookie(config('session.cookie'), $login->getCookie(config('session.cookie'))->getValue());
  $user=User::where('email',$email)->firstOrFail();$this->assertSame('editor',$user->role->value);$this->assertSame(UserStatus::PendingTwoFactor,$user->status);
  Mail::assertSent(EmailOtpMail::class,fn($mail)=>$mail->hasTo($email));
  $otp=Mail::sent(EmailOtpMail::class)->first()->code;
  $this->post(route('two-factor.verify'),['code'=>$otp])->assertRedirect(route('two-factor.recovery-codes'));
  $this->assertSame(UserStatus::Active,$user->fresh()->status);
 }

 public function test_password_form_is_bound_to_recipient_and_rejects_another_approved_email(): void
 {
  Mail::fake();$email='hsandcoattorney@gmail.com';
  $this->post(route('activation.send'),['email'=>$email]);
  $mail=Mail::sent(AccountInvitationMail::class)->first();$token=$mail->token;
  $this->assertTrue($mail->hasTo($email));
  $this->get(route('activation.edit',$token))->assertOk()->assertSee('value="'.$email.'"',false)->assertSee('readonly',false);
  $this->post(route('activation.update',$token),['email'=>'hermaliaziah22@gmail.com','name'=>'Other','password'=>'Password!12345','password_confirmation'=>'Password!12345'])->assertRedirect(route('activation.create'));
  $this->assertGuest();
  $this->assertDatabaseHas('users',['email'=>$email,'status'=>'invited','password'=>null]);
  Mail::assertNotSent(EmailOtpMail::class);
 }
 public function test_disabled_allowlist_entry_invalidates_issued_activation_token(): void
 {
  Mail::fake();$email='hsandcoattorney@gmail.com';app(InvitationService::class)->sendForEmail($email);
  $token=Mail::sent(AccountInvitationMail::class)->first()->token;
  AllowedActivationEmail::whereKey($email)->update(['enabled'=>false]);
  $this->get(route('activation.edit',$token))->assertNotFound();
  $this->assertNull(app(InvitationService::class)->consume($token,$email,'Admin','Password!12345'));
 }

 public function test_revoking_permission_before_otp_prevents_final_activation(): void
 {
  Mail::fake();$email='hsandcoattorney@gmail.com';$this->post(route('activation.send'),['email'=>$email]);$token=Mail::sent(AccountInvitationMail::class)->first()->token;
  $activation = $this->post(route('activation.update',$token),['email'=>$email,'name'=>'Admin','password'=>'Password!12345','password_confirmation'=>'Password!12345']);
  $this->assertGuest();
  Mail::assertNotSent(EmailOtpMail::class);
  $this->withCookie(config('session.cookie'), $activation->getCookie(config('session.cookie'))->getValue());
  $login = $this->post(route('login.store'), ['email'=>$email,'password'=>'Password!12345'])->assertRedirect(route('two-factor.challenge'));
  $this->withCookie(config('session.cookie'), $login->getCookie(config('session.cookie'))->getValue());
  $code=Mail::sent(EmailOtpMail::class)->first()->code;AllowedActivationEmail::whereKey($email)->update(['enabled'=>false]);
  $this->post(route('two-factor.verify'),['code'=>$code])->assertSessionHasErrors('code');
  $this->assertSame(UserStatus::PendingTwoFactor,User::where('email',$email)->first()->status);
 }

 public function test_existing_active_accounts_are_not_changed_by_activation_request(): void
 {
  Mail::fake();$user=User::factory()->active('super_admin')->create(['email'=>'hsandcoattorney@gmail.com']);$old=$user->password;
  $this->post(route('activation.send'),['email'=>$user->email]);Mail::assertNothingSent();
  $this->assertSame('super_admin',$user->fresh()->role->value);$this->assertSame($old,$user->fresh()->password);
 }

 public function test_admin_invitation_cannot_bypass_email_or_role_reference(): void
 {
  $actor=User::factory()->active('super_admin')->create();
  try {app(InvitationService::class)->invite($actor,'other@example.test','admin');$this->fail('Unlisted email accepted');} catch(\Illuminate\Validation\ValidationException) {}
  try {app(InvitationService::class)->invite($actor,'hermaliaziah22@gmail.com','admin');$this->fail('Role override accepted');} catch(\Illuminate\Validation\ValidationException) {}
  $this->assertSame(1,User::count());
 }

 public function test_mail_diagnostic_does_not_treat_log_as_delivery(): void
 {
  config(['mail.default'=>'log']);$this->artisan('hsnco:mail-test')->assertFailed();
  config(['mail.default'=>'smtp','mail.mailers.smtp.host'=>'smtp.example.test','mail.from.address'=>'no-reply@example.test']);
  $this->artisan('hsnco:mail-test')->assertSuccessful();
  $this->artisan('hsnco:mail-test',['email'=>'outside@example.test'])->assertFailed();
 }
 public function test_delivery_failure_revokes_token_and_allows_a_safe_retry(): void
 {
  $manager=Mail::getFacadeRoot();
  Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('Synthetic SMTP failure'));
  $service=app(InvitationService::class);$email='hsandcoattorney@gmail.com';
  $this->assertFalse($service->sendForEmail($email));
  $this->assertDatabaseHas('user_invitations',['email'=>$email,'token_hash'=>null,'last_sent_at'=>null]);
  Mail::swap($manager);Mail::fake();$this->assertTrue($service->sendForEmail($email));Mail::assertSent(AccountInvitationMail::class,1);
 }

 public function test_repeat_requests_respect_resend_cooldown(): void
 {
  Mail::fake();$service=app(InvitationService::class);$email='hsandcoattorney@gmail.com';
  $this->assertTrue($service->sendForEmail($email));$this->assertFalse($service->sendForEmail($email));Mail::assertSent(AccountInvitationMail::class,1);
 }

 public function test_super_administrator_can_view_the_reference_table(): void
 {
  $user=User::factory()->active('super_admin')->create();
  \App\Models\UserTwoFactorAuthentication::factory()->create(['user_id'=>$user->id,'method'=>'email','secret'=>null,'confirmed_at'=>now()]);
  $this->actingAs($user)->withSession(['auth.two_factor'=>['user_id'=>$user->id,'auth_version'=>$user->auth_version,'verified_at'=>time()]]);
  $this->get(route('admin.users.index'))->assertOk()->assertSee('Acuan email aktivasi')->assertSee('arvinz24wicaksono@gmail.com')->assertSee('hsandcoattorney@gmail.com')->assertSee('hermaliaziah22@gmail.com');
 }}