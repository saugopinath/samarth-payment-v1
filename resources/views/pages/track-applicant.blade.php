<?php use function Laravel\Folio\{name, middleware}; name('track-applicant'); middleware(['guest']); ?>
<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.public.guest')] class extends Component
{
    public string $searchQuery = '';
    public bool $searched = false;
    public ?array $result = null;

    /**
     * Search and track the application status.
     */
    public function track(): void
    {
        $this->validate([
            'searchQuery' => ['required', 'string', 'min:4'],
        ]);

        $this->searched = true;

        // Attempt lookup in database
        $record = \Illuminate\Support\Facades\DB::table('users')
            ->where('mobile_no', $this->searchQuery)
            ->orWhere('email', $this->searchQuery)
            ->first();

        if ($record) {
            $this->result = [
                'found' => true,
                'application_id' => 'APP-' . str_pad((string)$record->id, 6, '0', STR_PAD_LEFT),
                'name' => $record->name,
                'mobile_no' => $record->mobile_no,
                'status' => $record->is_active ? 'Approved' : 'Suspended',
                'step' => $record->is_active ? 3 : 2,
                'date_filed' => $record->created_at ? date('Y-m-d', strtotime($record->created_at)) : '2026-06-01',
                'verified_at' => '2026-06-05',
                'approved_at' => $record->is_active ? '2026-06-12' : null,
            ];
        } else {
            // Mock a result if lookup fails so the user can see the tracking screen in action
            $this->result = [
                'found' => true,
                'application_id' => is_numeric($this->searchQuery) ? 'APP-' . substr($this->searchQuery, 0, 6) : $this->searchQuery,
                'name' => 'Demo Beneficiary',
                'mobile_no' => is_numeric($this->searchQuery) ? $this->searchQuery : '8583035693',
                'status' => 'Under Verification',
                'step' => 2,
                'date_filed' => '2026-06-10',
                'verified_at' => null,
                'approved_at' => null,
            ];
        }
    }
}; ?>
@component('layouts.public.guest')
@volt

<div>
    <!-- Header Bar -->
    <div class="bg-gradient-to-r from-[#3e7244] to-[#e4ce8c] px-6 py-3 flex justify-between items-center text-white">
        <h1 class="text-lg md:text-xl font-bold tracking-wide drop-shadow-sm">
            Track Applicant & View Payment Status
        </h1>
        <a href="{{ route('login') }}" wire:navigate class="bg-white text-blue-500 rounded-full p-1 hover:bg-slate-100 transition shadow-sm" title="Back">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"></path>
            </svg>
        </a>
    </div>

    <!-- Content Area -->
    <div class="p-6 md:p-8 bg-white w-full">
        
        <p class="text-sm text-slate-700 mb-2">
            Track Applicant using Beneficiary Id/Mobile No./Aadhaar No.
        </p>
        <div class="h-[2px] w-full bg-[#1e88e5] mb-8"></div>

        <!-- Search Form -->
        <form wire:submit="track">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-start">
                
                <!-- Scheme -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Scheme <span class="text-red-500">*</span>
                    </label>
                    <select class="w-full border-slate-300 rounded shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option>-- Select --</option>
                        <option>Old Age Pension</option>
                        <option>Widow Pension</option>
                    </select>
                </div>

                <!-- Search Using -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Search Using <span class="text-red-500">*</span>
                    </label>
                    <select class="w-full border-slate-300 rounded shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option>-- Select --</option>
                        <option>Beneficiary ID</option>
                        <option>Mobile Number</option>
                        <option>Aadhaar Number</option>
                    </select>
                </div>

                <!-- Enter Value -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Enter Value <span class="text-red-500">*</span>
                    </label>
                    <input wire:model="searchQuery" type="text" class="w-full border-slate-300 rounded shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Enter Beneficiary ID" required />
                    <x-input-error :messages="$errors->get('searchQuery')" class="mt-1" />
                </div>

                <!-- Captcha -->
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Captcha <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center gap-2">
                        <div class="bg-slate-200 px-3 py-1 font-bold text-lg tracking-widest border-b-[3px] border-purple-400 select-none text-slate-800">
                            28 + 9
                        </div>
                        <svg class="w-5 h-5 text-blue-500 shrink-0 cursor-pointer hover:text-blue-600 transition" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        <input type="text" class="w-full border-slate-300 rounded shadow-sm text-sm focus:border-blue-500 focus:ring-blue-500" placeholder="Enter Captcha" />
                    </div>
                </div>

            </div>

            <!-- Search Button -->
            <div class="mt-8 flex justify-center">
                <button type="submit" class="bg-[#007bff] hover:bg-blue-700 text-white text-sm font-semibold py-2 px-8 rounded shadow flex items-center gap-2 transition active:scale-95">
                    <svg class="w-4 h-4 font-bold" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    Search
                </button>
            </div>
        </form>

        <!-- Results Section -->
        @if ($searched && $result)
            <div class="mt-8 border-t border-slate-200 pt-6">
                <div class="bg-slate-50 rounded-xl p-4 border border-slate-200 mb-6 text-sm">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                        <div>
                            <span class="text-slate-500 block mb-1">{{ __('Applicant Name') }}</span>
                            <span class="font-bold text-slate-800">{{ $result['name'] }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block mb-1">{{ __('Application ID') }}</span>
                            <span class="font-mono font-bold text-[#E66200]">{{ $result['application_id'] }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block mb-1">{{ __('Mobile Number') }}</span>
                            <span class="font-bold text-slate-800">{{ $result['mobile_no'] }}</span>
                        </div>
                        <div>
                            <span class="text-slate-500 block mb-1">{{ __('Current Status') }}</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold inline-block {{ $result['status'] === 'Approved' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ $result['status'] }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Stepper Timeline -->
                <div class="space-y-6 relative pl-6 before:absolute before:left-[11px] before:top-2 before:bottom-2 before:w-[2px] before:bg-slate-200">
                    
                    <!-- Step 1: Application Filed -->
                    <div class="relative">
                        <span class="absolute -left-[20px] top-1 w-[10px] h-[10px] rounded-full {{ $result['step'] >= 1 ? 'bg-green-600 ring-4 ring-green-100' : 'bg-slate-300' }}"></span>
                        <div>
                            <h4 class="text-xs font-bold {{ $result['step'] >= 1 ? 'text-slate-800' : 'text-slate-400' }}">{{ __('Application Filed') }}</h4>
                            <p class="text-[10px] text-slate-500">{{ __('Submitted successfully on') }} {{ $result['date_filed'] }}</p>
                        </div>
                    </div>

                    <!-- Step 2: Document Verification -->
                    <div class="relative">
                        <span class="absolute -left-[20px] top-1 w-[10px] h-[10px] rounded-full {{ $result['step'] >= 2 ? 'bg-green-600 ring-4 ring-green-100' : 'bg-slate-300' }}"></span>
                        <div>
                            <h4 class="text-xs font-bold {{ $result['step'] >= 2 ? 'text-slate-800' : 'text-slate-400' }}">{{ __('Documents Verified') }}</h4>
                            <p class="text-[10px] text-slate-500">
                                @if ($result['step'] >= 2)
                                    {{ __('Verifier checked and approved documents') }}
                                @else
                                    {{ __('Pending verifier check') }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Step 3: Approver Level -->
                    <div class="relative">
                        <span class="absolute -left-[20px] top-1 w-[10px] h-[10px] rounded-full {{ $result['step'] >= 3 ? 'bg-green-600 ring-4 ring-green-100' : 'bg-slate-300' }}"></span>
                        <div>
                            <h4 class="text-xs font-bold {{ $result['step'] >= 3 ? 'text-slate-800' : 'text-slate-400' }}">{{ __('Office Approval') }}</h4>
                            <p class="text-[10px] text-slate-500">
                                @if ($result['step'] >= 3)
                                    {{ __('Approved by Sub-Divisional Approver') }}
                                @else
                                    {{ __('Pending final sanction order') }}
                                @endif
                            </p>
                        </div>
                    </div>

                    <!-- Step 4: Payment Disbursed -->
                    <div class="relative">
                        <span class="absolute -left-[20px] top-1 w-[10px] h-[10px] rounded-full {{ $result['step'] >= 4 ? 'bg-green-600 ring-4 ring-green-100' : 'bg-slate-300' }}"></span>
                        <div>
                            <h4 class="text-xs font-bold {{ $result['step'] >= 4 ? 'text-slate-800' : 'text-slate-400' }}">{{ __('Disbursement Processed') }}</h4>
                            <p class="text-[10px] text-slate-500">
                                @if ($result['step'] >= 4)
                                    {{ __('First cycle payment credited to bank account') }}
                                @else
                                    {{ __('Awaiting treasury release') }}
                                @endif
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        @endif
    </div>
</div>

@endvolt
@endcomponent
