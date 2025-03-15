<?php

namespace Mary\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class SparkEventIcon extends Component
{
    public string $uuid;

    public string $class;

    public function __construct(
        public object $event,
        public string $unitsize = '5',
        public ?bool $inline = false,

    ) {
        $this->uuid = 'mary' . md5(serialize($this));
    }

    public function render(): View|Closure|string
    {

        $this->class = 'w-' . $this->unitsize . ' h-' . $this->unitsize;
        if ($this->inline == true) {
            $this->class = $this->class . ' inline';
        }

        return <<<'HTML'
            @includeFirst(['snippets.events.'.$event->event_domain.'.'.$event->event_service.'.'.$event->event_action.'.icon','snippets.events.'.$event->event_domain.'.'.$event->event_service.'.icon','snippets.events.'.$event->event_domain.'.icon', 'snippets.events.other.icon'],['class'=>$class])
        HTML;
    }
}
