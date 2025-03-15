<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SparkEventValue extends Component
{
    public string $uuid;

    public string $unitsize_full;

    public string $value;

    public function __construct(
        public object $event,
        public string $unitsize = 'xs',

    ) {
        $this->uuid = 'mary' . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {

        $this->unitsize_full = 'text-' . $this->unitsize;

        if ($this->event['event_value_multiplier'] != null) {
            $this->value = $this->event['event_value'] / $this->event['event_value_multiplier'];
        } else {
            $this->value = $this->event['event_value'];
        }

        return <<<'HTML'
            @includeFirst(['snippets.units.'.strtolower($event['event_value_unit']),'snippets.units.other'],['value'=>$value,'unit'=>$event['event_value_unit'],'unitsize'=>$unitsize_full])
        HTML;
    }
}
