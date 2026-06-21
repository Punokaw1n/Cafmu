<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TenantSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function index()
    {
        $tenant = App::make('currentTenant');

        // Ambil semua settings dalam bentuk key-value array
        $settings = TenantSetting::pluck('value', 'key')->toArray();

        return view('admin.settings.index', compact('settings', 'tenant'));
    }

    public function update(Request $request)
    {
        $tenant = App::make('currentTenant');

        $request->validate([
            'business_name'    => 'required|string|max:255',
            'business_address' => 'nullable|string|max:500',
            'wa_number'        => 'nullable|string|max:20',
            'primary_color'    => 'nullable|string|max:7',
            'logo'             => 'nullable|image|max:1024',
        ]);

        // Update nama tenant langsung di tabel tenants
        $tenant->update(['name' => $request->business_name]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Hapus logo lama jika ada
            $oldLogo = TenantSetting::where('key', 'logo_url')->first();
            if ($oldLogo && $oldLogo->value) {
                Storage::disk('public')->delete($oldLogo->value);
            }

            $logoPath = $request->file('logo')->store('logos', 'public');
            $this->saveSetting('logo_url', $logoPath);
        }

        // Simpan settings lainnya
        $this->saveSetting('business_address', $request->business_address);
        $this->saveSetting('wa_number', $request->wa_number);
        $this->saveSetting('primary_color', $request->primary_color ?? '#d97706');

        return redirect()->route('admin.settings.index')
                         ->with('success', 'Pengaturan berhasil disimpan.');
    }

    private function saveSetting(string $key, ?string $value): void
    {
        $tenant = App::make('currentTenant');

        TenantSetting::updateOrCreate(
            ['tenant_id' => $tenant->id, 'key' => $key],
            ['value' => $value]
        );
    }
}
