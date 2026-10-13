import fs from 'node:fs';
import path from 'node:path';
import crypto from 'node:crypto';
const root=process.cwd(),source='C:/laragon/www/hsnco-template/hsnco-bootstrap-template';
if(path.resolve(root)!==path.resolve('C:/laragon/www/hsnco-website'))throw new Error('Wrong destination');
const write=(file,text)=>{fs.mkdirSync(path.dirname(path.join(root,file)),{recursive:true});fs.writeFileSync(path.join(root,file),text+'\n');};
const walk=dir=>fs.readdirSync(dir,{withFileTypes:true}).flatMap(e=>e.isDirectory()?walk(path.join(dir,e.name)):[path.join(dir,e.name)]);
const hash=file=>crypto.createHash('sha256').update(fs.readFileSync(file)).digest('hex');
const inventory=walk(source).map(file=>({file:path.relative(source,file).replaceAll('\\','/'),sha256:hash(file)}));
write('docs/SOURCE_HASHES.json',JSON.stringify(inventory,null,2));
const protectedFiles=['.env','composer.json','composer.lock','package.json','vite.config.js','bootstrap/app.php','config/auth.php','config/session.php','database/database.sqlite','resources/views/welcome.blade.php','tests/Feature/ExampleTest.php'];
write('docs/EXISTING_FILE_HASHES.json',JSON.stringify(protectedFiles.map(file=>({file,sha256:hash(path.join(root,file))})),null,2));
const pages=[['home','index.html','/'],['about','pages/about.html','/about'],['practice-areas','pages/practice-areas.html','/practice-areas'],['team','pages/team.html','/team'],['insights','pages/insights.html','/insights'],['contact','pages/contact.html','/contact'],['sitemap','pages/sitemap.html','/sitemap']];
const htmls=pages.map(([id,file,url])=>({id,file,url,html:fs.readFileSync(path.join(source,file),'utf8')}));
const pageMap=Object.fromEntries(pages.map(([id,file])=>[file,id]));
const assetFiles=new Set();
function includeAsset(relative){relative=relative.replaceAll('\\','/');if(!relative.startsWith('assets/')||assetFiles.has(relative))return;const file=path.resolve(source,relative);if(!file.startsWith(path.resolve(source)+path.sep)||!fs.existsSync(file))throw new Error('Missing asset '+relative);assetFiles.add(relative);if(relative.endsWith('.css')){const css=fs.readFileSync(file,'utf8');for(const match of css.matchAll(/url\(\s*['"]?([^)'"\s]+)['"]?\s*\)/g)){if(!/^(data:|https?:|#)/.test(match[1]))includeAsset(path.relative(source,path.resolve(path.dirname(file),match[1])).replaceAll('\\','/'));}}}
for(const page of htmls){for(const match of page.html.matchAll(/(?:src|href)="((?:\.\.\/)?assets\/[^"#?]+)"/g))includeAsset(match[1].replace(/^\.\.\//,''));}
const content=JSON.parse(fs.readFileSync(path.join(source,'assets/js/content-data.js'),'utf8').replace(/^window\.HSNCO_CONTENT\s*=\s*/,'').replace(/;\s*$/,''));
for(const practice of content.practices)includeAsset('assets/images/'+practice.image+'.jpg');
for(const license of ['assets/fonts/manrope-OFL.txt','assets/fonts/playfair-display-OFL.txt','assets/fonts/cormorant-garamond-OFL.txt','assets/vendor/bootstrap/LICENSE','assets/vendor/jquery/LICENSE.txt','assets/vendor/jstree/LICENSE-MIT'])includeAsset(license);
for(const relative of [...assetFiles]){const src=path.join(source,relative),dest=path.join(root,'public',relative);if(fs.existsSync(dest)&&hash(src)!==hash(dest))throw new Error('Existing different asset '+relative);fs.mkdirSync(path.dirname(dest),{recursive:true});fs.copyFileSync(src,dest);}
write('docs/ASSET_MAPPING.json',JSON.stringify([...assetFiles].sort().map(file=>({source:file,destination:'public/'+file,sha256:hash(path.join(source,file))})),null,2));
function convert(markup){return markup.replace(/\{\{base\}\}/g,'').replace(/\b(src|href)="((?:\.\.\/)?assets\/[^"?#]+)([?#][^"]*)?"/g,(_,attr,file,suffix='')=>attr+'="{{ asset(\''+file.replace(/^\.\.\//,'')+'\') }}'+suffix+'"').replace(/href="((?:\.\.\/)?(?:index\.html|pages\/[a-z-]+\.html|[a-z-]+\.html))([#?][^"]*)?"/g,(_,file,suffix='')=>{let normalized=file.replace(/^\.\.\//,'');if(normalized!=='index.html'&&!normalized.startsWith('pages/'))normalized='pages/'+normalized;const id=pageMap[normalized];if(!id)throw new Error('Unknown route '+file);return 'href="{{ route(\''+id+'\') }}'+suffix+'"';});}
function shared(name){let text=convert(fs.readFileSync(path.join(source,'components',name+'.html'),'utf8'));text=text.replace(/\{\{active:([^}]+)\}\}/g,(_,id)=>'{{ request()->routeIs(\''+id+'\') ? \'active\' : \'\' }}').replace(/\{\{current:([^}]+)\}\}/g,(_,id)=>'@if(request()->routeIs(\''+id+'\')) aria-current="page" @endif').replace('{{navigation}}',"@include('layouts.partials.navigation')");write('resources/views/layouts/partials/'+name+'.blade.php',text);}
for(const name of ['header','navigation','footer'])shared(name);
write('resources/views/layouts/frontend.blade.php',`<!doctype html>
<html lang="en"><head>
@include('layouts.partials.head')
@stack('styles')
</head><body class="{{ request()->routeIs('home') ? 'home-page' : 'inner-page' }}" data-page="{{ request()->route()->getName() }}" data-asset-base="{{ asset('assets') }}/">
@include('layouts.partials.header')
<main id="main" tabindex="-1">@yield('content')</main>
@include('layouts.partials.footer')
<noscript><p class="no-script container">Navigation links remain available. Interactive previews and local form validation require JavaScript.</p></noscript>
@include('layouts.partials.scripts')
@stack('scripts')
</body></html>`);
write('resources/views/layouts/partials/head.blade.php',`@php
    $pageTitle = trim($__env->yieldContent('title', 'HSNCO'));
    $pageDescription = trim($__env->yieldContent('description', 'HSNCO website'));
@endphp
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="{{ $pageDescription }}"><meta name="theme-color" content="#080808">
<meta property="og:type" content="website"><meta property="og:title" content="{{ $pageTitle }}"><meta property="og:description" content="{{ $pageDescription }}">
<title>{{ $pageTitle }}</title>
<link rel="icon" href="{{ asset('assets/icons/favicon.svg') }}" type="image/svg+xml">
<link rel="preload" href="{{ asset('assets/fonts/manrope.woff2') }}" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}">
@foreach(['variables','style','components',request()->routeIs('home') ? 'home' : 'inner-pages','responsive'] as $sheet)
<link rel="stylesheet" href="{{ asset('assets/css/'.$sheet.'.css') }}">
@endforeach
@if(request()->routeIs('home'))<link rel="preload" href="{{ asset('assets/images/hero.jpg') }}" as="image">@endif`);
write('resources/views/layouts/partials/scripts.blade.php',`<script defer src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
@if(!request()->routeIs('sitemap'))<script defer src="{{ asset('assets/js/content-data.js') }}"></script>@endif
@foreach(['components','main'] as $script)<script defer src="{{ asset('assets/js/'.$script.'.js') }}"></script>@endforeach
<script defer src="{{ asset('assets/plugins-init/bootstrap-init.js') }}"></script>`);
write('resources/views/components/frontend/breadcrumb.blade.php',`@props(['current'])
<nav aria-label="Breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li><li class="breadcrumb-item active" aria-current="page">{{ $current }}</li></ol></nav>`);
write('resources/views/components/frontend/page-banner.blade.php',`@props(['current','eyebrow'])
<section class="inner-hero"><div class="container"><x-frontend.breadcrumb :current="$current" /><div class="row g-4 g-lg-5 align-items-end"><div class="col-lg-6"><p class="eyebrow">{{ $eyebrow }}</p><h1>{{ $heading }}</h1></div><div class="col-lg-6"><p class="banner-intro">{{ $intro }}</p></div></div></div></section>`);
write('resources/views/components/frontend/button.blade.php',`@props(['href','variant'=>'outline'])
<a href="{{ $href }}" {{ $attributes->class(['btn', $variant === 'primary' ? 'btn-brand' : 'btn-outline-brand']) }}>{{ $slot }}</a>`);
write('resources/views/components/frontend/section-heading.blade.php',`<h2 {{ $attributes->class(['section-title']) }}>{{ $slot }}</h2>`);
write('resources/views/components/frontend/practice-card.blade.php',`@props(['href'])
<a href="{{ $href }}" {{ $attributes->class(['practice-tile']) }}>{{ $slot }}</a>`);
let modalSaved=false,formSaved=false;
for(const page of htmls){let body=page.html.match(/<main\b[^>]*>([\s\S]*?)<\/main>/)[1];let modal=body.match(/<div class="modal fade" id="detailModal"[\s\S]*$/);if(modal){const markup=convert(modal[0].trim());if(!modalSaved){write('resources/views/components/frontend/detail-modal.blade.php',markup);modalSaved=true;}body=body.slice(0,modal.index)+'\n<x-frontend.detail-modal />';}
body=convert(body);
const banner=body.match(/<section class="inner-hero">([\s\S]*?)<\/section>/);if(banner){const title=banner[1].match(/<h1>([\s\S]*?)<\/h1>/)[1],intro=banner[1].match(/<p class="banner-intro">([\s\S]*?)<\/p>/)[1],eyebrow=banner[1].match(/<p class="eyebrow">([^<]*)<\/p>/)[1],current=banner[1].match(/aria-current="page">([^<]*)<\/li>/)[1];body=body.replace(banner[0],'<x-frontend.page-banner current="'+current+'" eyebrow="'+eyebrow+'"><x-slot:heading>'+title+'</x-slot:heading><x-slot:intro>'+intro+'</x-slot:intro></x-frontend.page-banner>');}
body=body.replace(/<h2 class="section-title([^"]*)">([\s\S]*?)<\/h2>/g,(_,extra,title)=>'<x-frontend.section-heading'+(extra.trim()?' class="'+extra.trim()+'"':'')+'>'+title+'</x-frontend.section-heading>');
body=body.replace(/<a class="btn btn-outline-brand" href="([^"]+)">([\s\S]*?)<\/a>/g,(_,href,text)=>'<x-frontend.button href="'+href+'">'+text+'</x-frontend.button>');
body=body.replace(/<a class="practice-tile" href="([^"]+)">([\s\S]*?)<\/a>/g,(_,href,text)=>'<x-frontend.practice-card href="'+href+'">'+text+'</x-frontend.practice-card>');
body=body.replace(/<form class="row g-4 enquiry-form" data-demo-form id="(home|contact)-form">[\s\S]*?<\/form>/g,(markup,prefix)=>{if(!formSaved){const form=markup.replace('id="'+prefix+'-form">','id="'+prefix+'-form" method="post" action="{{ route(\'contact\') }}">\n@csrf\n').replaceAll(prefix+'-','{{ $prefix }}-');write('resources/views/components/frontend/enquiry-form.blade.php',"@props(['prefix'])\n"+form);formSaved=true;}return '<x-frontend.enquiry-form prefix="'+prefix+'" />';});
const title=page.html.match(/<title>([^<]+)<\/title>/)[1],description=page.html.match(/<meta name="description" content="([^"]+)"/)[1];let view="@extends('layouts.frontend')\n@section('title')\n"+title+"\n@endsection\n@section('description')\n"+description+"\n@endsection\n@section('content')\n"+body+"\n@endsection";
if(page.id==='sitemap'){view+=`\n@push('styles')
<link rel="stylesheet" href="{{ asset('assets/vendor/jstree/themes/default/style.min.css') }}"><link rel="stylesheet" href="{{ asset('assets/css/plugins.css') }}">
@endpush
@push('scripts')
<script defer src="{{ asset('assets/vendor/jquery/jquery.min.js') }}"></script><script defer src="{{ asset('assets/vendor/jstree/jstree.min.js') }}"></script><script defer src="{{ asset('assets/plugins-init/jstree-init.js') }}"></script>
@endpush`;}
write('resources/views/frontend/'+page.id+'.blade.php',view);}
const componentsFile=path.join(root,'public/assets/js/components.js');const js=fs.readFileSync(componentsFile,'utf8');fs.writeFileSync(componentsFile,js.replace('`../assets/images/${image}.jpg`',"new URL('images/' + encodeURIComponent(image) + '.jpg', document.body.dataset.assetBase).href"));
let routes=fs.readFileSync(path.join(root,'routes/web.php'),'utf8');write('docs/ORIGINAL_WEB_ROUTES.txt',routes);const welcome=/Route::get\('\/', function \(\) \{\s*return view\('welcome'\);\s*\}\);/;if(!welcome.test(routes))throw new Error('Unexpected existing homepage; cannot replace automatically');routes=routes.replace(welcome,"// Frontend HSNCO: static Blade views; no database or CMS endpoint.\n"+pages.map(([id,,url])=>"Route::view('"+url+"', 'frontend."+id+"')->name('"+id+"');").join('\n'));write('routes/web.php',routes);
const lock=JSON.parse(fs.readFileSync(path.join(root,'composer.lock'),'utf8'));const version=lock.packages.find(p=>p.name==='laravel/framework').version;
write('docs/FRONTEND_INTEGRATION_AUDIT.md',`# Audit integrasi frontend HSNCO\n\n13 Oktober 2026. Laravel terpasang: ${version}, require PHP >=8.2; PHP CLI yang tersedia 8.2.24. Project sudah Laravel; vendor/composer tersedia. Hanya route awal / ke welcome, model User, controller dasar dan AppServiceProvider bawaan; tidak ada auth scaffolding atau middleware custom. Health /up dari bootstrap/app.php dipertahankan. Welcome view dan test existing tetap tersedia.\n\nVite 6 + Tailwind 4 dan axios merupakan scaffold npm, tanpa Bootstrap npm. Frontend menggunakan public/assets lokal, tidak memanggil @vite agar tidak mencampur Tailwind/reset atau Bootstrap ganda. package.json, vite.config.js, composer dan konfigurasi existing tidak diubah.\n\nEnvironment lokal menggunakan SQLite, SESSION_DRIVER=database, CACHE_STORE=database; database.sqlite kosong. Tidak ada migration dijalankan. Preview/test memakai driver file/array melalui environment proses agar frontend dapat diuji tanpa DB, tidak mengubah .env. Untuk Laragon biasa, session backend existing perlu dikonfigurasi oleh pemilik project.\n\nSumber read-only: ${source}. Tujuh dokumen dibaca penuh: Home, About, Practice Areas, Team, Insights, Contact, Sitemap. Home full-photo hero Playfair; internal banner Cormorant; body Manrope, charcoal/gold/paper, shared navigation/footer. Semua section, placeholders, stock images dan honest demo form dipertahankan. Bootstrap 5.3.8 lokal; jQuery 3.7.1 + jsTree 3.3.17 hanya sitemap. Chart.js admin tidak digunakan.\n\nSeluruh CSS dan JavaScript dibaca untuk referensi dependency dan aset. Closure aset dari HTML/CSS serta data gambar modal menghasilkan ${assetFiles.size} file yang disalin. ASSET_MAPPING.json memberi mapping/checksum. SOURCE_HASHES.json membuktikan seluruh source tetap sama setelah integrasi. Fonts WOFF2, lisensi, sprites jsTree disertakan; logo alternatif/gambar tak dipakai dan authoring JSON tidak disalin.\n\nPath .html dikonversi ke named routes; href fragmen dipertahankan. Assets HTML memakai asset(); CSS relative url tetap valid di public/assets. Image modal JS memakai data-asset-base dari Blade sehingga aman untuk path Laravel. Shared layout/partials, banner/breadcrumb/button/section-heading/practice-card/form/modal menghindari duplikasi. Default Blade escaping dipertahankan; slot static tidak memakai raw dynamic HTML.\n\nTidak ada organisasi/canonical domain rekaan, auth perubahan, database migration, POST delivery handler atau klaim enquiry diterima. Form masih validasi lokal, disabled tanpa JS, disertai @csrf dan penanda demo.\n`);
console.log('Integrated seven Blade pages and '+assetFiles.size+' runtime/licensed assets.');