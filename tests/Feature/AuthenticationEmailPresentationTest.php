<?php
namespace Tests\Feature;

use App\Mail\AccountInvitationMail;
use App\Mail\EmailOtpMail;
use App\Models\User;
use Tests\TestCase;

class AuthenticationEmailPresentationTest extends TestCase
{
    public function test_activation_email_renders_complete_branded_document_with_valid_action(): void
    {
        $user=new User(['email'=>'preview@example.test']);
        $token=str_repeat('A',64);
        $html=(new AccountInvitationMail($user,$token))->render();
        $this->assertStringContainsString('<html lang="id">',$html);
        $this->assertStringContainsString('COUNSELLORS AT LAW',$html);
        $this->assertStringContainsString('bgcolor="#183e34"',$html);
        $this->assertStringContainsString(route('activation.edit',['token'=>$token]),$html);
        $this->assertStringContainsString('preview@example.test',$html);
        $this->assertStringNotContainsString('@yield',$html);
        $this->assertStringNotContainsString('<script',$html);
    }

    public function test_otp_email_renders_code_without_leaking_activation_sections(): void
    {
        $user=new User(['email'=>'preview@example.test']);
        $html=(new EmailOtpMail($user,'123456'))->render();
        $this->assertStringContainsString('123456',$html);
        $this->assertStringContainsString('Verifikasi akses Anda',$html);
        $this->assertStringContainsString('COUNSELLORS AT LAW',$html);
        $this->assertStringNotContainsString('Buat kata sandi akun',$html);
        $this->assertStringNotContainsString('@yield',$html);
    }
}