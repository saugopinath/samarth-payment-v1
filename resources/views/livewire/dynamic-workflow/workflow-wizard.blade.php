<div>
    <div class="mb-8">
        <!-- Progress Bar -->
        <div class="relative after:absolute after:inset-x-0 after:top-1/2 after:-translate-y-1/2 after:block after:h-0.5 after:bg-slate-200 dark:after:bg-slate-700 after:z-0">
            <ol class="relative z-10 flex justify-between text-sm font-medium">
                @for ($i = 1; $i <= $totalTabs; $i++)
                    <li class="flex items-center gap-2 p-2 bg-white dark:bg-slate-800">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full text-white shadow-sm transition-colors duration-300 {{ $currentTab >= $i ? 'bg-amber-500' : 'bg-slate-300 dark:bg-slate-600' }}">
                            @if($currentTab > $i)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            @else
                                {{ $i }}
                            @endif
                        </span>
                        <span class="hidden sm:block text-slate-700 dark:text-slate-300 font-semibold">
                            @if($i == 1) Initialization 
                            @elseif($i == 2) Step Naming 
                            @else Configuration 
                            @endif
                        </span>
                    </li>
                @endfor
            </ol>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 sm:p-8">
        
        <!-- Flash Messages -->
        @if (session()->has('error'))
            <div class="mb-4 bg-red-100 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif
        @if (session()->has('success'))
            <div class="mb-4 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 px-4 py-3 rounded-lg flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        <!-- Tab 1: Initialization -->
        @if ($currentTab == 1)
            <div class="space-y-6 animate-fade-in">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-1">Initialize Workflow</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Select a scheme and configure the base module settings.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Scheme Selection -->
                    <div>
                        <x-input-label for="selectedScheme" :value="__('Scheme')" />
                        <select wire:model.live="selectedScheme" id="selectedScheme" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-lg shadow-sm sm:text-sm">
                            <option value="">-- Select a Scheme --</option>
                            @foreach ($schemes as $scheme)
                                <option value="{{ $scheme->id }}">{{ $scheme->name }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('selectedScheme')" class="mt-1" />
                    </div>

                    @if ($selectedScheme)
                        <!-- Module Selection -->
                        <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-lg border border-slate-200 dark:border-slate-700 space-y-4">
                            <div class="flex items-center justify-between">
                                <x-input-label :value="__('Module Configuration')" />
                                <label class="inline-flex items-center cursor-pointer">
                                    <input type="checkbox" wire:model.live="isNewModule" class="rounded border-slate-300 text-amber-500 shadow-sm focus:ring-amber-500">
                                    <span class="ml-2 text-sm font-medium text-slate-700 dark:text-slate-300">Create New Module</span>
                                </label>
                            </div>

                            @if ($isNewModule)
                                <div class="space-y-4 animate-fade-in-up">
                                    <div>
                                        <x-input-label for="newModuleName" :value="__('New Module Name')" />
                                        <x-text-input wire:model="newModuleName" id="newModuleName" type="text" class="mt-1 block w-full" placeholder="e.g. Beneficiary Approval" />
                                        <x-input-error :messages="$errors->get('newModuleName')" class="mt-1" />
                                    </div>
                                    <div>
                                        <x-input-label for="newModuleCode" :value="__('New Module Code')" />
                                        <x-text-input wire:model="newModuleCode" id="newModuleCode" type="text" class="mt-1 block w-full uppercase" placeholder="e.g. BEN_APP_01" />
                                        <x-input-error :messages="$errors->get('newModuleCode')" class="mt-1" />
                                    </div>
                                </div>
                            @else
                                <div class="animate-fade-in">
                                    <x-input-label for="selectedModule" :value="__('Select Existing Module')" />
                                    <select wire:model.live="selectedModule" id="selectedModule" class="mt-1 block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-lg shadow-sm sm:text-sm">
                                        <option value="">-- Select Module --</option>
                                        @foreach ($moduleList as $mod)
                                            <option value="{{ $mod->id }}">{{ $mod->module_name }} ({{ $mod->module_code }})</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('selectedModule')" class="mt-1" />
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                @if($selectedScheme && ($selectedModule || $isNewModule))
                    <!-- Step Count Selection -->
                    <div class="pt-4 border-t border-slate-200 dark:border-slate-700">
                        <x-input-label :value="__('Number of Workflow Steps')" />
                        <div class="mt-2 flex items-center gap-3">
                            <button type="button" wire:click="decrementStepCount" class="w-10 h-10 rounded-lg flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-200 transition-colors shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                            </button>
                            <input type="number" wire:model.live="stepCount" min="1" max="10" class="w-20 text-center font-bold text-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 focus:border-amber-500 focus:ring-amber-500 rounded-lg shadow-sm" readonly>
                            <button type="button" wire:click="incrementStepCount" class="w-10 h-10 rounded-lg flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-200 transition-colors shadow-sm">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('stepCount')" class="mt-1" />
                    </div>
                @endif
            </div>

            <!-- Navigation Buttons -->
            <div class="mt-8 pt-5 border-t border-slate-200 dark:border-slate-700 flex justify-end">
                <button type="button" wire:click="moveToNaming" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-medium rounded-lg shadow-sm transition-colors flex items-center gap-2">
                    Next: Name Steps
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </div>
        @endif


        <!-- Tab 2: Step Naming -->
        @if ($currentTab == 2)
            <div class="space-y-6 animate-fade-in">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-1">Name Workflow Steps</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Provide a descriptive label for each step in the workflow process.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @for ($i = 0; $i < $stepCount; $i++)
                        <div class="bg-slate-50 dark:bg-slate-800/50 p-4 rounded-lg border border-slate-200 dark:border-slate-700 shadow-sm relative">
                            <span class="absolute -top-3 -left-3 flex items-center justify-center w-7 h-7 bg-amber-500 text-white font-bold rounded-full shadow-md text-xs">
                                {{ $i + 1 }}
                            </span>
                            <x-input-label :for="'step_'.$i" :value="__('Step '.($i + 1).' Name')" class="mb-1 ml-2" />
                            <x-text-input wire:model="stepNames.{{ $i }}" id="step_{{ $i }}" type="text" class="block w-full" placeholder="e.g. Verification Level" />
                            <x-input-error :messages="$errors->get('stepNames.'.$i)" class="mt-1" />
                        </div>
                    @endfor
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="mt-8 pt-5 border-t border-slate-200 dark:border-slate-700 flex justify-between">
                <button type="button" wire:click="goBack" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-200 font-medium rounded-lg shadow-sm transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Back
                </button>
                <button type="button" wire:click="moveToConfig" class="px-6 py-2.5 bg-amber-500 hover:bg-amber-600 text-white font-medium rounded-lg shadow-sm transition-colors flex items-center gap-2">
                    Next: Configure Roles & Permissions
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </button>
            </div>
        @endif


        <!-- Tab 3: Configuration -->
        @if ($currentTab == 3)
            <div class="space-y-6 animate-fade-in">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-1">Assign Roles & Permissions</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Configure which roles have access to each step and what permissions they possess.</p>
                </div>

                <div class="space-y-8">
                    @foreach ($finalSteps as $index => $step)
                        <div class="bg-white dark:bg-slate-900 border-2 {{ $step['is_final'] ? 'border-emerald-400 dark:border-emerald-600' : 'border-slate-200 dark:border-slate-700' }} rounded-xl shadow-sm overflow-hidden" wire:key="step-{{ $index }}">
                            
                            <!-- Header -->
                            <div class="bg-slate-50 dark:bg-slate-800 px-5 py-3 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="flex items-center justify-center w-8 h-8 bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400 font-bold rounded-full">
                                        {{ $index + 1 }}
                                    </span>
                                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200">{{ $step['label'] }}</h3>
                                </div>
                                <div class="flex items-center gap-4 text-xs font-medium">
                                    <span class="px-2.5 py-1 bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 rounded-md border border-indigo-100 dark:border-indigo-800">
                                        Rank: {{ $step['rank'] }}
                                    </span>
                                    @if($step['is_final'])
                                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 rounded-md border border-emerald-100 dark:border-emerald-800 flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Final Step
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="p-5 grid grid-cols-1 lg:grid-cols-2 gap-6">
                                <!-- Roles Assignment -->
                                <div>
                                    <div class="flex items-center gap-2 mb-3">
                                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                        <h4 class="font-semibold text-slate-700 dark:text-slate-300">Authorized Roles <span class="text-rose-500">*</span></h4>
                                    </div>
                                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-700 p-3 max-h-48 overflow-y-auto">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            @foreach($roles as $id => $name)
                                                <label class="flex items-start space-x-2 p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded cursor-pointer transition-colors">
                                                    <input type="checkbox" wire:model="finalSteps.{{ $index }}.role_ids" value="{{ $id }}" class="mt-0.5 rounded border-slate-300 text-amber-500 focus:ring-amber-500 dark:bg-slate-900 dark:border-slate-600">
                                                    <span class="text-sm text-slate-700 dark:text-slate-300 leading-tight">{{ $name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('finalSteps.'.$index.'.role_ids')" class="mt-1" />
                                </div>

                                <!-- Permissions Assignment -->
                                <div>
                                    <div class="flex items-center gap-2 mb-3">
                                        <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                        <h4 class="font-semibold text-slate-700 dark:text-slate-300">Permissions Required <span class="text-rose-500">*</span></h4>
                                    </div>
                                    <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-200 dark:border-slate-700 p-3 max-h-48 overflow-y-auto">
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            @foreach($permissionsList as $id => $name)
                                                <label class="flex items-start space-x-2 p-1.5 hover:bg-slate-100 dark:hover:bg-slate-700 rounded cursor-pointer transition-colors">
                                                    <input type="checkbox" wire:model="finalSteps.{{ $index }}.permissions" value="{{ $id }}" class="mt-0.5 rounded border-slate-300 text-amber-500 focus:ring-amber-500 dark:bg-slate-900 dark:border-slate-600">
                                                    <span class="text-sm text-slate-700 dark:text-slate-300 break-all leading-tight">{{ $name }}</span>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                    <x-input-error :messages="$errors->get('finalSteps.'.$index.'.permissions')" class="mt-1" />
                                </div>
                            </div>
                            
                            <!-- Workflow Logic Info -->
                            <div class="bg-slate-50/50 dark:bg-slate-800/30 px-5 py-2.5 border-t border-slate-100 dark:border-slate-700/50 flex flex-wrap gap-x-6 gap-y-2 text-xs text-slate-500 dark:text-slate-400">
                                <div class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                    <span>On Success: Rank {{ $step['success_rank'] ?? 'End' }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                                    <span>On Revert: Rank {{ $step['revert_rank'] ?? 'Origin' }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="mt-8 pt-5 border-t border-slate-200 dark:border-slate-700 flex justify-between">
                <button type="button" wire:click="goBack" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-700 dark:hover:bg-slate-600 dark:text-slate-200 font-medium rounded-lg shadow-sm transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    Back
                </button>
                <button type="button" wire:click="saveWorkflow" class="px-8 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white font-bold rounded-lg shadow-sm transition-colors flex items-center gap-2">
                    <span wire:loading wire:target="saveWorkflow" class="animate-spin inline-block w-4 h-4 border-2 border-white/20 border-t-white rounded-full"></span>
                    Save Workflow Configuration
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </button>
            </div>
        @endif

    </div>
</div>
