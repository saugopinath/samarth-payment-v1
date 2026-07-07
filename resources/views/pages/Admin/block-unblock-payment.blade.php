<?php
use function Laravel\Folio\{name, middleware};

name('admin.block-unblock-payment');
middleware(['auth', 'verified']);
?>
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-medium text-gray-800 leading-tight tracking-wide">
                {{ __('Block-Unblock Payment') }}
            </h2>
        </div>
    </x-slot>

    @volt
    <div class="py-6 bg-gray-50 min-h-screen">
        <?php
            use function Livewire\Volt\{state, with};
            use App\Models\Scheme;
            use App\Models\District;

            state([
                'search_level' => 'area', // 'area' or 'beneficiary'
                'scheme_id' => '',
                'district_id' => '',
                'area_type' => 'sub_division',
                'subdivision_id' => '',
                'beneficiary_id' => '',
                
                'show_results' => false,
                'result_level' => 'area',
            ]);

            with(fn () => [
                'schemes' => Scheme::where('is_active', true)->get(),
                'districts' => District::where('is_active', true)->orderBy('name')->get(),
                'areaTypes' => [
                    'sub_division' => 'Sub-Division (For ULB)',
                    'block' => 'Block (For Rural)',
                ],
                // Mock subdivisions for the UI layout representation
                'subdivisions' => [
                    1 => 'Alipurduar',
                    2 => 'Falakata',
                ],
                // Mock results
                'municipalities' => [
                    ['id' => 1, 'name' => 'Falakata', 'status' => 'Unblock'],
                    ['id' => 2, 'name' => 'Alipurduar', 'status' => 'Unblock'],
                ],
                'beneficiaries' => [
                    ['id' => 1010101, 'name' => 'John Doe', 'status' => 'Unblock'],
                    ['id' => 2020202, 'name' => 'Jane Smith', 'status' => 'Block'],
                ]
            ]);

            $search = function () {
                $this->result_level = $this->search_level;
                $this->show_results = true;
            };

            $toggleBlock = function ($id) {
                // Logic to toggle block/unblock status
            };
        ?>
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Filter Criteria Panel -->
            <div class="bg-white shadow-sm border border-gray-200 rounded overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-sm font-semibold italic text-gray-800">Enter Filter Criteria</h3>
                    <div class="flex items-center space-x-6">
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" wire:model.live="search_level" value="area" class="text-blue-500 focus:ring-blue-500 border-gray-300 cursor-pointer">
                            <span class="ml-2 text-sm text-gray-700">Area Level</span>
                        </label>
                        <label class="inline-flex items-center cursor-pointer">
                            <input type="radio" wire:model.live="search_level" value="beneficiary" class="text-blue-500 focus:ring-blue-500 border-gray-300 cursor-pointer">
                            <span class="ml-2 text-sm text-gray-700">Beneficiary Level</span>
                        </label>
                    </div>
                </div>
                
                <div class="p-5">
                    @if($search_level === 'area')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <!-- Scheme -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Scheme <span class="text-red-500">*</span></label>
                                <select wire:model="scheme_id" class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 text-gray-700">
                                    <option value="">---Select Scheme---</option>
                                    @foreach($schemes as $sch)
                                        <option value="{{ $sch->id }}">{{ $sch->display_name ?? $sch->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- District -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">District <span class="text-red-500">*</span></label>
                                <select wire:model="district_id" class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 text-gray-700">
                                    <option value="">---Select District---</option>
                                    @foreach($districts as $district)
                                        <option value="{{ $district->id }}">{{ $district->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Select Area -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Select Area <span class="text-red-500">*</span></label>
                                <select wire:model="area_type" class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 text-gray-700">
                                    @foreach($areaTypes as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Subdivision -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Subdivision <span class="text-red-500">*</span></label>
                                <select wire:model="subdivision_id" class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 text-gray-700">
                                    <option value="">---Select Subdivision---</option>
                                    @foreach($subdivisions as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl mx-auto">
                            <!-- Scheme -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Scheme <span class="text-red-500">*</span></label>
                                <select wire:model="scheme_id" class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 text-gray-700">
                                    <option value="">---Select Scheme---</option>
                                    @foreach($schemes as $sch)
                                        <option value="{{ $sch->id }}">{{ $sch->display_name ?? $sch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Beneficiary ID -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Beneficiary ID <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="beneficiary_id" class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 text-gray-700" placeholder="Enter Beneficiary ID">
                            </div>
                        </div>
                    @endif

                    <!-- Search Button -->
                    <div class="mt-6 flex justify-center">
                        <button wire:click="search" class="px-5 py-1.5 bg-gray-50 text-gray-300 rounded border border-gray-100 shadow-sm hover:bg-gray-100 focus:outline-none font-medium text-sm flex items-center transition-colors opacity-70">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            Search
                        </button>
                    </div>
                </div>
            </div>

            <!-- Results Panel -->
            @if($show_results)
            <div class="bg-white shadow-sm border border-gray-200 rounded overflow-hidden">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200">
                    <h3 class="text-sm font-semibold italic text-gray-800">
                        {{ $result_level === 'area' ? 'List of Municipality Names' : 'Beneficiary Details' }}
                    </h3>
                </div>
                
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-white">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700 w-16">
                                        SL No
                                    </th>
                                    @if($result_level === 'area')
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Municipality
                                        </th>
                                    @else
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Beneficiary ID
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Beneficiary Name
                                        </th>
                                    @endif
                                    <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                        Current Status
                                    </th>
                                    <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @if($result_level === 'area')
                                    @foreach($municipalities as $index => $row)
                                        <tr class="bg-white hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $index + 1 }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $row['name'] }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap font-semibold {{ $row['status'] === 'Unblock' ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $row['status'] }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <button wire:click="toggleBlock({{ $row['id'] }})" class="inline-flex items-center px-3 py-1.5 bg-gray-50 text-gray-400 text-xs font-semibold rounded border border-gray-200 shadow-sm hover:bg-gray-100 transition-colors opacity-60">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H10V7a3 3 0 116 0v2h2V7a5 5 0 00-5-5z" clip-rule="evenodd"></path></svg>
                                                    Click to {{ $row['status'] === 'Unblock' ? 'Block' : 'Unblock' }}
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    @foreach($beneficiaries as $index => $row)
                                        <tr class="bg-white hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $index + 1 }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $row['id'] }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $row['name'] }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap font-semibold {{ $row['status'] === 'Unblock' ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $row['status'] }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <button wire:click="toggleBlock({{ $row['id'] }})" class="inline-flex items-center px-3 py-1.5 bg-gray-50 text-gray-400 text-xs font-semibold rounded border border-gray-200 shadow-sm hover:bg-gray-100 transition-colors opacity-60">
                                                    <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 2a5 5 0 00-5 5v2a2 2 0 00-2 2v5a2 2 0 002 2h10a2 2 0 002-2v-5a2 2 0 00-2-2H10V7a3 3 0 116 0v2h2V7a5 5 0 00-5-5z" clip-rule="evenodd"></path></svg>
                                                    Click to {{ $row['status'] === 'Unblock' ? 'Block' : 'Unblock' }}
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
    @endvolt
</x-app-layout>
