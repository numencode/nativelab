<?php

use Livewire\Volt\Component;
use Native\Mobile\Facades\Browser;

new class extends Component
{
    public string $url = 'https://www.numencode.com';
    public ?string $status = null;
    public ?string $error = null;

    public function setUrl(string $url): void
    {
        $this->url = $url;
        $this->status = null;
        $this->error = null;
    }

    protected function normalizeUrl(string $url): string
    {
        $url = trim($url);

        if (!preg_match('~^https?://~i', $url)) {
            $url = 'https://' . $url;
        }

        return $url;
    }

    public function openInApp(): void
    {
        $this->error = null;
        $this->status = null;

        try {
            $url = $this->normalizeUrl($this->url);
            Browser::inApp($url);
            $this->status = "Opened in-app: {$url}";
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function openSystem(): void
    {
        $this->error = null;
        $this->status = null;

        try {
            $url = $this->normalizeUrl($this->url);
            Browser::open($url);
            $this->status = "Opened system browser: {$url}";
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }
};

?>

<div class="nt-app">
    <div class="max-w-md mx-auto">
        <div class="nt-card p-5">
            <div class="text-center">
                <h2 class="text-xl font-extrabold m-0">Browser</h2>
                <p class="nt-muted text-sm mt-2 mb-0">
                    Test in-app browsing and system browser.
                </p>
            </div>

            <div class="mt-5">
                <label class="block nt-muted text-xs mb-2">URL</label>

                <input
                        type="text"
                        wire:model.defer="url"
                        class="w-full rounded-2xl px-4 py-3"
                        style="background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.10); color: rgba(255,255,255,.92); outline: none;"
                        placeholder="https://example.com"
                />

                <div class="mt-3 flex flex-wrap gap-2 justify-center">
                    <button type="button" class="nt-pill" wire:click="setUrl('https://nativephp.com/mobile')">NativePHP</button>
                    <button type="button" class="nt-pill" wire:click="setUrl('https://www.numencode.com')">Numencode</button>
                    <button type="button" class="nt-pill" wire:click="setUrl('https://laravel.com')">Laravel</button>
                </div>
            </div>

            <div class="mt-5 flex flex-wrap justify-center gap-3">
                <button wire:click="openInApp" class="nt-btn">🧭 In-app</button>
                <button wire:click="openSystem" class="nt-btn">🌍 System</button>
            </div>

            @if ($status)
                <div class="mt-4 text-center nt-muted text-sm">{{ $status }}</div>
            @endif

            @if ($error)
                <div class="mt-4 text-center" style="color:#ff6b6b; font-size: 13px;">
                    {{ $error }}
                </div>
            @endif

            <div class="mt-6 nt-card p-4">
                <div class="font-extrabold">What each button does?</div>

                <ul class="mt-3 text-sm nt-muted" style="margin:0; padding-left: 18px;">
                    <li><b>In-app</b>: opens Chrome Custom Tabs / SFSafariViewController inside your app.</li>
                    <li><b>System</b>: opens the link on the device’s default browser and leaves the app.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
