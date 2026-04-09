<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>NativeLab</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body style="margin:0; font-family: system-ui, sans-serif;">

<native:top-bar
        title="NativeLab"
        subtitle="NativePHP Mobile Playground"
        background-color="#0B1020"
        text-color="#FFFFFF"
        elevation="10"
        :show-navigation-icon="false"
>
    <native:top-bar-action id="home" icon="home" label="Home" url="/" />
    <native:top-bar-action id="settings" icon="settings" label="Settings" url="/system" />
</native:top-bar>

<div id="app-root">
    {{ $slot }}
</div>

<native:bottom-nav label-visibility="labeled" dark>
    <native:bottom-nav-item id="home"   icon="home"       label="Home"   url="/"       :active="request()->is('/')" />
    <native:bottom-nav-item id="dialog" icon="chat"       label="Dialog" url="/dialog" :active="request()->is('dialog')" />
    <native:bottom-nav-item id="camera" icon="camera_alt" label="Camera" url="/camera" :active="request()->is('camera')" />
    <native:bottom-nav-item id="device" icon="smartphone" label="Device" url="/device" :active="request()->is('device')" />
    <native:bottom-nav-item id="system" icon="settings"   label="System" url="/system" :active="request()->is('system')" />
</native:bottom-nav>

@livewireScripts
</body>
</html>
