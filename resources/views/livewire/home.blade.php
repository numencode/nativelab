<?php

use Livewire\Volt\Component;

new class extends Component
{
    public array $items = [
        ['label' => 'Camera',  'sub' => 'Snap a photo and preview it instantly.',     'url' => '/camera',  'icon' => '📷', 'tone' => 'pink'],
        ['label' => 'Browser', 'sub' => 'Open links in-app or in system browser.',    'url' => '/browser', 'icon' => '🌐', 'tone' => 'blue'],
        ['label' => 'Dialog',  'sub' => 'Try alerts, confirms, and toast messages.',  'url' => '/dialog',  'icon' => '💬', 'tone' => 'green'],
        ['label' => 'Audio',   'sub' => 'Record voice clips and play them back.',     'url' => '/audio',   'icon' => '🎙️', 'tone' => 'pink'],
        ['label' => 'Share',   'sub' => 'Open the native share sheet.',               'url' => '/share',   'icon' => '🔁', 'tone' => 'blue'],
        ['label' => 'Device',  'sub' => 'Read info, vibrate, and toggle flashlight.', 'url' => '/device',  'icon' => '⚙️', 'tone' => 'mix'],
        ['label' => 'Network', 'sub' => 'Check online status and connection type.',   'url' => '/network', 'icon' => '📡', 'tone' => 'mix'],
        ['label' => 'System',  'sub' => 'Open this app’s system settings.',           'url' => '/system',  'icon' => '🧩', 'tone' => 'green'],
    ];
};

?>

<div class="nt-app">
    <div class="nt-hero">
        <div class="flex items-center gap-3">
            <div class="nt-logo"></div>
            <div>
                <p class="text-xl font-extrabold leading-tight m-0">NativeLab</p>
                <p class="nt-muted text-sm m-0 mt-1">NativePHP Mobile Playground</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 gap-3">
        @foreach ($items as $item)
            <a href="{{ $item['url'] }}"
               class="nt-card nt-card--{{ $item['tone'] }} flex items-center gap-3 p-4 no-underline">
                <div class="nt-ic"><span class="text-lg">{{ $item['icon'] }}</span></div>
                <div>
                    <div class="text-[15px] font-extrabold leading-tight">{{ $item['label'] }}</div>
                    <div class="nt-muted text-[12px] mt-0.5">{{ $item['sub'] }}</div>
                </div>
            </a>
        @endforeach
    </div>
</div>
