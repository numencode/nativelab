<?php

use Livewire\Volt\Component;
use Native\Mobile\Facades\Device;

new class extends Component
{
    public bool $isNative = false;

    public ?string $deviceId = null;
    public array $deviceInfo = [];
    public array $batteryInfo = [];

    public ?bool $flashlightOn = null;
    public ?string $status = null;
    public ?string $error = null;

    public function mount(): void
    {
        $this->isNative = !empty(env('NATIVEPHP_PLATFORM'));

        if ($this->isNative) {
            $this->refreshAll();
        }
    }

    public function refreshAll(): void
    {
        $this->error = null;
        $this->status = null;

        try {
            $id = Device::getId();
            $info = Device::getInfo();
            $battery = Device::getBatteryInfo();

            $this->deviceId = $id ?? null;

            $this->deviceInfo = json_decode($info ?? '{}', true) ?: [];
            $this->batteryInfo = json_decode($battery ?? '{}', true) ?: [];

            $this->status = 'Refreshed.';
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function vibrate(): void
    {
        $this->error = null;
        $this->status = null;

        try {
            Device::vibrate();
            $this->status = 'Vibrated.';
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function toggleFlashlight(): void
    {
        $this->error = null;
        $this->status = null;

        try {
            $res = Device::flashlight();
            $this->flashlightOn = $res['state'] ?? null;

            $this->status = ($this->flashlightOn === true)
                ? 'Flashlight ON'
                : (($this->flashlightOn === false) ? 'Flashlight OFF' : 'Flashlight toggled.');
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function batteryPercent(): ?int
    {
        if (! isset($this->batteryInfo['batteryLevel'])) {
            return null;
        }

        return (int) round(((float) $this->batteryInfo['batteryLevel']) * 100);
    }

    public function memUsedMb(): ?int
    {
        if (! isset($this->deviceInfo['memUsed'])) {
            return null;
        }

        return (int) round(((float) $this->deviceInfo['memUsed']) / 1048576);
    }
};

?>

<div class="nt-app">
    <div class="max-w-md mx-auto">
        <div class="nt-card p-5">
            <div class="text-center">
                <h2 class="text-xl font-extrabold m-0">Device</h2>
                <p class="nt-muted text-sm mt-2 mb-0">
                    Inspect the device info, check the battery status, trigger vibration and toggle flashlight.
                </p>
            </div>

            @if (!$isNative)
                <div class="mt-5 nt-card p-4">
                    <div class="font-semibold">Not running in NativePHP</div>
                    <div class="nt-muted text-sm mt-1">
                        This screen only works inside the Android/iOS runtime (emulator/Jump).
                    </div>
                </div>
            @else
                <div class="mt-5 flex flex-wrap justify-center gap-3">
                    <button wire:click="refreshAll" class="nt-btn">🔄 Refresh</button>
                    <button wire:click="vibrate" class="nt-btn">📳 Vibrate</button>
                    <button wire:click="toggleFlashlight" class="nt-btn">
                        🔦 Flashlight
                        @if($flashlightOn === true) (ON) @elseif($flashlightOn === false) (OFF) @endif
                    </button>
                </div>

                @if ($status)
                    <div class="mt-4 text-center nt-muted text-sm">{{ $status }}</div>
                @endif

                @if ($error)
                    <div class="mt-4 text-center" style="color:#ff6b6b; font-size: 13px;">
                        {{ $error }}
                    </div>
                @endif

                <div class="mt-5 grid gap-3">
                    {{-- Device ID --}}
                    <div class="nt-card p-4">
                        <div class="font-extrabold">Device ID</div>
                        <div class="nt-muted text-xs mt-2 break-all">
                            {{ $deviceId ?? '—' }}
                        </div>
                    </div>

                    {{-- Device info --}}
                    <div class="nt-card p-4">
                        <div class="font-extrabold">Device info</div>
                        <div class="mt-2 grid gap-1 text-sm">
                            <div><span class="nt-muted">Platform:</span> {{ $deviceInfo['platform'] ?? '—' }}</div>
                            <div><span class="nt-muted">OS:</span> {{ $deviceInfo['operatingSystem'] ?? '—' }} {{ $deviceInfo['osVersion'] ?? '' }}</div>
                            <div><span class="nt-muted">Emulator:</span> {{ isset($deviceInfo['isVirtual']) ? ($deviceInfo['isVirtual'] ? 'Yes' : 'No') : '—' }}</div>
                            <div><span class="nt-muted">Memory used:</span> {{ $this->memUsedMb() !== null ? ($this->memUsedMb().' MB') : '—' }}</div>
                        </div>
                    </div>

                    {{-- Battery --}}
                    <div class="nt-card p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div class="font-extrabold">Battery</div>

                            @php
                                $pct = $this->batteryPercent();
                                $charging = isset($batteryInfo['isCharging']) ? (bool) $batteryInfo['isCharging'] : null;
                            @endphp

                            <div class="nt-pill">
                                <span>🔋</span>
                                <span class="font-semibold">{{ $pct !== null ? $pct.'%' : '—' }}</span>
                                <span class="nt-muted">•</span>
                                <span class="nt-muted">
                                    {{ $charging === null ? '—' : ($charging ? 'Charging' : 'Not charging') }}
                                </span>
                            </div>
                        </div>

                        @if ($pct !== null)
                            @php
                                // Choose a fill gradient based on battery level (low=red-ish, high=green-ish)
                                $fillBg =
                                    $pct <= 15 ? 'linear-gradient(90deg, rgba(255,70,70,.95), rgba(255,75,209,.85))' :
                                    ($pct <= 40 ? 'linear-gradient(90deg, rgba(255,75,209,.95), rgba(124,77,255,.85))' :
                                    ($pct <= 70 ? 'linear-gradient(90deg, rgba(124,77,255,.95), rgba(57,182,255,.85))' :
                                                  'linear-gradient(90deg, rgba(57,182,255,.95), rgba(44,255,178,.90))'));
                            @endphp

                            <div class="nt-meter">
                                <div class="nt-meter-fill"
                                     style="width: {{ $pct }}%; background: {{ $fillBg }};"></div>
                            </div>

                            <div class="mt-2 text-sm nt-muted">
                                Level: {{ $pct }}%
                                @if ($charging === true)
                                    <span class="nt-muted"> • Charging</span>
                                @elseif ($charging === false)
                                    <span class="nt-muted"> • Not charging</span>
                                @endif
                            </div>
                        @else
                            <div class="mt-2 text-sm nt-muted">Battery info not available.</div>
                        @endif
                    </div>

                    {{-- Full device info (collapsible-ish) --}}
                    <div class="nt-card p-4">
                        <div class="font-extrabold">Raw device info</div>
                        <pre class="mt-3 text-xs nt-muted" style="white-space: pre-wrap; word-break: break-word; margin:0;">{{ json_encode($deviceInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>

                    <div class="nt-card p-4">
                        <div class="font-extrabold">Raw battery info</div>
                        <pre class="mt-3 text-xs nt-muted" style="white-space: pre-wrap; word-break: break-word; margin:0;">{{ json_encode($batteryInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
