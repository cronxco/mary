<?php

namespace Mary\View\Components;

use Closure;
use Exception;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\View\Component;

class ChoicesOffline extends Component
{
    public string $uuid;

    public function __construct(
        public ?string $label = null,
        public ?string $icon = null,
        public ?string $hint = null,
        public ?string $hintClass = 'label-text-alt text-base-content/50 py-1 pb-0',
        public ?bool $searchable = false,
        public ?bool $single = false,
        public ?bool $compact = false,
        public ?string $compactText = 'selected',
        public ?bool $allowAll = false,
        public ?string $debounce = '250ms',
        public ?int $minChars = 0,
        public ?string $allowAllText = 'Select all',
        public ?string $removeAllText = 'Remove all',
        public ?string $optionValue = 'id',
        public ?string $optionLabel = 'name',
        public ?string $optionSubLabel = '',
        public ?string $optionAvatar = 'avatar',
        public ?bool $valuesAsString = false,
        public ?string $height = 'max-h-64',
        public Collection|array $options = new Collection,
        public ?string $noResultText = 'No results found.',
        // Validations
        public ?string $errorField = null,
        public ?string $errorClass = 'text-error label-text-alt p-1',
        public ?bool $omitError = false,
        public ?bool $firstErrorOnly = false,
        // slots
        public mixed $item = null,
        public mixed $selection = null,
        public mixed $prepend = null,
        public mixed $append = null
    ) {
        $this->uuid = 'mary' . md5(serialize($this));

        if (($this->allowAll || $this->compact) && ($this->single || $this->searchable)) {
            throw new Exception('`allow-all` and `compact` does not work combined with `single` or `searchable`.');
        }
    }

    public function modelName(): ?string
    {
        return $this->attributes->whereStartsWith('wire:model')->first();
    }

    public function errorFieldName(): ?string
    {
        return $this->errorField ?? $this->modelName();
    }

    public function isReadonly(): bool
    {
        return $this->attributes->has('readonly') && $this->attributes->get('readonly') == true;
    }

    public function isDisabled(): bool
    {
        return $this->attributes->has('disabled') && $this->attributes->get('disabled') == true;
    }

    public function isRequired(): bool
    {
        return $this->attributes->has('required') && $this->attributes->get('required') == true;
    }

    public function getOptionValue($option): mixed
    {
        $value = data_get($option, $this->optionValue);

        if ($this->valuesAsString) {
            return "'{$value}'";
        }

        return is_numeric($value) && ! str($value)->startsWith('0') ? $value : "'{$value}'";
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
                <div x-data="{ focused: false, selection: @entangle($attributes->wire('model')) }">
                    <div
                        @click.outside = "clear()"
                        @keyup.esc = "clear()"

                        x-data="{
                            id: $id('{{ $uuid }}'),
                            options: {{ json_encode($options) }},
                            isSingle: {{ json_encode($single) }},
                            isSearchable: {{ json_encode($searchable) }},
                            isReadonly: {{ json_encode($isReadonly()) }},
                            isDisabled: {{ json_encode($isDisabled()) }},
                            isRequired: {{ json_encode($isRequired()) }},
                            minChars: {{ $minChars }},
                            noResults: false,
                            search: '',

                            init() {
                                // Fix weird issue when navigating back
                                document.addEventListener('livewire:navigating', () => {
                                    let elements = document.querySelectorAll('.mary-choices-element');
                                    elements.forEach(el =>  el.remove());
                                });
                            },
                            get selectedOptions() {
                                return this.isSingle
                                    ? this.options.filter(i => i.{{ $optionValue }} == this.selection)
                                    : this.selection.map(i => this.options.filter(o => o.{{ $optionValue }} == i)[0])
                            },
                            get isAllSelected() {
                                return this.options.length == this.selection.length
                            },
                            get isSelectionEmpty() {
                                return this.isSingle
                                    ? this.selection == null || this.selection == ''
                                    : this.selection.length == 0
                            },
                            selectAll() {
                                this.selection = this.options.map(i => i.{{ $optionValue }})
                            },
                            clear() {
                                this.focused = false;
                                this.search = ''
                            },
                            reset() {
                                this.clear();
                                this.isSingle
                                    ? this.selection = null
                                    : this.selection = []

                                this.dispatchChangeEvent({ value: this.selection })
                            },
                            focus() {
                                if (this.isReadonly || this.isDisabled) {
                                    return
                                }

                                this.focused = true
                                this.$refs.searchInput.focus()
                            },
                            isActive(id) {
                                return this.isSingle
                                    ? this.selection == id
                                    : this.selection.includes(id)
                            },
                            toggle(id, keepOpen = false) {
                                if (this.isReadonly || this.isDisabled) {
                                    return
                                }

                                if (this.isSingle) {
                                    this.selection = id
                                    this.focused = false
                                    this.search = ''
                                } else {
                                    this.selection.includes(id)
                                        ? this.selection = this.selection.filter(i => i != id)
                                        : this.selection.push(id)
                                }

                                this.dispatchChangeEvent({ value: this.selection })

                                if (!keepOpen) {
                                    this.$refs.searchInput.focus()
                                }
                            },
                            lookup() {
                                Array.from(this.$refs.choicesOptions.children).forEach(child => {
                                    if (!child.getAttribute('search-value').match(new RegExp(this.search, 'i'))){
                                        child.classList.add('hidden')
                                    } else {
                                        child.classList.remove('hidden')
                                    }
                                })

                                this.noResults = Array.from(this.$refs.choicesOptions.querySelectorAll('div > .hidden')).length ==
                                                 Array.from(this.$refs.choicesOptions.querySelectorAll('[search-value]')).length
                            },
                            dispatchChangeEvent(detail) {
                                this.$refs.searchInput.dispatchEvent(new CustomEvent('change-selection', { bubbles: true, detail }))
                            }
                        }"

                        @keydown.up="$focus.previous()"
                        @keydown.down="$focus.next()"
                    >
                        <!-- STANDARD LABEL -->
                        @if($label)
                            <label :for="id" class="pt-0 label label-text font-semibold">
                                <span>
                                    {{ $label }}

                                    @if($attributes->get('required'))
                                        <span class="text-error">*</span>
                                    @endif
                                </span>
                            </label>
                        @endif

                        <!-- PREPEND/APPEND CONTAINER -->
                        @if($prepend || $append)
                            <div class="flex">
                        @endif

                        <!-- PREPEND -->
                        @if($prepend)
                            <div class="rounded-s-lg flex items-center bg-base-200">
                                {{ $prepend }}
                            </div>
                        @endif

                        <!-- SELECTED OPTIONS + SEARCH INPUT -->
                        <div
                            @click="focus();"
                            x-ref="container"

                            {{
                                $attributes->except(['wire:model', 'wire:model.live'])->class([
                                    "select select-border  max-w-none h-fit ps-2.5 pe-16 py-1 inline-block cursor-pointer relative min-h-[40px] whitespace-normal",
                                    'border border-dashed' => $isReadonly(),
                                    'select-error' => $errors->has($errorFieldName()),
                                    'rounded-s-none' => $prepend,
                                    'rounded-e-none' => $append,
                                    'ps-10' => $icon,
                                ])
                            }}
                        >
                            <!-- ICON  -->
                            @if($icon)
                                <x-mary-icon :name="$icon" class="absolute top-1/2 -translate-y-1/2 start-3 text-base-content/50 pointer-events-none" />
                            @endif

                            <!-- CLEAR ICON  -->
                            @if(! $isReadonly() && ! $isDisabled())
                                <x-mary-icon @click="reset()"  name="o-x-mark" x-show="!isSelectionEmpty" class="absolute top-1/2 end-8 -translate-y-1/2 cursor-pointer text-base-content/50 hover:text-base-content/80" />
                            @endif

                            <!-- SELECTED OPTIONS -->
                            <span wire:key="selected-options-{{ $uuid }}">
                                @if($compact)
                                    <div class="bg-primary/5 text-primary text-sm hover:bg-primary/10 dark:bg-primary/20 dark:hover:bg-primary/40 dark:text-inherit px-2 me-2 py-1 mt-0.5 last:me-0 rounded inline-block cursor-pointer">
                                        <span class="font-black" x-text="selectedOptions.length"></span> {{ $compactText }}
                                    </div>
                                @else
                                    <template x-for="(option, index) in selectedOptions" :key="index">
                                        <div class="mary-choices-element bg-primary/5 text-primary text-sm hover:bg-primary/10 dark:bg-primary/20 dark:hover:bg-primary/40 dark:text-inherit px-2 me-2 py-1 mt-0.5 last:me-0 inline-block rounded cursor-pointer">
                                            <!-- SELECTION SLOT -->
                                             @if($selection)
                                                <span x-html="document.getElementById('selection-{{ $uuid . '-\' + option.'. $optionValue }}).innerHTML"></span>
                                             @else
                                                <span x-text="option.{{ $optionLabel }}"></span>
                                             @endif

                                            <x-mary-icon @click="toggle(option.{{ $optionValue }})" x-show="!isReadonly && !isDisabled && !isSingle" name="o-x-mark" class="text-gray-500 hover:text-error h-4 w-4" />
                                        </div>
                                    </template>
                                @endif
                            </span>

                            <!-- INPUT SEARCH -->
                            <input
                                :id="id"
                                x-ref="searchInput"
                                x-model="search"
                                @keyup="lookup()"
                                @input="focus()"
                                @keydown.arrow-down.prevent="focus()"
                                :required="isRequired && isSelectionEmpty"
                                :readonly="isReadonly || isDisabled || ! isSearchable"
                                :class="(isReadonly || isDisabled || !isSearchable || !focused) && '!w-0.5 absolute top-0'"
                                class="max-w-20 border-none outline-none ms-2"
                             />

                            <!-- PLACEHOLDER -->
                            @if (!$compact && $attributes->has('placeholder'))
                                <span @class(["absolute inset-0 mt-1.5 me-8 truncate text-base-content/50 pointer-events-none", $icon ? "ms-10" : "ms-4"]) x-show="!focused && isSelectionEmpty">
                                    {{ $attributes->get('placeholder') }}
                                </span>
                            @endif
                        </div>


                        <!-- APPEND -->
                        @if($append)
                            <div class="rounded-e-lg flex items-center bg-base-200">
                                {{ $append }}
                            </div>
                        @endif

                        <!-- END: APPEND/PREPEND CONTAINER  -->
                        @if($prepend || $append)
                            </div>
                        @endif

                        <!-- OPTIONS LIST -->
                        <div x-cloak x-show="focused" class="relative" wire:key="options-list-main-{{ $uuid }}" >
                            <div wire:key="options-list-{{ $uuid }}" class="{{ $height }} w-full absolute z-10 shadow-xl bg-base-100 border border-base-300 rounded-lg cursor-pointer overflow-y-auto" x-anchor.bottom-start="$refs.container">

                               <!-- SELECT ALL -->
                               @if($allowAll)
                                   <div
                                        wire:key="allow-all-{{ rand() }}"
                                        class="font-bold   border border-s-4 border-b-base-200 hover:bg-base-200"
                                   >
                                        <div x-show="!isAllSelected" @click="selectAll()" class="p-3 underline decoration-wavy decoration-info">{{ $allowAllText }}</div>
                                        <div x-show="isAllSelected" @click="reset()" class="p-3 underline decoration-wavy decoration-error">{{ $removeAllText }}</div>
                                   </div>
                               @endif

                                <!-- NO RESULTS -->
                                <div
                                    x-show="noResults"
                                    wire:key="no-results-{{ rand() }}"
                                    class="p-3 decoration-wavy decoration-warning underline font-bold border border-s-4 border-s-warning border-b-base-200"
                                >
                                    {{ $noResultText }}
                                </div>

                                <div x-ref="choicesOptions">
                                    @foreach($options as $option)
                                        <div
                                            id="option-{{ $uuid }}-{{ data_get($option, $optionValue) }}"
                                            wire:key="option-{{ data_get($option, $optionValue) }}"
                                            @click="toggle({{ $getOptionValue($option) }}, true)"
                                            @keydown.enter="toggle({{ $getOptionValue($option) }}, true)"
                                            :class="isActive({{ $getOptionValue($option) }}) && 'border-s-4 border-s-primary'"
                                            search-value="{{ data_get($option, $optionLabel) }}"
                                            class="border-s-4 border-base-300 focus:bg-base-200 focus:outline-none"
                                            tabindex="0"
                                        >
                                            <!-- ITEM SLOT -->
                                            @if($item)
                                                {{ $item($option) }}
                                            @else
                                                <x-mary-list-item :item="$option" :value="$optionLabel" :sub-value="$optionSubLabel" :avatar="$optionAvatar" />
                                            @endif

                                            <!-- SELECTION SLOT -->
                                            @if($selection)
                                                <span id="selection-{{ $uuid }}-{{ data_get($option, $optionValue) }}" class="hidden">
                                                    {{ $selection($option) }}
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- ERROR -->
                        @if(!$omitError && $errors->has($errorFieldName()))
                            @foreach($errors->get($errorFieldName()) as $message)
                                @foreach(Arr::wrap($message) as $line)
                                    <div class="{{ $errorClass }}" x-classes="text-error label-text-alt p-1">{{ $line }}</div>
                                    @break($firstErrorOnly)
                                @endforeach
                                @break($firstErrorOnly)
                            @endforeach
                        @endif

                        <!-- HINT -->
                        @if($hint)
                            <div class="{{ $hintClass }}" x-classes="label-text-alt text-base-content/50 py-1 pb-0">{{ $hint }}</div>
                        @endif
                    </div>
                </div>
            HTML;
    }
}
