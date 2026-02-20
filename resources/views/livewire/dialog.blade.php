<?php

use Livewire\Volt\Component;
use Native\Mobile\Facades\Dialog;
use Native\Mobile\Attributes\OnNative;
use Native\Mobile\Events\Alert\ButtonPressed;

new class extends Component
{
    public bool $isNative = false;

    public string $title = 'Confirm Action';
    public string $message = 'Are you sure you want to continue?';

    public string $toastMessage = 'Saved successfully!';

    public ?int $lastIndex = null;
    public ?string $lastLabel = null;

    public ?string $status = null;
    public ?string $error = null;

    public string $slideChoice = 'center'; // left|center|right
    public ?string $slideLastAction = null;

    public function mount(): void
    {
        $this->isNative = ! empty(env('NATIVEPHP_PLATFORM'));
    }

    public function showAlertOk(): void
    {
        $this->resetStatus();
        try {
            Dialog::alert('Hello!', 'This is a simple alert.', ['OK']);
            $this->status = 'Opened alert (1 button).';
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function showConfirm(): void
    {
        $this->resetStatus();
        try {
            Dialog::alert($this->title, $this->message, ['Cancel', 'OK']);
            $this->status = 'Opened confirm (Cancel/OK).';
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function showThreeButtons(): void
    {
        $this->resetStatus();
        try {
            Dialog::alert('3-button dialog', 'Choose one option:', ['Cancel', 'Maybe', 'Do it']);
            $this->status = 'Opened alert (3 buttons).';
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    public function showToast(): void
    {
        $this->resetStatus();
        try {
            Dialog::toast($this->toastMessage);
            $this->status = 'Toast shown.';
        } catch (\Throwable $e) {
            $this->error = $e->getMessage();
        }
    }

    #[OnNative(ButtonPressed::class)]
    public function handleButtonPressed(int $index, string $label): void
    {
        $this->lastIndex = $index;
        $this->lastLabel = $label;

        // Optional: show feedback
        try {
            Dialog::toast("Pressed: {$label}");
        } catch (\Throwable $e) {
            // ignore (toast may fail in some contexts)
        }
    }

    public function slidePicked(string $choice): void
    {
        $this->resetStatus();

        $choice = in_array($choice, ['left', 'center', 'right'], true) ? $choice : 'center';
        $this->slideChoice = $choice;
        $this->slideLastAction = match ($choice) {
            'left' => 'Selected LEFT action',
            'center' => 'Selected CENTER action',
            'right' => 'Selected RIGHT action',
        };

        // Optional: show feedback as toast in native runtime
        if ($this->isNative) {
            try {
                Dialog::toast($this->slideLastAction);
            } catch (\Throwable $e) {
                // ignore
            }
        }
    }

    private function resetStatus(): void
    {
        $this->status = null;
        $this->error = null;
    }
};

?>

<div class="nt-app">
    <div class="max-w-md mx-auto">
        <div class="nt-card p-5">
            <div class="text-center">
                <h2 class="text-xl font-extrabold m-0">Dialog</h2>
                <p class="nt-muted text-sm mt-2 mb-0">
                    Test native alerts and toasts, and capture which button was pressed.
                </p>
            </div>

            @if (! $isNative)
                <div class="mt-5 nt-card p-4">
                    <div class="font-semibold">Not running in NativePHP</div>
                    <div class="nt-muted text-sm mt-1">
                        Dialog APIs work only in the Android/iOS runtime (emulator/Jump).
                    </div>
                </div>
            @else
                {{-- Actions --}}
                <div class="mt-5 flex flex-wrap justify-center gap-3">
                    <button wire:click="showAlertOk" class="nt-btn">💡 Alert</button>
                    <button wire:click="showConfirm" class="nt-btn">✅ Confirm</button>
                    <button wire:click="showThreeButtons" class="nt-btn">🎛️ 3 Buttons</button>
                </div>

                {{-- Confirm inputs --}}
                <div class="mt-5 nt-card p-4">
                    <div class="font-extrabold">Confirm dialog content</div>

                    <div class="mt-3 grid gap-3">
                        <div>
                            <label class="block nt-muted text-xs mb-2">Title</label>
                            <input type="text" wire:model.defer="title"
                                   class="w-full rounded-2xl px-4 py-3"
                                   style="background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.10); color: rgba(255,255,255,.92); outline: none;">
                        </div>

                        <div>
                            <label class="block nt-muted text-xs mb-2">Message</label>
                            <textarea wire:model.defer="message" rows="3"
                                      class="w-full rounded-2xl px-4 py-3"
                                      style="background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.10); color: rgba(255,255,255,.92); outline: none; resize: none;"></textarea>
                        </div>

                        <div class="flex justify-center">
                            <button wire:click="showConfirm" class="nt-btn">Open Confirm</button>
                        </div>
                    </div>
                </div>

                {{-- Toast --}}
                <div class="mt-5 nt-card p-4">
                    <div class="font-extrabold">Toast</div>

                    <div class="mt-3">
                        <label class="block nt-muted text-xs mb-2">Toast message</label>
                        <input type="text" wire:model.defer="toastMessage"
                               class="w-full rounded-2xl px-4 py-3"
                               style="background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.10); color: rgba(255,255,255,.92); outline: none;">
                    </div>

                    <div class="mt-4 flex justify-center">
                        <button wire:click="showToast" class="nt-btn">Show Toast</button>
                    </div>
                </div>

                {{-- Result --}}
                <div class="mt-5 nt-card p-4">
                    <div class="font-extrabold">Last button pressed</div>

                    @if ($lastLabel !== null)
                        <div class="mt-2 text-sm">
                            <span class="nt-muted">Index:</span> {{ $lastIndex }}
                            <span class="nt-muted"> • Label:</span> <span class="font-semibold">{{ $lastLabel }}</span>
                        </div>
                    @else
                        <div class="mt-2 text-sm nt-muted">No button pressed yet.</div>
                    @endif
                </div>

                @if ($status)
                    <div class="mt-4 text-center nt-muted text-sm">{{ $status }}</div>
                @endif

                @if ($error)
                    <div class="mt-4 text-center" style="color:#ff6b6b; font-size: 13px;">
                        {{ $error }}
                    </div>
                @endif

                {{-- Slide Action --}}
                <div class="mt-5 nt-card p-4">
                    <div class="font-extrabold">Slide Action</div>
                    <div class="nt-muted text-sm mt-2">
                        Drag the handle left/center/right. On release it snaps and triggers an action (like a radio group).
                    </div>

                    <div class="mt-4">
                        <div
                            class="nt-slide"
                            x-data
                            data-slide
                        >
                            <div class="nt-slide-track">
                                <div class="nt-slide-tick nt-slide-tick--left">◀</div>
                                <div class="nt-slide-tick nt-slide-tick--center">●</div>
                                <div class="nt-slide-tick nt-slide-tick--right">▶</div>

                                <div class="nt-slide-fill" data-fill></div>

                                <div
                                    class="nt-slide-knob"
                                    data-knob
                                    role="slider"
                                    aria-label="Slide Action"
                                    aria-valuemin="0"
                                    aria-valuemax="2"
                                    aria-valuenow="{{ $slideChoice === 'left' ? 0 : ($slideChoice === 'center' ? 1 : 2) }}"
                                    tabindex="0"
                                >
                                    <span class="nt-slide-knob-dot"></span>
                                </div>
                            </div>

                            <div class="mt-3 flex items-center justify-between text-xs">
                                <span class="nt-pill">
                                    <span class="nt-muted">Choice:</span>
                                    <span class="font-extrabold" style="text-transform: uppercase;">{{ $slideChoice }}</span>
                                </span>

                                @if ($slideLastAction)
                                    <span class="nt-muted">{{ $slideLastAction }}</span>
                                @else
                                    <span class="nt-muted">Slide to trigger.</span>
                                @endif
                            </div>

                            <div class="mt-3 flex justify-center gap-2 flex-wrap">
                                <button class="nt-card px-4 py-2 font-extrabold text-sm" wire:click="slidePicked('left')">Left</button>
                                <button class="nt-card px-4 py-2 font-extrabold text-sm" wire:click="slidePicked('center')">Center</button>
                                <button class="nt-card px-4 py-2 font-extrabold text-sm" wire:click="slidePicked('right')">Right</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
