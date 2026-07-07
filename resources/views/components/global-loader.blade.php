<div 
    x-data="{ loading: false }"
    x-init="
        Livewire.hook('commit', ({ component, commit, respond, succeed, fail }) => {
            loading = true;
            succeed(() => { loading = false; });
            fail(() => { loading = false; });
        });
    "
    x-show="loading"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    style="display: none;"
    class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm"
>
    <div class="flex flex-col items-center justify-center space-y-4 bg-white p-8 rounded-2xl shadow-2xl border border-amber-200/50">
        <div class="relative w-16 h-16">
            <div class="absolute inset-0 rounded-full border-4 border-slate-100"></div>
            <div class="absolute inset-0 rounded-full border-4 border-[#FF9F1A] border-t-transparent animate-spin"></div>
            <div class="absolute inset-4 rounded-full bg-amber-50 animate-pulse"></div>
        </div>
        <div class="text-slate-600 font-semibold text-sm tracking-widest uppercase">
            {{ __('Processing...') }}
        </div>
    </div>
</div>
