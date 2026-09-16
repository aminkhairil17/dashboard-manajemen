<?php

namespace App\Http\Middleware;

use App\Support\CurrentCompany;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanySelected
{
    public function __construct(private CurrentCompany $currentCompany) {}

    public function handle(Request $request, Closure $next): Response
    {
        if ($this->currentCompany->get()) {
            return $next($request);
        }

        $companies = $request->user()->companies;

        if ($companies->isEmpty()) {
            abort(403, 'Akun Anda belum terdaftar di perusahaan manapun. Hubungi admin untuk ditambahkan.');
        }

        if ($companies->count() === 1) {
            $this->currentCompany->set($companies->first());

            return $next($request);
        }

        return redirect()->route('perusahaan.pilih');
    }
}
