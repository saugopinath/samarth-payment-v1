@php
    $strategy = \App\Services\Integration\PaymentIntegrationFactory::make($lot->lot_no);
    $actions = $strategy->getAvailableActions($lot);
@endphp

<button class="text-orange-600 hover:text-orange-900 bg-orange-100 px-3 py-1 rounded-md text-xs font-bold transition-colors">View Details</button>

@foreach($actions as $action)
    @if(isset($action['modal']))
        <button class="text-{{ $action['color'] ?? 'blue' }}-600 hover:text-{{ $action['color'] ?? 'blue' }}-900 bg-{{ $action['color'] ?? 'blue' }}-100 px-3 py-1 rounded-md text-xs font-bold transition-colors inline-flex items-center justify-center"
            @click="$dispatch('open-{{ $action['modal'] }}-modal', { lot_no: '{{ $lot->lot_no }}' })">
            {{ $action['label'] }}
        </button>
    @else
        <button wire:click="executeDynamicAction('{{ $lot->lot_no }}', '{{ $action['key'] }}')" 
            class="text-{{ $action['color'] ?? 'blue' }}-600 hover:text-{{ $action['color'] ?? 'blue' }}-900 bg-{{ $action['color'] ?? 'blue' }}-100 px-3 py-1 rounded-md text-xs font-bold transition-colors inline-flex items-center justify-center">
            
            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
            {{ $action['label'] }}
        </button>
    @endif
@endforeach

@if(str_contains(strtolower(\App\Models\Codemaster::where('code', $lot->payment_mode)->first()?->name ?? ''), 'ifms'))
    @include('pages.Admin.partials.payment-lot-actions.ifms-modal')
@endif
