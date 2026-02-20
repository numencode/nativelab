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

<native:side-nav gestures-enabled="false" dark>
    <native:side-nav-header
            title="NativeLab"
            subtitle="NativePHP Mobile Playground"
            icon="apps"
            background-color="#0B1020"
            text-color="#FFFFFF"
            show-close-button="true"
    />

    <native:side-nav-item id="home"    label="Home"                  icon="home"        url="/"        :active="request()->is('/')"/>
    <native:side-nav-item id="camera"  label="Camera"                icon="camera_alt"  url="/camera"  :active="request()->is('camera')"/>
    <native:side-nav-item id="browser" label="Browser (Open URL)"    icon="language"    url="/browser" :active="request()->is('browser')"/>
    <native:side-nav-item id="audio"   label="Audio (Record + Play)" icon="mic"         url="/audio"   :active="request()->is('audio')"/>
    <native:side-nav-item id="dialog"  label="Dialog"                icon="chat"        url="/dialog"  :active="request()->is('dialog')"/>
    <native:side-nav-item id="share"   label="Share"                 icon="share"       url="/share"   :active="request()->is('share')"/>
    <native:side-nav-item id="device"  label="Device Info"           icon="settings"    url="/device"  :active="request()->is('device')"/>
    <native:side-nav-item id="network" label="Network"               icon="wifi"        url="/network" :active="request()->is('network')"/>
    <native:side-nav-item id="system"  label="System"                icon="apps"        url="/system"  :active="request()->is('system')"/>
</native:side-nav>

<native:top-bar
        title="NativeLab"
        subtitle="NativePHP Mobile Playground"
        background-color="#0B1020"
        text-color="#FFFFFF"
        elevation="10"
>
    <native:top-bar-action id="home" icon="home" label="Home" url="/" />
    <native:top-bar-action id="settings" icon="settings" label="Settings" url="/system" />
</native:top-bar>

<div id="app-root">
    {{ $slot }}
</div>

<native:bottom-nav label-visibility="labeled" dark>
    <native:bottom-nav-item id="home"    icon="home"       label="Home"    url="/"        :active="request()->is('/')" />
    <native:bottom-nav-item id="camera"  icon="camera_alt" label="Camera"  url="/camera"  :active="request()->is('camera')" />
    <native:bottom-nav-item id="browser" icon="language"   label="Browser" url="/browser" :active="request()->is('browser')" />
    <native:bottom-nav-item id="audio"   icon="mic"        label="Audio"   url="/audio"   :active="request()->is('audio')" />
    <native:bottom-nav-item id="system"  icon="apps"       label="More"    url="/system"  :active="request()->is('system')" />
</native:bottom-nav>

@livewireScripts
</body>
</html>
