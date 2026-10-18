@extends('layouts.admin')
@section('title', 'Pengaturan')
@section('intro', 'Jaga identitas firma dan informasi kontak tetap konsisten.')
@section('content')
<form method="post" action="{{ route('admin.settings.update') }}">@csrf @method('PUT')
<fieldset @disabled($preview)>
    <div class="row g-4">
        <div class="col-xl-7">
            <section class="panel">
                <div class="panel-heading"><div><h2>Identitas firma</h2><p>Informasi utama untuk website HSNCO.</p></div></div>
                <div class="editor-body">
                    <x-admin.field name="name" label="Nama firma" :value="$values['name']" required maxlength="255" />
                    <x-admin.field name="tagline" label="Tagline firma" type="textarea" :value="$values['tagline']" required maxlength="1000" />
                    <x-admin.field name="description" label="Deskripsi pencarian" type="textarea" :value="$values['description']" maxlength="1000" />
                    <div class="mt-3"><label class="form-label" for="logo_media_id">Logo firma</label><select class="form-select" id="logo_media_id" name="logo_media_id"><option value="">Logo bawaan</option>@foreach($mediaOptions as $media)<option value="{{ $media->id }}" @selected(old('logo_media_id', $values['logo_media_id']) === $media->id)>{{ $media->original_name }}</option>@endforeach</select><div class="form-text">Pilih gambar publik dari Media Library.</div></div>
                    <div class="mt-3"><label class="form-label" for="favicon_path">Favicon</label><select class="form-select" id="favicon_path" name="favicon_path"><option value="assets/icons/favicon.svg" @selected(old('favicon_path', $values['favicon_path']) === 'assets/icons/favicon.svg')>HSNCO (SVG)</option></select></div>
                </div>
            </section>
        </div>
        <div class="col-xl-5">
            <section class="panel">
                <div class="panel-heading"><div><h2>Informasi kontak</h2><p>Gunakan informasi yang sudah diverifikasi firma.</p></div></div>
                <div class="editor-body">
                    <x-admin.field name="office_address" label="Alamat kantor" type="textarea" :value="$values['office_address']" maxlength="1000" />
                    <x-admin.field name="email" label="Email kontak" type="email" :value="$values['email']" maxlength="254" />
                    <x-admin.field name="phone" label="Nomor telepon" :value="$values['phone']" maxlength="40" />
                    <input type="hidden" name="contact_verified" value="0">
                    <div class="form-check"><input class="form-check-input" type="checkbox" id="contactVerified" name="contact_verified" value="1" @checked(old('contact_verified', $values['contact_verified']))><label class="form-check-label" for="contactVerified">Informasi kontak sudah diverifikasi</label></div>
                </div>
            </section>
        </div>
    </div>
    <section class="panel mt-4">
        <div class="panel-heading"><div><h2>Navigasi utama</h2><p>Urutan menu mengikuti navigasi website yang sudah ada.</p></div></div>
        <div class="editor-body row g-3">
            @foreach (['home' => 'Beranda', 'about' => 'Tentang Firma', 'practice-areas' => 'Bidang Praktik', 'team' => 'Profesional', 'insights' => 'Publikasi', 'contact' => 'Kontak'] as $route => $label)
                <div class="col-md-6"><x-admin.field name="navigation.{{ $route }}" :label="$label" :value="$values['navigation'][$route]" required maxlength="80" /></div>
            @endforeach
        </div>
    </section>
    <div class="mt-4"><button type="submit" class="btn btn-dark">Simpan pengaturan</button></div>
</fieldset>
</form>
<section class="panel mt-4"><div class="panel-heading"><div><h2>Preferensi admin</h2><p>Preferensi ini hanya berlaku untuk akun Anda.</p></div></div><div class="editor-body"><form method="post" action="{{ route('admin.settings.preferences.update') }}" class="row g-3 align-items-end">@csrf @method('PUT')<div class="col-md-6"><label class="form-label" for="page_size">Jumlah baris per halaman</label><select class="form-select" id="page_size" name="page_size"><option value="10" @selected(old('page_size', $preferences['page_size'] ?? 10) == 10)>10</option><option value="20" @selected(old('page_size', $preferences['page_size'] ?? 10) == 20)>20</option><option value="50" @selected(old('page_size', $preferences['page_size'] ?? 10) == 50)>50</option></select></div><div class="col-md-6"><button class="btn btn-outline-dark">Simpan preferensi</button></div></form></div></section>
@endsection

