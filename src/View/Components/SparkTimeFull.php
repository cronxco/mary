<?php

namespace Mary\View\Components;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SparkTimeFull extends Component
{
    public string $uuid;

    public function __construct(
        public Carbon $time,

    ) {
        $this->uuid = 'mary' . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {

        return <<<'HTML'
            <x-m-popover>
                <x-slot:trigger>
                    {{Carbon::parse($time)->timezone('Europe/London')->format('d/m/y H:i')}} ({{Carbon::parse($time)->diffForHumans()}})
                </x-slot:trigger>
                <x-slot:content class="text-sm">
                    {{Carbon::parse($time)->format('D j M Y, H:i:s T')}}
                </x-slot:content>
            </x-m-popover>
        HTML;
    }
}
