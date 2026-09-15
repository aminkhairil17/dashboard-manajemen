<?php

namespace App\Livewire\Dashboard;

use App\Models\TvDisplayToken;
use Illuminate\Support\Collection;
use Livewire\Component;

class TvKioskManager extends Component
{
    public string $label = '';

    public string $type = TvDisplayToken::TYPE_PUBLIK;

    public function create(): void
    {
        $this->validate([
            'label' => 'required|string|max:80',
            'type' => 'required|in:'.TvDisplayToken::TYPE_PUBLIK.','.TvDisplayToken::TYPE_DIREKTUR,
        ]);

        TvDisplayToken::generate($this->label, $this->type, auth()->id());

        $this->label = '';
        $this->type = TvDisplayToken::TYPE_PUBLIK;
        session()->flash('status', 'Link TV baru berhasil dibuat.');
    }

    public function revoke(int $id): void
    {
        $token = TvDisplayToken::findOrFail($id);
        $token->update(['revoked_at' => now()]);

        session()->flash('status', 'Link TV "'.$token->label.'" sudah dicabut.');
    }

    public function getTokensProperty(): Collection
    {
        return TvDisplayToken::latest()->get();
    }

    public function render()
    {
        return view('livewire.dashboard.tv-kiosk-manager', [
            'tokens' => $this->tokens,
        ])->layout('layouts.app');
    }
}
