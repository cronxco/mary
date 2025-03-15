<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SparkEventObject extends Component
{
    public string $uuid;

    public function __construct(
        public object $object,
        public bool $primary = false,
    ) {
        $this->uuid = 'mary' . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <x-m-popover>
                <x-slot:trigger>
                    {{ $object['target']['object_title'] }}
                </x-slot:trigger>
                <x-slot:content class="{{  $primary === true ? 'bg-primary text-primary-content border-primary' :  ' text-accent border-accent' }} text-center">
                    Two
                </x-slot:content>
            </x-m-popover>

        HTML;
    }
}
