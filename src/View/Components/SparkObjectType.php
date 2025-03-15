<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SparkObjectType extends Component
{
    public string $uuid;

    public function __construct(
        public object $object,
        public bool $primary = false,
        public string $badge_name = '',
    ) {
        $this->uuid = 'mary' . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        $str = $this->object['object_type'];
        $max = 20;
        $s_pos = strpos($str, ' ');
        if (strlen(utf8_decode((string) $str)) > $max) {
            $cut = $s_pos === false || $s_pos > $max;
            $str = wordwrap($str, $max, ';;', $cut);
            $str = explode(';;', $str);
            $str = $str[0] . '...';
            $this->badge_name = $str;
        } else {
            $this->badge_name = $str;
        }

        return <<<'HTML'
            <x-m-popover>
                <x-slot:trigger>
                    <x-m-badge value="{{ $badge_name }}" class="{{  $primary === true ? 'badge-primary' :  'badge-accent' }} badge-outline my-1" />
                </x-slot:trigger>
                <x-slot:content class="{{  $primary === true ? 'bg-primary text-primary-content border-primary' :  ' text-accent border-accent' }} text-center">
                    <a href="{{route('objects.view',$object)}}">
                    <x-fas-cubes-stacked class="w-4 h-4 inline"/> {{Str::Headline($object['object_concept'])}}
                    <span class="font-bold text-base text-center">{{$object['object_type']}}</span>
                    <br/>
                    <span class="font-bold text-base text-center">{{$object['object_title']}}</span>
                    <br />
                    <span class="word-wrap">{{Str::words($object['object_content'],15," …")}}</span>
                    </a>
                </x-slot:content>
            </x-m-popover>

        HTML;
    }
}
