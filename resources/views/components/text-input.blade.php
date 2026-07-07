@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bg-white border border-slate-300 text-slate-900 placeholder-slate-400 focus:border-[#FF8800] focus:ring-1 focus:ring-[#FF8800] rounded-xl shadow-sm transition-all duration-200']) }}>
