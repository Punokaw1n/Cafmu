<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class TenantController extends Controller
{
    /**
     * Tampilkan daftar semua tenant.
     */
    public function index()
    {
        $tenants = Tenant::withCount(['orders', 'users'])
            ->orderByDesc('created_at')
            ->get();

        return view('superadmin.index', compact('tenants'));
    }

    /**
     * Tampilkan form buat tenant baru.
     */
    public function create()
    {
        return view('superadmin.create');
    }

    /**
     * Simpan tenant baru + akun admin kafe.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'slug'        => ['required', 'string', 'max:50', 'unique:tenants,subdomain', 'regex:/^[a-z0-9\-]+$/'],
            'admin_name'  => ['required', 'string', 'max:100'],
            'admin_email' => ['required', 'email', 'unique:users,email'],
            'admin_password' => ['required', Password::min(8)],
        ], [
            'slug.regex'          => 'Slug hanya boleh berisi huruf kecil, angka, dan tanda hubung (-).',
            'slug.unique'         => 'Slug ini sudah dipakai kafe lain.',
            'admin_email.unique'  => 'Email ini sudah terdaftar.',
        ]);

        // Buat tenant baru
        $tenant = Tenant::create([
            'name'      => $request->name,
            'subdomain' => $request->slug,
            'is_active' => true,
        ]);

        // Buat akun admin untuk kafe tersebut
        User::create([
            'tenant_id' => $tenant->id,
            'name'      => $request->admin_name,
            'email'     => $request->admin_email,
            'password'  => Hash::make($request->admin_password),
        ]);

        return redirect()->route('superadmin.index')
            ->with('success', "Kafe \"{$tenant->name}\" berhasil ditambahkan! Admin dapat login dengan email {$request->admin_email}.");
    }

    /**
     * Aktifkan / nonaktifkan tenant.
     */
    public function toggle(Tenant $tenant)
    {
        $tenant->update(['is_active' => !$tenant->is_active]);

        $status = $tenant->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Kafe \"{$tenant->name}\" berhasil {$status}.");
    }

    /**
     * Hapus tenant (soft delete — semua data tetap ada, hanya tenant yang dihapus).
     */
    public function destroy(Tenant $tenant)
    {
        $name = $tenant->name;
        $tenant->users()->update(['tenant_id' => null]); // lepas user dari tenant
        $tenant->delete();

        return redirect()->route('superadmin.index')
            ->with('success', "Kafe \"{$name}\" berhasil dihapus.");
    }
}
