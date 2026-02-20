<?php

use Livewire\Volt\Component;
use Native\Mobile\Facades\Network;

new class extends Component
{
    public bool $isNative = false;

    public array $status = [];
    public ?string $statusText = null;
    public ?string $error = null;

    public function mount(): void
    {
        $this->isNative = (bool) (getenv('NATIVEPHP_PLATFORM') ?: false);

        if ($this->isNative) {
            $this->refresh();
        }
    }

    public function refresh(): void
    {
        $this->error = null;
        $this->statusText = null;

        try {
            $s = Network::status();

            $this->status = [
                'connected' => (bool) ($s->connected ?? false),
                'type' => (string) ($s->type ?? 'unknown'),
                'isExpensive' => (bool) ($s->isExpensive ?? false),
                'isConstrained' => (bool) ($s->isConstrained ?? false),
            ];

            if (! $this->status['connected']) {
                $this->statusText = 'No network connection';
            } else {
                $this->statusText =
                    'Connected via ' . $this->status['type'] .
                    ($this->status['isExpensive'] ? ' (metered)' : '');
            }
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
                <h2 class="text-xl font-extrabold m-0">Network</h2>
                <p class="nt-muted text-sm mt-2 mb-0">
                    Check the connectivity and the connection type.
                </p>
            </div>

            @if (! $isNative)
                <div class="mt-5 nt-card p-4">
                    <div class="font-semibold">Not running in NativePHP</div>
                    <div class="nt-muted text-sm mt-1">
                        Network status works only inside the Android/iOS runtime (emulator/Jump).
                    </div>
                </div>
            @else
                <div class="mt-5 flex justify-center gap-3">
                    <button wire:click="refresh" class="nt-btn">🔄 Refresh</button>
                </div>

                @if ($statusText)
                    <div class="mt-4 text-center nt-muted text-sm">{{ $statusText }}</div>
                @endif

                @if ($error)
                    <div class="mt-4 text-center" style="color:#ff6b6b; font-size: 13px;">
                        {{ $error }}
                    </div>
                @endif

                <div class="mt-5 grid gap-3">
                    <div class="nt-card p-4">
                        <div class="font-extrabold">Current status</div>

                        <div class="mt-2 grid gap-1 text-sm">
                            <div><span class="nt-muted">Connected:</span> {{ ($status['connected'] ?? false) ? 'Yes' : 'No' }}</div>
                            <div><span class="nt-muted">Type:</span> {{ $status['type'] ?? 'unknown' }}</div>
                            <div><span class="nt-muted">Metered / expensive:</span> {{ ($status['isExpensive'] ?? false) ? 'Yes' : 'No' }}</div>
                            <div><span class="nt-muted">Low Data Mode (iOS):</span> {{ ($status['isConstrained'] ?? false) ? 'Yes' : 'No' }}</div>
                        </div>
                    </div>

                    <div class="nt-card p-4">
                        <div class="font-extrabold">Tip</div>
                        <div class="nt-muted text-sm mt-2">
                            Toggle Wi-Fi / Mobile data on the emulator/device and hit Refresh to see the values change.
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
