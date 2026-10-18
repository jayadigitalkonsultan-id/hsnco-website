@props(['name', 'label', 'value' => null, 'type' => 'text', 'options' => [], 'required' => false, 'help' => null, 'multiple' => false])
@php
$fieldValue = old($name, $value);
if ($fieldValue instanceof \BackedEnum) $fieldValue = $fieldValue->value;
if ($type === 'textarea' && is_array($fieldValue)) $fieldValue = json_encode($fieldValue, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
$fieldId = 'field-'.\Illuminate\Support\Str::slug($name);
$fieldName = preg_replace_callback('/\.([^.]+)/', fn ($match) => '['.$match[1].']', $name);
if ($multiple) $fieldName .= '[]';
@endphp
<div class="mb-4">
    <label class="form-label" for="{{ $fieldId }}">{{ $label }} @if($required)<span class="text-danger" aria-hidden="true">*</span>@endif</label>
    @if($type === 'textarea')
        <textarea id="{{ $fieldId }}" name="{{ $fieldName }}" {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }} rows="5" @required($required) @if($errors->has($name)) aria-invalid="true" aria-describedby="{{ $fieldId }}-error" @elseif($help) aria-describedby="{{ $fieldId }}-help" @endif>{{ $fieldValue }}</textarea>
    @elseif($type === 'select')
        <select id="{{ $fieldId }}" name="{{ $fieldName }}" {{ $attributes->class(['form-select', 'is-invalid' => $errors->has($name)]) }} @required($required) @if($multiple) multiple size="5" @endif @if($errors->has($name)) aria-invalid="true" aria-describedby="{{ $fieldId }}-error" @endif>
            @if(!$required && !$multiple)<option value="">Tidak dipilih</option>@endif
            @foreach($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected($multiple ? in_array((string) $optionValue, array_map('strval', (array) $fieldValue), true) : (string) $fieldValue === (string) $optionValue)>{{ $optionLabel }}</option>
            @endforeach
        </select>
    @else
        <input type="{{ $type }}" id="{{ $fieldId }}" name="{{ $fieldName }}" @if($type !== 'file') value="{{ $fieldValue }}" @endif {{ $attributes->class(['form-control', 'is-invalid' => $errors->has($name)]) }} @required($required) @if($errors->has($name)) aria-invalid="true" aria-describedby="{{ $fieldId }}-error" @elseif($help) aria-describedby="{{ $fieldId }}-help" @endif>
    @endif
    @error($name)<div class="invalid-feedback d-block" id="{{ $fieldId }}-error">{{ $message }}</div>@enderror
    @if($help)<p class="field-help" id="{{ $fieldId }}-help">{{ $help }}</p>@endif
</div>

