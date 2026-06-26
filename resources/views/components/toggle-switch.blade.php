@props([
    'name',
    'checked' => false,
    'value' => '1',
    'label' => null,
    'submitOnChange' => false,
])

<label {{ $attributes->class(['toggle-switch']) }}>
    <input
        type="checkbox"
        name="{{ $name }}"
        value="{{ $value }}"
        class="toggle-switch__input"
        @checked($checked)
        @if($submitOnChange) onchange="this.form.submit()" @endif
    />
    <span class="toggle-switch__track" aria-hidden="true">
        <span class="toggle-switch__knob"></span>
    </span>
    @if($label !== false)
        <span class="toggle-switch__label {{ $checked ? 'is-on' : 'is-off' }}">
            {{ $label ?? ($checked ? __('Active') : __('Inactive')) }}
        </span>
    @endif
</label>
