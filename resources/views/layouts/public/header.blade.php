<header class="w-full bg-white shadow-[0_4px_20px_-10px_rgba(0,0,0,0.1)] relative z-50">
    <!-- Top Accessibility Bar (Deep Navy) -->
    <div class="bg-[#1B365D] px-4 md:px-8 py-2 flex items-center justify-between text-white/90">
        <!-- Left Side: Font Size Adjusters & Screen Reader -->
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-1.5 bg-white/10 rounded-md p-1">
                <button id="btn-decrease-text" title="Decrease Text Size" class="w-7 h-7 flex items-center justify-center text-xs font-bold border border-transparent hover:bg-white/20 hover:text-white rounded transition duration-150">
                    A<sup>-</sup>
                </button>
                <div class="w-px h-4 bg-white/20"></div>
                <button id="btn-reset-text" title="Normal Text Size" class="w-7 h-7 flex items-center justify-center text-xs font-bold border border-transparent hover:bg-white/20 hover:text-white rounded transition duration-150">
                    A
                </button>
                <div class="w-px h-4 bg-white/20"></div>
                <button id="btn-increase-text" title="Increase Text Size" class="w-7 h-7 flex items-center justify-center text-xs font-bold border border-transparent hover:bg-white/20 hover:text-white rounded transition duration-150">
                    A<sup>+</sup>
                </button>
            </div>
            
            
            
           
        </div>

        <!-- Right Side: Dark/Contrast Theme Toggle Switch -->
        <div class="flex items-center gap-3">
            <span class="text-xs font-medium hidden sm:inline-block text-white/80"></span>
            <button id="contrast-toggle" class="relative inline-flex h-6 w-12 shrink-0 cursor-pointer rounded-full border border-transparent transition-colors duration-200 ease-in-out focus:outline-none bg-black/40 items-center ring-1 ring-white/20 hover:ring-white/40" role="switch" aria-checked="false">
                <span class="sr-only">Toggle Contrast Mode</span>
                <!-- Toggle Circle Indicator -->
                <span id="contrast-toggle-circle" class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow transition duration-200 ease-in-out" style="transform: translateX(4px);"></span>
                <!-- Sun Icon (always visible on the right side) -->
                <span class="absolute right-1.5 text-yellow-400 select-none pointer-events-none text-[10px] flex items-center justify-center">☀️</span>
            </button>
        </div>
    </div>

    <!-- Main Header Content -->
    <div class="w-full px-4 md:px-12 py-4 md:py-5 flex flex-col md:flex-row items-center justify-between gap-6">
        
        <!-- Left: Emblem & Gov Text -->
        <div class="flex-1 flex flex-col sm:flex-row items-center gap-4 md:gap-5 text-center sm:text-left justify-start">
            <!-- Government Text -->
            <div class="flex flex-col justify-center">
                <h2 class="text-[#E66200] font-bold text-lg md:text-xl leading-tight font-display mb-0.5">
                    পশ্চিমবঙ্গ সরকার
                </h2>
                <h3 class="text-[#1B365D] font-extrabold text-sm md:text-base uppercase tracking-wide leading-tight">
                    Government of West Bengal
                </h3>
                <h4 class="text-slate-500 font-bold text-xs uppercase tracking-widest mt-1">
                    Finance Department
                </h4>
            </div>
        </div>

        <!-- Center: Slogan & Portal Name -->
        <div class="flex-none flex flex-col text-center md:px-8 py-1.5">
            <h1 class="text-[#1B365D] text-lg md:text-xl font-black tracking-widest uppercase font-display leading-tight drop-shadow-sm mb-1">
                {{ strtoupper(config('app.name', 'SAMARTH')) }} Portal
            </h1>
            <span class="text-[#1B365D] text-[12px] md:text-sm font-bold leading-tight mb-0.5">
                পশ্চিমবঙ্গ সরকারের সমস্ত সামাজিক পেনশন প্রকল্পের ওয়ান আমব্রেলা স্কিম
            </span>
           
        </div>

        <!-- Right: Empty Spacer for perfect centering -->
        <div class="flex-1 hidden md:block"></div>
    </div>
</header>
