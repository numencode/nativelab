<?php

use Livewire\Volt\Component;
use App\Services\ConnectivityService;

new class extends Component
{
    /**
     * Component State
     */
    public bool $isNative = false;
    public array $status = [];
    public ?string $statusText = null;
    public ?string $error = null;

    /**
     * Initialize the component.
     */
    public function mount(): void
    {
        // Check if we are running within the NativePHP mobile runtime
        $this->isNative = (bool) (getenv('NATIVEPHP_PLATFORM') ?: false);

        if ($this->isNative) {
            // We resolve the service via Laravel's container manually in mount
            $this->refresh(app(ConnectivityService::class));
        }
    }

    /**
     * Refresh the network status using our ConnectivityService.
     */
    public function refresh(ConnectivityService $connectivity): void
    {
        $this->error = null;
        $this->statusText = null;

        try {
            // Get the comprehensive status (Native API + Ping Fallback)
            $data = $connectivity->status();

            $this->status = [
                'connected'     => (bool) $data['connected'],
                'type'          => (string) $data['type'],
                'isExpensive'   => (bool) $data['isExpensive'],
                'isConstrained' => (bool) $data['isConstrained'],
                'isVirtual'     => (bool) ($data['isVirtual'] ?? false),
            ];

            if (!$this->status['connected']) {
                $this->statusText = 'No internet connection detected';
            } else {
                $typeDisplay = $this->status['isVirtual'] ? 'Emulator' : $this->status['type'];
                $metered = $this->status['isExpensive'] ? ' (metered)' : '';

                $this->statusText = "Connected via " . ucfirst($typeDisplay) . $metered;
            }
        } catch (\Throwable $e) {
            $this->error = "Connectivity Error: " . $e->getMessage();
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
                    Check connectivity and connection type.
                </p>
            </div>

            @if (! $isNative)
                {{-- Shown when viewing in a standard web browser --}}
                <div class="mt-5 nt-card p-4 border-l-4 border-blue-400 bg-blue-50/50">
                    <div class="font-semibold text-blue-900">Web Browser Mode</div>
                    <div class="nt-muted text-sm mt-1">
                        Native network features (like metering and connection types) are only available inside the Android/iOS runtime.
                    </div>
                </div>
            @else
                {{-- Refresh Controls --}}
                <div class="mt-5 flex justify-center gap-3">
                    <button
                        wire:click="refresh"
                        wire:loading.attr="disabled"
                        class="nt-btn flex items-center gap-2"
                    >
                        <span wire:loading.remove>🔄 Refresh Status</span>
                        <span wire:loading>⌛ Checking...</span>
                    </button>
                </div>

                {{-- Primary Status Label --}}
                @if ($statusText)
                    <div class="mt-4 text-center font-bold {{ $status['connected'] ? 'text-green-600' : 'text-red-500' }}">
                        {{ $statusText }}
                    </div>
                @endif

                {{-- Error Reporting --}}
                @if ($error)
                    <div class="mt-4 p-3 rounded bg-red-50 text-center text-red-600 text-xs border border-red-100">
                        <strong>Error:</strong> {{ $error }}
                    </div>
                @endif

                {{-- Detailed Data Cards --}}
                <div class="mt-5 grid gap-3">
                    <div class="nt-card p-4">
                        <div class="font-extrabold text-xs uppercase tracking-wider text-gray-500 mb-3">
                            Device Diagnostics
                        </div>

                        <div class="grid gap-2 text-sm">
                            <div class="flex justify-between border-b border-gray-100 pb-1">
                                <span class="nt-muted">Internet Reachable:</span>
                                <span class="font-bold {{ ($status['connected'] ?? false) ? 'text-green-600' : 'text-red-500' }}">
                                    {{ ($status['connected'] ?? false) ? 'YES' : 'NO' }}
                                </span>
                            </div>
                            <div class="flex justify-between border-b border-gray-100 pb-1">
                                <span class="nt-muted">Connection Type:</span>
                                <span class="font-mono">{{ strtoupper($status['type'] ?? 'unknown') }}</span>
                            </div>
                            <div class="flex justify-between border-b border-gray-100 pb-1">
                                <span class="nt-muted">Metered / Expensive:</span>
                                <span>{{ ($status['isExpensive'] ?? false) ? 'Yes' : 'No' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="nt-muted">Low Data Mode:</span>
                                <span>{{ ($status['isConstrained'] ?? false) ? 'Active' : 'Inactive' }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="nt-card p-4 bg-gray-50 italic">
                        <div class="font-extrabold not-italic text-xs text-gray-400 mb-1">PRO TIP</div>
                        <div class="nt-muted text-xs">
                            Toggle Airplane Mode or Wi-Fi on your device/emulator and tap refresh. This view uses a double-check system (Native API + Ping) for 100% accuracy.
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
