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
        $parts = explode('.', $host);

        // Ambil subdomain (bagian pertama dari host)
        // Contoh: kopisusu.cafmu.test → subdomain = kopisusu
        if (count($parts) < 2) {
            abort(404, 'Tenant tidak ditemukan.');
        }

        $subdomain = $parts[0];

        // Jangan resolve tenant untuk subdomain 'www'
        if ($subdomain === 'www') {
            return $next($request);
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
