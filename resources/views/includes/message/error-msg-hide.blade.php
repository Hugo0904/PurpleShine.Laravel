@if ($errors->has($fieldName))
    <dl id="error-{{ $fieldName }}">
        <dt>{{ trans("{$type}_{$fieldName}") }}</dt>
        <dd>
            <p class="text-red">
                @foreach ($errors->get($fieldName) as $message)
                    {{ $message }}<br>
                @endforeach
            </p>
        </dd>
    </dl>
@endif