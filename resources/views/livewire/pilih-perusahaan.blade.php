<div>
    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">Pilih Perusahaan</h2>
    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Akun Anda terdaftar di lebih dari satu perusahaan. Pilih salah satu untuk melanjutkan.</p>

    <div class="space-y-2">
        @foreach ($companies as $company)
            <button type="button" wire:click="pilih({{ $company->id }})"
                class="w-full text-left px-4 py-3 border border-gray-300 dark:border-gray-700 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                <span class="block font-medium text-gray-900 dark:text-gray-100">{{ $company->name }}</span>
                <span class="block text-xs text-gray-500 dark:text-gray-400">{{ $company->pivot->role }}</span>
            </button>
        @endforeach
    </div>
</div>
