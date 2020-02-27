<dl>
    <dt>{{ trans("{$type}_{$fieldName}") }}</dt>
    <dd>{!! trans($type . '.note_msg.' . ($msgName ?? $fieldName)) !!}</dd>
    <dd>
        <p id="error-{{ $fieldName }}" class="text-red">
            @foreach ($errors->get($fieldName) as $message)
                {{ $message }}<br>
            @endforeach
        </p>
    </dd>
</dl>