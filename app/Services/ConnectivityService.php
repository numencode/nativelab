<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Native\Mobile\Facades\Device;
use Native\Mobile\Facades\Network;

class ConnectivityService
{
    /**
     * Get the full diagnostic status of the connection.
     */
    public function status(): array
    {
        $isVirtual = $this->isVirtualDevice();

        try {
            // 1. Get status from the NativePHP bridge
            $status = Network::status();
            $connected = (bool) ($status->connected ?? false);

            // 2. Fallback: If plugin says offline, try a manual "heartbeat" check
            if (! $connected && $this->canReachInternet()) {
                $connected = true;
            }

            return [
                'connected' => $connected,
                'type' => $isVirtual ? 'emulator' : (string) ($status->type ?? 'unknown'),
                'isExpensive' => (bool) ($status->isExpensive ?? false),
                'isConstrained' => (bool) ($status->isConstrained ?? false),
                'isVirtual' => $isVirtual,
            ];
        } catch (\Throwable $e) {
            // 3. Absolute Fallback: If the Native plugin fails entirely
            return [
                'connected' => $this->canReachInternet(),
                'type' => $isVirtual ? 'emulator' : 'unknown',
                'isExpensive' => false,
                'isConstrained' => false,
                'isVirtual' => $isVirtual,
            ];
        }
    }

    /**
     * Quick check for boolean online status.
     */
    public function isOnline(): bool
    {
        return $this->status()['connected'];
    }

    /**
     * Checks if the device is an emulator/simulator.
     */
    protected function isVirtualDevice(): bool
    {
        try {
            $info = json_decode(Device::getInfo(), true);

            return (bool) ($info['isVirtual'] ?? false);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Verifies actual internet reachability.
     */
    protected function canReachInternet(): bool
    {
        try {
            // Ping a reliable host (or your own API base URL)
            $response = Http::timeout(3)->get('https://8.8.8.8');

            return $response->successful();
        } catch (\Throwable $e) {
            return false;
        }
    }
}
