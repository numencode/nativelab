@if (getenv('NATIVEPHP_PLATFORM'))
    @include('layouts.app', ['slot' => $slot])
@else
    @include('layouts.web', ['slot' => $slot])
@endif
