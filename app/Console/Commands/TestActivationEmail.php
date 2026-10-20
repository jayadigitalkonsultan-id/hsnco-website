<?php
namespace App\Console\Commands;

use App\Models\AllowedActivationEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestActivationEmail extends Command
{
    protected $signature = 'hsnco:mail-test {email? : Optional approved test recipient}';
    protected $description = 'Check SMTP configuration without exposing secrets; send only when an approved email is supplied';

    public function handle(): int
    {
        $mailer = config('mail.default');
        $settings = config('mail.mailers.'.$mailer, []);
        $this->table(['Pemeriksaan', 'Nilai'], [
            ['Mailer', $mailer], ['Transport', $settings['transport'] ?? '-'],
            ['Host SMTP', $settings['host'] ?? '-'], ['Port', $settings['port'] ?? '-'],
            ['Username terisi', filled($settings['username'] ?? null) ? 'Ya' : 'Tidak'],
            ['Password terisi', filled($settings['password'] ?? null) ? 'Ya' : 'Tidak'],
            ['Pengirim terisi', filled(config('mail.from.address')) ? 'Ya' : 'Tidak'],
        ]);
        if (($settings['transport'] ?? null) !== 'smtp') {
            $this->error('Mailer belum memakai SMTP. Isi konfigurasi provider pada Environment aplikasi; mailer log tidak mengirim ke inbox.');
            return self::FAILURE;
        }
        if (!filled($settings['host'] ?? null) || !filter_var(config('mail.from.address'), FILTER_VALIDATE_EMAIL)) {
            $this->error('Host SMTP atau alamat pengirim belum valid.');
            return self::FAILURE;
        }
        if (!$this->argument('email')) {
            $this->info('Konfigurasi dasar SMTP tersedia. Belum ada koneksi atau pengiriman email yang diuji.');
            return self::SUCCESS;
        }
        $email = mb_strtolower(trim($this->argument('email')));
        if (!AllowedActivationEmail::approved($email)) {
            $this->error('Tujuan tidak termasuk email aktivasi yang diizinkan.');
            return self::FAILURE;
        }
        try {
            Mail::raw('Ini adalah email uji konfigurasi SMTP HSNCO. Email ini tidak mengaktifkan akun dan tidak memuat kata sandi atau kode verifikasi.', fn ($message) => $message->to($email)->subject('Uji pengiriman email HSNCO'));
            $this->info('Pesan diterima transport SMTP. Periksa inbox dan folder spam; penerimaan inbox belum dapat dipastikan dari console.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            // Do not print transport exceptions which may contain connection details.
            $this->error('Pengiriman SMTP gagal. Periksa host, port, TLS, kredensial, pengirim terverifikasi dan koneksi keluar server.');
            return self::FAILURE;
        }
    }
}