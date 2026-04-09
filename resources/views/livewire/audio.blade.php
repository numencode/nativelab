<?php

use Illuminate\Support\Str;
use Livewire\Volt\Component;
use App\Traits\HasNativeCheck;
use Native\Mobile\Facades\Microphone;
use Native\Mobile\Attributes\OnNative;
use Illuminate\Support\Facades\Storage;
use Native\Mobile\Events\Microphone\MicrophoneRecorded;

new class extends Component
{
    use HasNativeCheck;

    public bool $isNative = false;
    public string $micStatus = 'idle';
    public ?string $status = null;
    public ?string $error = null;
    public ?string $recorderId = null;
    public ?string $mimeType = 'audio/mpeg';
    public ?string $playbackUrl = null;

    public function mount(): void
    {
        $this->refreshStatus();
    }

    public function refreshStatus(): void
    {
        if (! $this->isNative) {
            $this->micStatus = 'idle';
            return;
        }

        try {
            $s = Microphone::getStatus();
            $this->micStatus = is_string($s) && $s !== '' ? strtolower($s) : 'idle';
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function startRecording(): void
    {
        $this->status = null;
        $this->error = null;

        if (! $this->isNative) return;

        try {
            $this->recorderId = 'nt-' . Str::random(8);
            $this->playbackUrl = null;

            $ok = Microphone::record()
                ->id($this->recorderId)
                ->start();

            $this->status = $ok ? 'Recording… speak now.' : 'Already recording (or recorder could not start).';

            // Kick off the refresh timer in the frontend
            $this->dispatch('refresh-mic-status');
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function stopRecording(): void
    {
        if (! $this->isNative) return;

        try {
            Microphone::stop();
            $this->status = 'Stopping…';
            $this->dispatch('refresh-mic-status');
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function clearRecording(): void
    {
        $this->status = null;
        $this->error = null;
        $this->playbackUrl = null;
    }

    #[OnNative(MicrophoneRecorded::class)]
    public function handleAudioRecorded(?string $path = null, ?string $mimeType = null, ?string $id = null): void
    {
        if (! $path) return;

        if ($this->recorderId && $id && $id !== $this->recorderId) {
            return;
        }

        $this->persistForPlayback($path, $mimeType ?? 'audio/mpeg');
    }

    private function persistForPlayback(string $tempPath, string $mimeType): void
    {
        $filename = 'voice-' . time() . '.m4a';
        $relative = 'recordings/' . $filename;
        $dest = storage_path('app/public/' . $relative);

        try {
            if (!file_exists(dirname($dest))) {
                @mkdir(dirname($dest), 0775, true);
            }

            if (@copy($tempPath, $dest)) {
                $this->playbackUrl = Storage::disk('public')->url($relative) . '?t=' . time();
                $this->mimeType = $mimeType;
                $this->status = 'Recording saved. Ready to play.';
            } else {
                $this->error = "Failed to copy recording to storage.";
            }
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }

        $this->refreshStatus();
    }
};

?>

<div class="nt-app" x-data x-on:refresh-mic-status.window="setTimeout(() => $wire.refreshStatus(), 500)">
    <div class="max-w-md mx-auto">
        <div class="nt-card p-5">
            <div class="text-center">
                <h2 class="text-xl font-extrabold m-0">Microphone</h2>
                <p class="nt-muted text-sm mt-2 mb-0">
                    Record audio, stop, and play the saved file.
                </p>
            </div>

            <div class="mt-5 nt-card p-4">
                <div class="font-extrabold">Runtime Status</div>

                <div class="mt-3 flex items-center justify-between">
                    <span class="nt-pill">
                        <span class="nt-muted">Mic:</span>
                        <span class="font-extrabold" style="text-transform: uppercase;">{{ $micStatus }}</span>
                    </span>

                    <button
                        wire:click="refreshStatus"
                        class="nt-card px-3 py-2 text-sm font-extrabold disabled:opacity-60 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                    >
                        ↻ Refresh
                    </button>
                </div>
            </div>

            <div class="mt-5 flex justify-center gap-3 flex-wrap">
                <button
                    wire:click="startRecording"
                    class="nt-btn disabled:opacity-60"
                    wire:loading.attr="disabled"
                    @disabled($micStatus === 'recording')
                >
                    🎙️ Record
                </button>

                <button
                    wire:click="stopRecording"
                    class="nt-card px-4 py-3 font-extrabold disabled:opacity-60"
                    wire:loading.attr="disabled"
                    @disabled($micStatus !== 'recording' && $micStatus !== 'paused')
                >
                    ⏹️ Stop
                </button>

                <button
                    wire:click="clearRecording"
                    class="nt-card px-4 py-3 font-extrabold disabled:opacity-60"
                    wire:loading.attr="disabled"
                    @disabled(! $playbackUrl)
                >
                    Sweep Clear
                </button>
            </div>

            @if ($status)
                <div class="mt-4 text-center nt-muted text-sm italic">{{ $status }}</div>
            @endif

            @if ($error)
                <div class="mt-4 text-center" style="color:#ff6b6b; font-size: 13px;">
                    {{ $error }}
                </div>
            @endif

            @if ($playbackUrl)
                <div class="mt-6 nt-card p-4" wire:key="player-container-{{ md5($playbackUrl) }}">
                    <div class="font-extrabold">Playback</div>
                    <div class="nt-muted text-sm mt-2">
                        Tap play to listen to your latest recording.
                    </div>

                    <div class="mt-3">
                        <audio controls preload="metadata" style="width:100%;" wire:key="audio-{{ md5($playbackUrl) }}">
                            <source src="{{ $playbackUrl }}" type="audio/mp4">
                            Your device does not support audio playback.
                        </audio>
                    </div>

                    <div class="mt-3 text-xs nt-muted" style="word-break: break-all;">
                        <div><span class="font-semibold">URL:</span> {{ $playbackUrl }}</div>
                    </div>
                </div>
            @else
                <div class="mt-6 nt-card p-4">
                    <div class="font-extrabold">Playback</div>
                    <div class="nt-muted text-sm mt-2">
                        No recording yet. Hit <span class="font-semibold">Record</span>, then <span class="font-semibold">Stop</span>.
                    </div>
                </div>
            @endif

            <div class="mt-6 nt-card p-4">
                <div class="font-extrabold">Notes</div>
                <div class="nt-muted text-sm mt-2">
                    If recording fails, ensure you have granted microphone permissions in your mobile settings.
                </div>
            </div>
        </div>

        @if (!$isNative)
            <div class="mt-5 nt-card p-4">
                <div class="font-semibold">Not running in NativePHP</div>
                <div class="nt-muted text-sm mt-1">
                    This plugin action needs the Android/iOS runtime in order to function properly.
                </div>
            </div>
        @endif
    </div>
</div>
