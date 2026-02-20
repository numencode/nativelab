<?php

use Livewire\Volt\Component;
use Native\Mobile\Facades\System;

new class extends Component
{
    public bool $isNative = false;

    public ?string $platform = null;
    public ?string $status = null;
    public ?string $error = null;

    public function mount(): void
    {
        $p = getenv('NATIVEPHP_PLATFORM');
        $this->isNative = (bool) $p;
        $this->platform = $p ?: 'web';
    }

    public function openSettings(): void
    {
        $this->status = null;
        $this->error = null;

        try {
            $res = System::appSettings();
            $this->status = ($res['success'] ?? false) ? 'Opened app settings.' : 'Failed to open app settings.';
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
                <h2 class="text-xl font-extrabold m-0">System</h2>
                <p class="nt-muted text-sm mt-2 mb-0">
                    System-level actions (like opening this app’s settings).
                </p>
            </div>

            <div class="mt-5 nt-card p-4">
                <div class="font-extrabold">Runtime</div>
                <div class="mt-2 text-sm">
                    <span class="nt-muted">Platform:</span> <span class="font-semibold">{{ $platform }}</span>
                </div>
                <div class="mt-1 text-sm nt-muted">
                    (Expected: <code>android</code> / <code>ios</code> inside the app, <code>web</code> in the browser.)
                </div>
            </div>

            @if (! $isNative)
                <div class="mt-5 nt-card p-4">
                    <div class="font-semibold">Not running in NativePHP</div>
                    <div class="nt-muted text-sm mt-1">
                        This plugin action needs the Android/iOS runtime.
                    </div>
                </div>
            @else
                <div class="mt-5 flex justify-center">
                    <button wire:click="openSettings" class="nt-btn">⚙️ Open App Settings</button>
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
                    <div class="font-extrabold">When is this useful?</div>
                    <div class="nt-muted text-sm mt-2">
                        If a user denies a permission (camera, etc.), you can send them directly to the app’s settings page to enable it.
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
