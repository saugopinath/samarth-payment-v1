<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-6 py-3 w-full bg-[#FF8800] hover:bg-[#E67700] active:scale-[0.98] border border-transparent rounded-xl font-display font-extrabold text-sm text-white uppercase tracking-wider shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-[#FF8800] focus:ring-offset-2 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
