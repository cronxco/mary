<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SparkSpotlightValue extends Component
{
    public string $uuid;

    public string $unitsize_full;

    public string $eventvalue;

    public function __construct(
        public string $value,
        public string $multiplier,
        public string $units,
        public string $unitsize = 'xs',

    ) {
        $this->uuid = 'mary' . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {

        $this->unitsize_full = 'text-' . $this->unitsize;

        if ($this->multiplier != null && $this->multiplier != '0') {
            $this->eventvalue = $this->value / $this->multiplier;
        } else {
            $this->eventvalue = $this->value;
        }

        return <<<'HTML'
            @includeFirst(['snippets.units.'.strtolower($units),'snippets.units.other'],['value'=>$value,'unit'=>$eventvalue'],'unitsize'=>$unitsize_full])
        HTML;
    }
}
