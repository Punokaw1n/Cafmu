<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $subdomain = null;

        // Development mode: localhost / 127.0.0.1 → ambil tenant dari query ?tenant= atau session
        if (in_array($host, ['localhost', '127.0.0.1'])) {
            $subdomain = $request->query('tenant') ?? session('tenant_subdomain');

            if (!$subdomain) {
                abort(404, 'Tenant tidak ditemukan. Gunakan ?tenant=nama_subdomain');
            }

            // Simpan ke session agar tidak perlu ?tenant= di setiap URL
            session(['tenant_subdomain' => $subdomain]);
        } else {
            // Production mode: ambil dari subdomain
            // Contoh: kopisusu.cafmu.test → subdomain = kopisusu
            $parts = explode('.', $host);

            if (count($parts) < 2) {
                abort(404, 'Tenant tidak ditemukan.');
            }

            $subdomain = $parts[0];

            // Jangan resolve tenant untuk subdomain 'www'
            if ($subdomain === 'www') {
                return $next($request);
            }
        }

        $tenant = Tenant::where('subdomain', $subdomain)
            ->where('is_active', true)
            ->first();

        if (!$tenant) {
            abort(404, 'Tenant tidak ditemukan atau tidak aktif.');
        }

        // Simpan tenant aktif ke dalam container Laravel
        App::instance('currentTenant', $tenant);

        // Share ke semua Blade views
        view()->share('currentTenant', $tenant);

        return $next($request);
    }
}
