@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-display font-semibold text-xs text-slate-700 tracking-wider uppercase']) }}>
    {{ $value ?? $slot }}
</label>
