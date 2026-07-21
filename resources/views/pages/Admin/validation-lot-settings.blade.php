<?php
use function Laravel\Folio\{name, middleware};

name('admin.validation-lot-settings');
middleware(['auth', 'verified']);
?>
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-2xl text-orange-500 leading-tight">
                {{ __('Validation Lot Settings') }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">Configure validation lot options for all schemes.</p>
        </div>
    </x-slot>

    @volt
    <div class="py-8 bg-[#fdfaf5] min-h-screen">
        <?php
            use function Livewire\Volt\{state, with};

            state([
                'settings' => [],
            ]);

            with(function () {
                $schemes = \App\Models\Scheme::where('is_active', true)->orderBy('name')->get();
                $allSettings = \App\Models\ValidationLotSetting::all();
                
                $validationModes = \Illuminate\Support\Facades\DB::table('codemasters')
                    ->where('parent_short_code', 'validation_mode')
                    ->orderBy('name')
                    ->get();
                
                $validationModeCodes = $validationModes->pluck('code')->toArray();
                
                if (empty($this->settings)) {
                    foreach ($schemes as $scheme) {
                        foreach (['52401', '52402', '52403'] as $type) {
                            $exists = $allSettings->where('scheme_id', $scheme->id)->where('type', $type)->isNotEmpty();
                            $this->settings[$scheme->id][$type] = $exists;
                        }
                        
                        $modeSetting = $allSettings->where('scheme_id', $scheme->id)->whereIn('type', $validationModeCodes)->first();
                        $this->settings[$scheme->id]['mode'] = $modeSetting ? $modeSetting->type : '';
                    }
                }

                return [
                    'schemes' => $schemes,
                    'validationModes' => $validationModes,
                ];
            });

            $finalSubmit = function () {
                $validationModes = \Illuminate\Support\Facades\DB::table('codemasters')
                    ->where('parent_short_code', 'validation_mode')
                    ->pluck('code')
                    ->toArray();
                    
                foreach ($this->settings as $schemeId => $types) {
                    // Save toggle settings
                    foreach (['52401', '52402', '52403'] as $type) {
                        $isEnabled = $types[$type] ?? false;
                        if ($isEnabled) {
                            \App\Models\ValidationLotSetting::firstOrCreate([
                                'scheme_id' => $schemeId,
                                'type' => $type,
                            ]);
                        } else {
                            \App\Models\ValidationLotSetting::where([
                                'scheme_id' => $schemeId,
                                'type' => $type,
                            ])->delete();
                        }
                    }
                    
                    // Save validation mode
                    $modeCode = $types['mode'] ?? '';
                    \App\Models\ValidationLotSetting::where('scheme_id', $schemeId)->whereIn('type', $validationModes)->delete();
                    
                    if (!empty($modeCode)) {
                        \App\Models\ValidationLotSetting::create([
                            'scheme_id' => $schemeId,
                            'type' => $modeCode,
                        ]);
                    }
                }
                session()->flash('status', 'Validation lot settings saved successfully!');
            };
        ?>
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(session('status'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('status') }}</span>
                </div>
            @endif

            <div class="relative bg-white shadow-sm border border-blue-200 rounded-lg p-6 pt-10 animate-[fadeIn_0.3s_ease-in-out]">
                <span class="absolute -top-4 left-6 bg-blue-500 text-white px-5 py-1.5 rounded-lg text-sm font-bold shadow-md tracking-wide flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Scheme Configurations
                </span>

                <div class="mt-4 overflow-x-auto border border-gray-300">
                    <table class="min-w-full divide-y divide-gray-300 text-sm border-collapse">
                        <thead class="bg-gray-50 border-b border-gray-300">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700 border-r border-gray-300 uppercase text-xs w-64">
                                    Scheme Name
                                </th>
                                <th scope="col" class="px-4 py-3 text-center font-bold text-gray-700 border-r border-gray-300 text-sm">
                                    Validation Mode
                                </th>
                                <th scope="col" class="px-4 py-3 text-center font-bold text-gray-700 border-r border-gray-300 text-sm">
                                    Creation Option
                                </th>
                                <th scope="col" class="px-4 py-3 text-center font-bold text-gray-700 border-r border-gray-300 text-sm">
                                    Pushing Option
                                </th>
                                <th scope="col" class="px-4 py-3 text-center font-bold text-gray-700 text-sm">
                                    Response Receive Option
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-300">
                            @forelse($schemes as $scheme)
                            <tr class="hover:bg-gray-50 transition-colors border-b border-gray-300">
                                <td class="px-4 py-3 text-gray-800 font-semibold align-middle border-r border-gray-300 w-64">
                                    {{ $scheme->display_name ?? $scheme->name }}
                                </td>
                                
                                <td class="px-4 py-3 align-middle border-r border-gray-300 text-center">
                                    <select wire:model="settings.{{ $scheme->id }}.mode" class="w-full max-w-[150px] mx-auto border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                        <option value="">-- Mode --</option>
                                        @foreach($validationModes as $mode)
                                            <option value="{{ $mode->code }}">{{ $mode->name }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                
                                <!-- Creation (52401) -->
                                <td class="px-4 py-3 align-middle border-r border-gray-300 text-center">
                                    <label class="inline-flex items-center cursor-pointer group">
                                        <input type="checkbox" wire:model="settings.{{ $scheme->id }}.52401" class="form-checkbox h-4 w-4 text-blue-600 border-gray-400 rounded focus:ring-blue-500 cursor-pointer">
                                        <span class="ml-2 text-sm text-gray-700 font-medium">Enable</span>
                                    </label>
                                </td>

                                <!-- Pushing (52402) -->
                                <td class="px-4 py-3 align-middle border-r border-gray-300 text-center">
                                    <label class="inline-flex items-center cursor-pointer group">
                                        <input type="checkbox" wire:model="settings.{{ $scheme->id }}.52402" class="form-checkbox h-4 w-4 text-blue-600 border-gray-400 rounded focus:ring-blue-500 cursor-pointer">
                                        <span class="ml-2 text-sm text-gray-700 font-medium">Enable</span>
                                    </label>
                                </td>

                                <!-- Response Receive (52403) -->
                                <td class="px-4 py-3 align-middle text-center">
                                    <label class="inline-flex items-center cursor-pointer group">
                                        <input type="checkbox" wire:model="settings.{{ $scheme->id }}.52403" class="form-checkbox h-4 w-4 text-blue-600 border-gray-400 rounded focus:ring-blue-500 cursor-pointer">
                                        <span class="ml-2 text-sm text-gray-700 font-medium">Enable</span>
                                    </label>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-500 font-medium">
                                    No schemes found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-8 flex justify-center">
                    <button wire:click="finalSubmit" style="background-color: #5c9ccc; color: white;" class="px-8 py-2.5 rounded shadow text-sm font-semibold tracking-wide hover:opacity-90 transition-opacity">
                        Final Submit
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endvolt
</x-app-layout>
