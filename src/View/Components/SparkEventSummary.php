<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SparkEventSummary extends Component
{
    public string $uuid;

    public string $unitsize_full;

    public string $value;

    public ?int $min;

    public ?int $max;
    // public string $today;

    public function __construct(
        public array $event,
        public string $unitsize = 'xs',

    ) {
        $this->uuid = 'mary' . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {

        $this->unitsize_full = 'text-' . $this->unitsize;

        if ($this->event['event_value_unit'] == 'seconds' && $this->event['event_value_multiplier'] != null) {
            $this->value = $this->event['avg_value'] / $this->event['event_value_multiplier'] / 1000;
            $this->min = $this->event['min_value'] / $this->event['event_value_multiplier'] / 1000;
            $this->max = $this->event['max_value'] / $this->event['event_value_multiplier'] / 1000;
        } elseif ($this->event['event_value_unit'] == 'seconds') {
            $this->value = $this->event['avg_value'] / 1000;
            $this->min = $this->event['min_value'] / 1000;
            $this->max = $this->event['max_value'] / 1000;
        } elseif ($this->event['event_value_unit'] == 'min' && $this->event['event_value_multiplier'] != null) {
            $this->value = $this->event['avg_value'] / $this->event['event_value_multiplier'] / 60000;
            $this->min = $this->event['min_value'] / $this->event['event_value_multiplier'] / 60000;
            $this->max = $this->event['max_value'] / $this->event['event_value_multiplier'] / 60000;
        } elseif ($this->event['event_value_unit'] == 'min') {
            $this->value = $this->event['avg_value'] / 60000;
            $this->min = $this->event['min_value'] / 60000;
            $this->max = $this->event['max_value'] / 60000;
        } elseif ($this->event['event_value_unit'] == 'hr' && $this->event['event_value_multiplier'] != null) {
            $this->value = $this->event['avg_value'] / $this->event['event_value_multiplier'] / 3600000;
            $this->min = $this->event['min_value'] / $this->event['event_value_multiplier'] / 3600000;
            $this->max = $this->event['max_value'] / $this->event['event_value_multiplier'] / 3600000;
        } elseif ($this->event['event_value_unit'] == 'hr') {
            $this->value = $this->event['avg_value'] / 3600000;
            $this->min = $this->event['min_value'] / 3600000;
            $this->max = $this->event['max_value'] / 3600000;
        } elseif ($this->event['event_value_multiplier'] != null) {
            $this->value = $this->event['avg_value'] / $this->event['event_value_multiplier'];
            $this->min = $this->event['min_value'] / $this->event['event_value_multiplier'];
            $this->max = $this->event['max_value'] / $this->event['event_value_multiplier'];
        } else {
            $this->value = $this->event['avg_value'];
            $this->min = $this->event['min_value'];
            $this->max = $this->event['max_value'];
        }

        return <<<'HTML'
            @includeFirst(['snippets.units.'.strtolower($event['event_value_unit']),'snippets.units.other'],['value'=>$value,'unit'=>$event['event_value_unit'],'unitsize'=>$unitsize_full])
            <span class="{{$unitsize_full}}">(@includeFirst(['snippets.units.'.strtolower($event['event_value_unit']),'snippets.units.other'],['value'=>$min,'unit'=>$event['event_value_unit'],'unitsize'=>$unitsize_full]) &mdash; @includeFirst(['snippets.units.'.strtolower($event['event_value_unit']),'snippets.units.other'],['value'=>$max,'unit'=>$event['event_value_unit'],'unitsize'=>$unitsize_full]))</span>
        HTML;
    }
}
