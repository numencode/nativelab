<?php

namespace App\Traits;

trait HasNativeCheck
{
    public bool $isNative = false;

    /**
     * In Livewire, 'mountHasNativeCheck' will automatically run
     * when the component that uses this trait is mounted.
     */
    public function mountHasNativeCheck(): void
    {
        $this->isNative = ! empty(getenv('NATIVEPHP_PLATFORM'));
    }
}
