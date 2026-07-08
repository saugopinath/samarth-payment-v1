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
            use function Livewire\Volt\{state, with, updated};
            use App\Models\Scheme;
            use App\Models\District;
            use App\Models\Subdivision;
            use App\Models\Block;
            use App\Models\Municipality;
            use App\Models\Panchayat;
            use App\Models\FinancialYear;
            use App\Models\Month;
            use App\Models\FinancialYearMonthLot;

            state([
                'active_tab' => 'scheme', // 'scheme', 'area' or 'beneficiary'
                'scheme_id' => '',
                'district_id' => '',
                'area_type' => '',
                'subdivision_id' => '',
                'block_id' => '',
                'beneficiary_id' => '',
                'financial_year' => '',
                'month' => '',
                
                'show_results' => false,
                'result_level' => 'scheme',
            ]);

            updated([
                'district_id' => function () {
                    $this->area_type = '';
                    $this->subdivision_id = '';
                    $this->block_id = '';
                },
                'area_type' => function () {
                    $this->subdivision_id = '';
                    $this->block_id = '';
                },
                'active_tab' => function () {
                    $this->show_results = false;
                }
            ]);

            with(function () {
                $years = FinancialYear::where('is_active', true)->orderBy('name')->pluck('name', 'code')->toArray();
                
                // Set default financial year to the latest one if not set
                if (empty($this->financial_year) && !empty($years)) {
                    $this->financial_year = array_key_last($years);
                }
                
                $dynamicSubdivisions = [];
                $dynamicBlocks = [];
                $allAreas = [];
                
                if (!empty($this->district_id)) {
                    $dynamicSubdivisions = Subdivision::where('district_id', $this->district_id)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray();
                        
                    $dynamicBlocks = Block::where('district_id', $this->district_id)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray();
                        
                    foreach($dynamicSubdivisions as $id => $name) {
                        $allAreas[] = ['type' => 'Subdivision', 'name' => $name];
                    }
                    foreach($dynamicBlocks as $id => $name) {
                        $allAreas[] = ['type' => 'Block', 'name' => $name];
                    }
                    
                    usort($allAreas, function($a, $b) {
                        return strcmp($a['name'], $b['name']);
                    });
                }
                
                $dynamicMunicipalities = [];
                if ($this->area_type === 'sub_division' && !empty($this->subdivision_id)) {
                    $dynamicMunicipalities = Municipality::where('subdivision_id', $this->subdivision_id)
                        ->orderBy('name')
                        ->get();
                }
                
                $dynamicPanchayats = [];
                if ($this->area_type === 'block' && !empty($this->block_id)) {
                    $dynamicPanchayats = Panchayat::where('block_id', $this->block_id)
                        ->orderBy('name')
                        ->get();
                }

                return [
                    'schemes' => Scheme::where('is_active', true)->get(),
                    'districts' => District::where('is_active', true)->orderBy('name')->get(),
                    'financialYears' => $years,
                    'months' => Month::where('is_active', true)->orderBy('display_order')->pluck('name')->toArray(),
                    'areaTypes' => [
                        'sub_division' => 'Sub-Division (For ULB)',
                        'block' => 'Block (For Rural)',
                    ],
                    'subdivisions' => $dynamicSubdivisions,
                    'blocks' => $dynamicBlocks,
                    'allAreas' => $allAreas,
                    'municipalities' => $dynamicMunicipalities,
                    'panchayats' => $dynamicPanchayats,
                    'beneficiaries' => [
                        ['id' => 1010101, 'name' => 'John Doe', 'status' => 'Unblock'],
                        ['id' => 2020202, 'name' => 'Jane Smith', 'status' => 'Block'],
                    ]
                ];
            });

            $search = function () {
                if ($this->active_tab === 'area') {
                    $this->validate([
                        'scheme_id' => 'required',
                    ], [
                        'scheme_id.required' => 'Scheme selection is mandatory for Area Level search.'
                    ]);
                    
                    if (empty($this->district_id)) {
                        $this->result_level = 'district';
                    } elseif (empty($this->area_type)) {
                        $this->result_level = 'all_areas';
                    } elseif ($this->area_type === 'sub_division') {
                        if (empty($this->subdivision_id)) {
                            $this->result_level = 'subdivision';
                        } else {
                            $this->result_level = 'area';
                        }
                    } elseif ($this->area_type === 'block') {
                        if (empty($this->block_id)) {
                            $this->result_level = 'block';
                        } else {
                            $this->result_level = 'panchayat';
                        }
                    } else {
                        $this->result_level = 'area';
                    }
                } elseif ($this->active_tab === 'beneficiary') {
                    $this->validate([
                        'scheme_id' => 'required',
                        'beneficiary_id' => 'required',
                    ], [
                        'scheme_id.required' => 'Scheme selection is mandatory for Beneficiary Level search.',
                        'beneficiary_id.required' => 'Beneficiary ID is mandatory.'
                    ]);
                    $this->result_level = 'beneficiary';
                } else {
                    $this->result_level = 'scheme';
                }

                $this->show_results = true;
            };

            $toggleSchemeLot = function ($schemeId, $type) {
                $scheme = Scheme::find($schemeId);
                if (!$scheme) return;
                
                if ($type === 'regular') {
                    $scheme->allow_regular_lot = !$scheme->allow_regular_lot;
                } elseif ($type === 'arrear') {
                    $scheme->allow_arrear_lot = !$scheme->allow_arrear_lot;
                }
                
                $scheme->save();
            };

            $toggleBlock = function ($id) {
                // Logic to toggle block/unblock status
            };
        ?>
        <div class="w-full px-4 sm:px-6 lg:px-8 space-y-6">
            
            <!-- Filter Criteria Panel -->
            <div class="bg-white shadow-md border border-gray-200 rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <div class="flex">
                        <button wire:click="$set('active_tab', 'scheme')" class="mr-8 pb-4 text-sm font-medium transition-colors border-b-2 {{ $active_tab === 'scheme' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Scheme Level
                        </button>
                        <button wire:click="$set('active_tab', 'area')" class="mr-8 pb-4 text-sm font-medium transition-colors border-b-2 {{ $active_tab === 'area' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Area Level
                        </button>
                        <button wire:click="$set('active_tab', 'beneficiary')" class="pb-4 text-sm font-medium transition-colors border-b-2 {{ $active_tab === 'beneficiary' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            Beneficiary Level
                        </button>
                    </div>
                </div>
                
                <div class="p-6">
                    @if($active_tab === 'scheme')
                        <div class="text-sm text-gray-600 mb-4">
                            Click Search to view and toggle the regular and arrear lots for all active schemes globally.
                        </div>
                    @elseif($active_tab === 'area')
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                            <!-- Scheme -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Scheme <span class="text-red-500">*</span></label>
                                <select wire:model="scheme_id" class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 text-gray-700">
                                    <option value="">---Select Scheme---</option>
                                    @foreach($schemes as $sch)
                                        <option value="{{ $sch->id }}">{{ $sch->display_name ?? $sch->name }}</option>
                                    @endforeach
                                </select>
                                @error('scheme_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- District -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">District</label>
                                <select wire:model.live="district_id" class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 text-gray-700">
                                    <option value="">---All Districts---</option>
                                    @foreach($districts as $district)
                                        <option value="{{ $district->id }}">{{ $district->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Area Type -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Area Type</label>
                                <select wire:model.live="area_type" class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 text-gray-700" {{ empty($district_id) ? 'disabled' : '' }}>
                                    <option value="">---All Areas---</option>
                                    @foreach($areaTypes as $val => $label)
                                        <option value="{{ $val }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Subdivision / Block -->
                            @if($area_type === 'block')
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Block</label>
                                    <select wire:model="block_id" class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 text-gray-700" {{ empty($district_id) ? 'disabled' : '' }}>
                                        <option value="">---All Blocks---</option>
                                        @foreach($blocks as $val => $label)
                                            <option value="{{ $val }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @elseif($area_type === 'sub_division')
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Subdivision</label>
                                    <select wire:model="subdivision_id" class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 text-gray-700" {{ empty($district_id) ? 'disabled' : '' }}>
                                        <option value="">---All Subdivisions---</option>
                                        @foreach($subdivisions as $val => $label)
                                            <option value="{{ $val }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Subdivision / Block</label>
                                    <select disabled class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 text-gray-700 bg-gray-50 opacity-70">
                                        <option value="">---Select Area Type First---</option>
                                    </select>
                                </div>
                            @endif
                        </div>
                    @elseif($active_tab === 'beneficiary')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-2xl">
                            <!-- Scheme -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Scheme <span class="text-red-500">*</span></label>
                                <select wire:model="scheme_id" class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 text-gray-700">
                                    <option value="">---Select Scheme---</option>
                                    @foreach($schemes as $sch)
                                        <option value="{{ $sch->id }}">{{ $sch->display_name ?? $sch->name }}</option>
                                    @endforeach
                                </select>
                                @error('scheme_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>

                            <!-- Beneficiary ID -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Beneficiary ID <span class="text-red-500">*</span></label>
                                <input type="text" wire:model="beneficiary_id" class="block w-full border-gray-300 rounded shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm py-1.5 text-gray-700" placeholder="Enter Beneficiary ID">
                                @error('beneficiary_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @endif
                    </div>

                    <!-- Search Button -->
                    <div class="mt-8 flex justify-center">
                        <button wire:click="search" class="px-6 py-2 bg-blue-600 text-white rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 font-semibold text-sm flex items-center transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
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
                        {{ $result_level === 'scheme' ? 'List of Schemes' : ($result_level === 'district' ? 'List of Districts' : ($result_level === 'all_areas' ? 'List of Areas' : ($result_level === 'subdivision' ? 'List of Subdivisions' : ($result_level === 'block' ? 'List of Blocks' : ($result_level === 'panchayat' ? 'List of Gram Panchayats' : ($result_level === 'area' ? 'List of Municipality Names' : 'Beneficiary Details')))))) }}
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
                                    @if($result_level === 'scheme')
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Scheme Name
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Enable/Disable Lots
                                        </th>
                                    @elseif($result_level === 'district')
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            District
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Enable/Disable Lots
                                        </th>
                                    @elseif($result_level === 'subdivision')
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Subdivision
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Enable/Disable Lots
                                        </th>
                                    @elseif($result_level === 'block')
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Block
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Enable/Disable Lots
                                        </th>
                                    @elseif($result_level === 'all_areas')
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Area Name
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Area Type
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Enable/Disable Lots
                                        </th>
                                    @elseif($result_level === 'area')
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Municipality
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Enable/Disable Lots
                                        </th>
                                    @elseif($result_level === 'panchayat')
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Gram Panchayat
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Enable/Disable Lots
                                        </th>
                                    @else
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Beneficiary ID
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Beneficiary Name
                                        </th>
                                    @endif
                                    @if($result_level !== 'scheme' && $result_level !== 'district' && $result_level !== 'subdivision' && $result_level !== 'block' && $result_level !== 'all_areas' && $result_level !== 'area' && $result_level !== 'panchayat')
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Current Status
                                        </th>
                                        <th scope="col" class="px-4 py-3 text-left font-bold text-gray-700">
                                            Action
                                        </th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @if($result_level === 'scheme')
                                    @foreach($schemes as $index => $sch)
                                        <tr class="bg-white hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $index + 1 }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $sch->display_name ?? $sch->name }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="flex items-center space-x-4">
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" wire:click="toggleSchemeLot({{ $sch->id }}, 'regular')" {{ $sch->allow_regular_lot ? 'checked' : '' }} class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                        <span class="ml-2 text-sm text-gray-700 font-medium">Regular Lot</span>
                                                    </label>
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" wire:click="toggleSchemeLot({{ $sch->id }}, 'arrear')" {{ $sch->allow_arrear_lot ? 'checked' : '' }} class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                                        <span class="ml-2 text-sm text-gray-700 font-medium">Arrear Lot</span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @elseif($result_level === 'district')
                                    @foreach($districts as $index => $row)
                                        <tr class="bg-white hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $index + 1 }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $row->name }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="flex items-center space-x-4">
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                                        <span class="ml-2 text-sm text-gray-700 font-medium">Regular Lot</span>
                                                    </label>
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                                        <span class="ml-2 text-sm text-gray-700 font-medium">Arrear Lot</span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @elseif($result_level === 'subdivision')
                                    @foreach($subdivisions as $val => $label)
                                        <tr class="bg-white hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $loop->iteration }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $label }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="flex items-center space-x-4">
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                                        <span class="ml-2 text-sm text-gray-700 font-medium">Regular Lot</span>
                                                    </label>
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                                        <span class="ml-2 text-sm text-gray-700 font-medium">Arrear Lot</span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @elseif($result_level === 'block')
                                    @foreach($blocks as $val => $label)
                                        <tr class="bg-white hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $loop->iteration }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $label }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="flex items-center space-x-4">
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                                        <span class="ml-2 text-sm text-gray-700 font-medium">Regular Lot</span>
                                                    </label>
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                                        <span class="ml-2 text-sm text-gray-700 font-medium">Arrear Lot</span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @elseif($result_level === 'all_areas')
                                    @foreach($allAreas as $index => $area)
                                        <tr class="bg-white hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $index + 1 }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $area['name'] }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $area['type'] }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="flex items-center space-x-4">
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                                        <span class="ml-2 text-sm text-gray-700 font-medium">Regular Lot</span>
                                                    </label>
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                                        <span class="ml-2 text-sm text-gray-700 font-medium">Arrear Lot</span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @elseif($result_level === 'panchayat')
                                    @foreach($panchayats as $index => $row)
                                        <tr class="bg-white hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $index + 1 }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $row->name }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="flex items-center space-x-4">
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                                        <span class="ml-2 text-sm text-gray-700 font-medium">Regular Lot</span>
                                                    </label>
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                                        <span class="ml-2 text-sm text-gray-700 font-medium">Arrear Lot</span>
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @elseif($result_level === 'area')
                                    @foreach($municipalities as $index => $row)
                                        <tr class="bg-white hover:bg-gray-50">
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $index + 1 }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-gray-600">
                                                {{ $row->name }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap">
                                                <div class="flex items-center space-x-4">
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                                        <span class="ml-2 text-sm text-gray-700 font-medium">Regular Lot</span>
                                                    </label>
                                                    <label class="flex items-center cursor-pointer">
                                                        <input type="checkbox" class="form-checkbox h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" checked>
                                                        <span class="ml-2 text-sm text-gray-700 font-medium">Arrear Lot</span>
                                                    </label>
                                                </div>
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
