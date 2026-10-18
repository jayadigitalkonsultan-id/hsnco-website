@php($schema = config('cms.section_fields.'.$section['type'], []))
@foreach($section['content'] ?? [] as $key => $value)
    @if(isset($schema[$key]) && $schema[$key]['type'] === 'rich_text')
        @if(is_string($value) && $value !== '')<h4>{{ $schema[$key]['label'] }}</h4><x-frontend.rich-text class="rich-content" :html="$value" />@endif
    @elseif(isset($schema[$key]) && $schema[$key]['type'] === 'items' && is_array($value))
        <h4>{{ $schema[$key]['label'] }}</h4>
        @foreach($value as $item)<div class="border rounded p-3 my-2">@foreach($schema[$key]['items'] as $itemKey => $itemField)
            @if(($itemField['type'] ?? '') === 'rich_text')<x-frontend.rich-text class="rich-content" :html="$item[$itemKey] ?? ''" />
            @elseif(isset($item[$itemKey]) && $item[$itemKey] !== '')<p><strong>{{ $itemField['label'] }}:</strong> {{ is_scalar($item[$itemKey]) ? $item[$itemKey] : json_encode($item[$itemKey], JSON_UNESCAPED_UNICODE) }}</p>@endif
        @endforeach</div>@endforeach
    @elseif(is_array($value))
        <h4>{{ $schema[$key]['label'] ?? \Illuminate\Support\Str::headline($key) }}</h4><pre class="text-break">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
    @elseif($value !== null && $value !== '')
        <p><strong>{{ $schema[$key]['label'] ?? \Illuminate\Support\Str::headline($key) }}:</strong> {{ $value }}</p>
    @endif
@endforeach
