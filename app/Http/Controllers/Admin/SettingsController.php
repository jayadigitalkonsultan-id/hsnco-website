<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Cms\AdminSettingsRequest;
use App\Models\SiteSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Media;
use App\Models\MediaUsage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use App\Services\SecurityAudit;

class SettingsController extends AdminController
{
    public function edit(Request $request)
    {
        $this->authorizeRead($request, 'viewAny', SiteSetting::class);
        $settings = SiteSetting::whereIn('key', ['identity.name', 'identity.logo', 'identity.favicon', 'contact.details', 'footer.content', 'seo.defaults', 'navigation.main'])->pluck('value', 'key');
        $navigationDefaults = [
            'home' => 'Beranda', 'about' => 'Tentang Firma', 'practice-areas' => 'Bidang Praktik',
            'team' => 'Profesional', 'insights' => 'Publikasi', 'contact' => 'Kontak',
        ];
        $navigationItems = collect($settings['navigation.main']['items'] ?? [])
            ->filter(fn ($item) => is_array($item) && isset($item['route'], $item['label']))
            ->keyBy('route');
        $values = [
            'name' => $settings['identity.name']['name'] ?? '',
            'logo_media_id' => $settings['identity.logo']['media_id'] ?? '',
            'favicon_path' => $settings['identity.favicon']['path'] ?? 'assets/icons/favicon.svg',
            'office_address' => $settings['contact.details']['office_address'] ?? '',
            'email' => $settings['contact.details']['email'] ?? '',
            'phone' => $settings['contact.details']['phone'] ?? '',
            'contact_verified' => $settings['contact.details']['verified'] ?? false,
            'tagline' => $settings['footer.content']['tagline'] ?? '',
            'description' => $settings['seo.defaults']['description'] ?? '',
            'navigation' => collect($navigationDefaults)->mapWithKeys(fn ($label, $route) => [
                $route => $navigationItems->get($route)['label'] ?? $label,
            ])->all(),
        ];

        $mediaOptions = Media::query()->where('visibility', 'public')->where('mime_type', 'like', 'image/%')->orderBy('original_name')->get(['id', 'original_name']);
        $preferences = $request->user()?->preferences ?? ['page_size' => 10];
        return view('admin.settings', compact('values', 'mediaOptions', 'preferences'));
    }

    public function update(AdminSettingsRequest $request)
    {
        $data = $request->validated();
        $navigationDefaults = [
            'home' => 'Beranda', 'about' => 'Tentang Firma', 'practice-areas' => 'Bidang Praktik',
            'team' => 'Profesional', 'insights' => 'Publikasi', 'contact' => 'Kontak',
        ];
        $storedNavigation = SiteSetting::query()->where('key', 'navigation.main')->first()?->value ?? [];
        $navigation = $data['navigation'] ?? collect($storedNavigation['items'] ?? [])
            ->filter(fn ($item) => is_array($item) && isset($item['route'], $item['label']))
            ->keyBy('route')
            ->map(fn ($item, $route) => $item['label'])
            ->all();
        $navigation = array_replace($navigationDefaults, $navigation);
        DB::transaction(function () use ($request, $data, $navigation) {
            $updates = [
                'identity.name' => ['name' => $data['name']],
                'identity.logo' => ['media_id' => $data['logo_media_id'] ?? null],
                'identity.favicon' => ['path' => $data['favicon_path']],
                'contact.details' => ['office_address' => $data['office_address'] ?? null, 'email' => $data['email'] ?? null, 'phone' => $data['phone'] ?? null, 'verified' => $request->boolean('contact_verified')],
                'footer.content' => ['tagline' => $data['tagline']],
                'seo.defaults' => ['site_name' => $data['name'], 'description' => $data['description'] ?? null],
                'navigation.main' => ['items' => collect($navigation)->map(fn ($label, $route) => ['route' => $route, 'label' => $label])->values()->all()],
            ];
            foreach ($updates as $key => $value) {
                $setting = SiteSetting::firstOrCreate(['key' => $key], ['group' => explode('.', $key)[0], 'value' => []]);
                $setting->update(['value' => array_merge($setting->value, $value), 'updated_by' => $request->user()->id]);
            }
            $logoSetting = SiteSetting::where('key', 'identity.logo')->firstOrFail();
            $logoSetting->mediaUsages()->delete();
            if (! empty($data['logo_media_id'])) {
                MediaUsage::query()->create(['media_id' => $data['logo_media_id'], 'usable_type' => $logoSetting->getMorphClass(), 'usable_id' => $logoSetting->getKey(), 'field' => 'value.media_id']);
            }
        });
        app(SecurityAudit::class)->record('site_settings_updated', 'success', $request->user(), $request, null, ['fields' => array_values(array_diff(array_keys($data), ['contact_verified']))]);

        return redirect()->route('admin.settings.edit')->with('success', 'Pengaturan website berhasil disimpan.');
    }

    public function updatePreferences(Request $request)
    {
        Gate::authorize('updatePreferences', $request->user());
        $data = $request->validate(['page_size' => ['required', Rule::in([10, 20, 50])]]);
        $user = $request->user();
        $user->forceFill(['preferences' => array_merge($user->preferences ?? [], $data)])->save();
        app(SecurityAudit::class)->record('admin_preferences_updated', 'success', $user, $request, null, ['page_size' => (int) $data['page_size']]);
        return redirect()->route('admin.settings.edit')->with('success', 'Preferensi admin berhasil disimpan.');
    }
}
