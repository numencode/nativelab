<?php

use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Native\Mobile\Facades\Camera;
use Native\Mobile\Events\Camera\PhotoTaken;

new class extends Component
{
    public ?string $photoPath = null;
    public ?string $photoDataUrl = null;

    public function takePhoto(): void
    {
        Camera::getPhoto()->id('playground-photo');
    }

    #[On('native:' . PhotoTaken::class)]
    public function handlePhotoTaken(string $path, ?string $id = null): void
    {
        if ($id !== null && $id !== 'playground-photo') {
            return;
        }

        $this->photoPath = $path;

        $this->photoDataUrl = $this->makeDataUrlFromPath($path);
    }

    protected function makeDataUrlFromPath(string $path): ?string
    {
        if (!is_file($path) || !is_readable($path)) {
            return null;
        }

        $bytes = @file_get_contents($path);
        if ($bytes === false || $bytes === '') {
            return null;
        }

        $base64 = base64_encode($bytes);

        return 'data:image/jpeg;base64,' . $base64;
    }
};

?>

<div class="nt-app">
    <div class="max-w-md mx-auto">
        <div class="nt-card p-5">
            <div class="text-center">
                <h2 class="text-xl font-extrabold m-0">Camera Playground</h2>
                <p class="nt-muted text-sm mt-2 mb-0">
                    Take a photo using the native<br>camera and instantly preview it below.
                </p>
            </div>

            <div class="mt-5 flex justify-center">
                <button wire:click="takePhoto" class="nt-btn">
                    📷 Take a photo
                </button>
            </div>

            <div class="mt-5 text-center">
                @if ($photoDataUrl)
                    <div class="text-base font-semibold mt-1">
                        Here's your picture!
                    </div>

                    <div class="nt-muted text-xs mt-2 break-all">
                        Path: {{ $photoPath }}
                    </div>

                    <div class="mt-4 flex justify-center">
                        <img src="{{ $photoDataUrl }}" class="nt-img" />
                    </div>
                @else
                    <p class="nt-muted mt-4 mb-0">No photo yet.</p>
                @endif
            </div>
        </div>
    </div>
</div>

