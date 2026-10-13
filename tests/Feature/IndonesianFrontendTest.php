<?php

namespace Tests\Feature;

use Tests\TestCase;

class IndonesianFrontendTest extends TestCase
{
    public function test_all_pages_use_indonesian_content_and_document_language(): void
    {
        foreach (['/', '/about', '/practice-areas', '/team', '/insights', '/contact', '/sitemap'] as $path) {
            $this->get($path)->assertOk()
                ->assertSee('<html lang="id">', false)
                ->assertSee('Beranda')
                ->assertSee('Tentang Firma')
                ->assertSee('Bidang Praktik')
                ->assertSee('Jadwalkan konsultasi')
                ->assertDontSee('Schedule consultation')
                ->assertDontSee('Content placeholder');
        }
    }

    public function test_contact_labels_and_honest_demo_note_are_indonesian(): void
    {
        $this->get('/contact')->assertOk()
            ->assertSee('Nama depan')
            ->assertSee('Nama belakang')
            ->assertSee('Periksa permintaan')
            ->assertSee('data tidak dikirim atau disimpan')
            ->assertSee('name="_token"', false);
    }
}