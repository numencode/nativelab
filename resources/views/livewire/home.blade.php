<?php

use Livewire\Volt\Component;

new class extends Component
{
    public array $items = [
        ['label' => 'Camera',  'sub' => 'Capture photos and manage image previews.',  'url' => '/camera',  'icon' => '📸', 'tone' => 'pink'],
        ['label' => 'Browser', 'sub' => 'Launch external links or in-app webviews.',  'url' => '/browser', 'icon' => '🌐', 'tone' => 'blue'],
        ['label' => 'Dialog',  'sub' => 'Test native alerts, confirms, and toasts.',  'url' => '/dialog',  'icon' => '🔔', 'tone' => 'green'],
        ['label' => 'Audio',   'sub' => 'Capture voice clips with native recording.', 'url' => '/audio',   'icon' => '🎙️', 'tone' => 'pink'],
        ['label' => 'Share',   'sub' => 'Trigger the system-level share sheet.',      'url' => '/share',   'icon' => '📤', 'tone' => 'blue'],
        ['label' => 'Device',  'sub' => 'Access hardware info and haptic feedback.',  'url' => '/device',  'icon' => '📱', 'tone' => 'mix'],
        ['label' => 'Network', 'sub' => 'Monitor real-time connectivity status.',     'url' => '/network', 'icon' => '📶', 'tone' => 'mix'],
        ['label' => 'System',  'sub' => 'Manage app permissions and OS settings.',    'url' => '/system',  'icon' => '⚙️', 'tone' => 'green'],
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
