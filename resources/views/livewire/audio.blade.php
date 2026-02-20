<?php

use Illuminate\Support\Str;
use Livewire\Volt\Component;
use Native\Mobile\Facades\Microphone;
use Native\Mobile\Attributes\OnNative;
use Illuminate\Support\Facades\Storage;
use Native\Mobile\Events\Microphone\MicrophoneRecorded;

new class extends Component
{
    public bool $isNative = false;

    public string $micStatus = 'idle'; // idle | recording | paused

    public ?string $status = null;
    public ?string $error = null;

    public ?string $recorderId = null;

    public ?string $rawPath = null;        // native temp path returned by plugin
    public ?string $mimeType = 'audio/m4a';

    public ?string $savedRelative = null;  // recordings/xxx.m4a
    public ?string $playbackUrl = null;    // /storage/recordings/xxx.m4a

    public function mount(): void
    {
        $p = getenv('NATIVEPHP_PLATFORM');
        $this->isNative = (bool) $p;

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
            $this->micStatus = is_string($s) && $s !== '' ? $s : 'idle';
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function startRecording(): void
    {
        $this->status = null;
        $this->error = null;

        if (! $this->isNative) {
            $this->status = 'This action needs the Android/iOS runtime.';
            return;
        }

        try {
            $this->recorderId = 'nt-mic-' . Str::uuid()->toString();

            $ok = Microphone::record()
                ->id($this->recorderId)
                ->start();

            if (! $ok) {
                $this->status = 'Already recording (or recorder could not start).';
            } else {
                $this->status = 'Recording… speak now.';
                $this->rawPath = null;
                $this->savedRelative = null;
                $this->playbackUrl = null;
                $this->mimeType = 'audio/m4a';
            }

            $this->refreshStatus();
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function stopRecording(): void
    {
        $this->status = null;
        $this->error = null;

        if (! $this->isNative) {
            $this->status = 'This action needs the Android/iOS runtime.';
            return;
        }

        try {
            Microphone::stop();
            $this->status = 'Stopping…';

            try {
                $p = Microphone::getRecording();
                if (is_string($p) && $p !== '' && $p !== $this->rawPath) {
                    $this->persistForPlayback($p, $this->mimeType ?: 'audio/m4a');
                }
            } catch (\Throwable $ignored) {
                //
            }

            $this->refreshStatus();
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function clearRecording(): void
    {
        $this->status = null;
        $this->error = null;

        $this->rawPath = null;
        $this->savedRelative = null;
        $this->playbackUrl = null;
        $this->mimeType = 'audio/m4a';
    }

    #[OnNative(MicrophoneRecorded::class)]
    public function handleAudioRecorded(string $path, string $mimeType, ?string $id): void
    {
        if ($this->recorderId && $id && $id !== $this->recorderId) {
            return;
        }

        $this->persistForPlayback($path, $mimeType ?: 'audio/m4a');
        $this->refreshStatus();
    }

    private function persistForPlayback(string $tempPath, string $mimeType): void
    {
        $this->rawPath = $tempPath;
        $this->mimeType = $mimeType;

        $filename = 'voice-' . now()->format('Ymd-His') . '-' . substr(md5($tempPath), 0, 6) . '.m4a';
        $relative = 'recordings/' . $filename;

        $dest = storage_path('app/public/' . $relative);

        try {
            @mkdir(dirname($dest), 0775, true);

            $moved = false;

            if (class_exists(\Native\Mobile\Facades\File::class)) {
                $res = \Native\Mobile\Facades\File::move($tempPath, $dest);
                $moved = (bool)($res['success'] ?? false);

                if (! $moved) {
                    $res2 = \Native\Mobile\Facades\File::copy($tempPath, $dest);
                    $moved = (bool)($res2['success'] ?? false);
                }
            } else {
                $moved = @rename($tempPath, $dest);
                if (! $moved) {
                    $moved = @copy($tempPath, $dest);
                }
            }

            if (! $moved) {
                $this->status = 'Recorded, but could not move/copy file for playback.';
                return;
            }

            $this->savedRelative = $relative;
            $this->playbackUrl = Storage::disk('public')->url($relative);
            $this->status = 'Recording saved. Ready to play.';
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
                <h2 class="text-xl font-extrabold m-0">Microphone</h2>
                <p class="nt-muted text-sm mt-2 mb-0">
                    Record audio, stop, and play the saved file.
                </p>
            </div>

            <div class="mt-5 nt-card p-4">
                <div class="font-extrabold">Runtime</div>

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

            @if (! $isNative)
                <div class="mt-5 nt-card p-4">
                    <div class="font-semibold">Not running in NativePHP</div>
                    <div class="nt-muted text-sm mt-1">
                        Recording requires the Android/iOS runtime (permission prompts + native recorder).
                    </div>
                </div>
            @else
                <div class="mt-5 flex justify-center gap-3 flex-wrap">
                    <button
                            wire:click="startRecording"
                            class="nt-btn disabled:opacity-60 disabled:cursor-not-allowed"
                            wire:loading.attr="disabled"
                            @disabled($micStatus === 'recording')
                    >
                        🎙️ Record
                    </button>

                    <button
                            wire:click="stopRecording"
                            class="nt-card px-4 py-3 font-extrabold disabled:opacity-60 disabled:cursor-not-allowed"
                            wire:loading.attr="disabled"
                            @disabled($micStatus !== 'recording' && $micStatus !== 'paused')
                    >
                        ⏹️ Stop
                    </button>

                    <button
                            wire:click="clearRecording"
                            class="nt-card px-4 py-3 font-extrabold disabled:opacity-60 disabled:cursor-not-allowed"
                            wire:loading.attr="disabled"
                            @disabled(! $playbackUrl && ! $rawPath)
                    >
                        🧹 Clear
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

                @if ($playbackUrl)
                    <div class="mt-6 nt-card p-4">
                        <div class="font-extrabold">Playback</div>
                        <div class="nt-muted text-sm mt-2">
                            Tap play to listen to your latest recording.
                        </div>

                        <div class="mt-3">
                            <audio controls preload="metadata" style="width:100%;">
                                <source src="{{ $playbackUrl }}" type="{{ $mimeType ?? 'audio/m4a' }}">
                                Your device does not support audio playback.
                            </audio>
                        </div>

                        <div class="mt-3 text-xs nt-muted" style="word-break: break-all;">
                            <div><span class="font-semibold">URL:</span> {{ $playbackUrl }}</div>
                            @if ($savedRelative)
                                <div class="mt-1"><span class="font-semibold">Saved:</span> {{ $savedRelative }}</div>
                            @endif
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
                        The first time, the OS will prompt for microphone permission. If the user denies it, recording calls may no-op.
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
