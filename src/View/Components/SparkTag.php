<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SparkTag extends Component
{
    public string $uuid;

    public function __construct(
        public array $tag,
        public string $tag_name = '',
        public ?bool $full = false,
        public ?bool $fill = false,
    ) {
        $this->uuid = 'mary' . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {
        $str = $this->tag['name']['en'];
        $max = 20;
        $s_pos = strpos($str, ' ');
        if ($this->full == true) {
            $this->tag_name = $str;
        } elseif (strlen(utf8_decode((string) $str)) > $max) {
            $cut = $s_pos === false || $s_pos > $max;
            $str = wordwrap($str, $max, ';;', $cut);
            $str = explode(';;', $str);
            $str = $str[0] . '...';
            $this->tag_name = $str;
        } else {
            $this->tag_name = $str;
        }

        return <<<'HTML'
            <x-m-popover>
                <x-slot:trigger>
                    <x-m-badge class="badge-secondary {{  $fill === true ? 'text-secondary-content' :  'badge-outline' }}  my-1" >
                        <x-slot:value>
                            {{$tag_name}}
                        </x-slot:value>
                    </x-m-badge>
                </x-slot:trigger>
                <x-slot:content class="bg-secondary text-secondary-content text-center border-secondary/40">
                    <a href="{{route('tags.view', [$tag['type'], $tag['slug']['en'], $tag['id']])}}">
                        <x-fas-tags class="w-4 h-4 inline"/> {{Str::Headline($tag['type'])}}
                        <br/>
                        <span class="font-bold text-base text-center">{{$tag['name']['en']}}</span>
                    </a>
                </x-slot:content>
            </x-m-popover>

        HTML;
    }
}
