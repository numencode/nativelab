<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NativeLab</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    <style>
        :root{
            --web-topbar-h: 56px;
            --web-bottomnav-h: 72px;
            --web-bar-bg: #0B1020;
            --web-bar-text: rgba(255,255,255,.95);
            --web-bar-muted: rgba(255,255,255,.70);
            --web-border: rgba(255,255,255,.08);
        }

        /* Fixed bars */
        .web-topbar{
            position: fixed;
            top: 0; left: 0; right: 0;
            height: var(--web-topbar-h);
            background: var(--web-bar-bg);
            color: var(--web-bar-text);
            border-bottom: 1px solid var(--web-border);
            z-index: 9999;
            display: flex;
            align-items: center;
            padding: 0 14px;
        }

        .web-topbar .title{
            font-weight: 900;
            font-size: 14px;
            line-height: 1.1;
            margin: 0;
        }

        .web-topbar .subtitle{
            margin: 2px 0 0;
            font-size: 11px;
            color: var(--web-bar-muted);
            line-height: 1.1;
        }

        .web-topbar .actions{
            margin-left: auto;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .web-topbar .action-btn{
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: 12px;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.10);
            color: rgba(255,255,255,.92);
            text-decoration: none;
            font-size: 16px;
            font-weight: 900;
        }

        .web-bottomnav{
            position: fixed;
            left: 0; right: 0; bottom: 0;
            height: var(--web-bottomnav-h);
            background: var(--web-bar-bg);
            border-top: 1px solid var(--web-border);
            z-index: 9999;
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            padding: 8px 10px 10px;
            gap: 6px;
        }

        .web-bottomnav a{
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            text-decoration: none;
            color: rgba(255,255,255,.70);
            border-radius: 14px;
            padding: 8px 6px;
            border: 1px solid transparent;
            user-select: none;
        }

        .web-bottomnav a .icon{
            font-size: 18px;
            line-height: 1;
        }

        .web-bottomnav a .label{
            font-size: 11px;
            font-weight: 800;
            line-height: 1;
        }

        .web-bottomnav a.active{
            color: rgba(255,255,255,.95);
            background: rgba(255,255,255,.06);
            border-color: rgba(255,255,255,.10);
        }

        /* Push content so it doesn't sit under bars */
        #app-root{
            padding-top: var(--web-topbar-h);
            padding-bottom: var(--web-bottomnav-h);
        }
    </style>
</head>

<body class="web" style="margin:0; font-family: system-ui, sans-serif;">

<!-- Fake Top Bar -->
<header class="web-topbar">
    <div>
        <p class="title">NativeLab</p>
        <p class="subtitle">NativePHP Mobile Playground</p>
    </div>

    <div class="actions">
        <a class="action-btn" href="/" title="Home">🏠</a>
        <a class="action-btn" href="/device" title="Settings">⚙️</a>
    </div>
</header>

<div id="app-root">
    {{ $slot }}
</div>

<!-- Fake Bottom Nav -->
<nav class="web-bottomnav">
    <a href="/" class="{{ request()->is('/') ? 'active' : '' }}">
        <div class="icon">🏠</div>
        <div class="label">Home</div>
    </a>

    <a href="/camera" class="{{ request()->is('camera') ? 'active' : '' }}">
        <div class="icon">📷</div>
        <div class="label">Camera</div>
    </a>

    <a href="/browser" class="{{ request()->is('browser') ? 'active' : '' }}">
        <div class="icon">🌐</div>
        <div class="label">Browser</div>
    </a>

    <a href="/audio" class="{{ request()->is('audio') ? 'active' : '' }}">
        <div class="icon">🎙️</div>
        <div class="label">Audio</div>
    </a>

    <a href="/system" class="{{ request()->is('system') ? 'active' : '' }}">
        <div class="icon">🧩</div>
        <div class="label">More</div>
    </a>
</nav>

@livewireScripts
</body>
</html>
