@component('layouts.public.guest')
    <div class="flex flex-col items-center justify-center py-16 px-4 w-full h-full min-h-[50vh]">
        <div class="max-w-md w-full bg-white rounded-2xl border border-amber-200/40 p-8 shadow-md text-center transition duration-200" style="padding: 2.5rem;">
            
            <div class="mb-6 flex justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-[#FF9F1A]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <h1 class="text-6xl font-extrabold text-slate-800 tracking-tighter mb-2">
                @yield('code')
            </h1>
            
            <h2 class="text-2xl font-bold text-slate-800 mb-4">
                @yield('message')
            </h2>

            <p class="text-slate-600 mb-8" style="margin-bottom: 2.5rem;">
                Oops! We're sorry, but something went wrong or the page you are looking for cannot be found.
            </p>

            <div class="flex flex-col sm:flex-row justify-center gap-4" style="margin-top: 2rem; gap: 1rem;">
                <a href="{{ url('/') }}" class="inline-flex items-center justify-center bg-[#FF9F1A] hover:bg-[#E68A00] text-white font-extrabold text-sm uppercase tracking-wider rounded-xl shadow-sm transition duration-150" style="padding: 0.875rem 1.5rem; flex: 1;">
                    Return Home
                </a>
                <button onclick="window.history.back()" class="inline-flex items-center justify-center bg-white border border-slate-300 text-slate-700 hover:text-[#FF8800] hover:bg-orange-50 font-extrabold text-sm uppercase tracking-wider rounded-xl shadow-sm transition duration-150" style="padding: 0.875rem 1.5rem; flex: 1;">
                    Go Back
                </button>
            </div>
        </div>
    </div>
@endcomponent
