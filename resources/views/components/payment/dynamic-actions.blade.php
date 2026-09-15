@php
    // Expects $lot to be passed to this component
    $gateway = \App\Services\Payment\PaymentGatewayFactory::make($lot);
    $availableSteps = $gateway->getAvailableSteps($lot);
@endphp

<div class="flex space-x-2">
    @foreach($availableSteps as $step)
        <button wire:click="executeStep('{{ $lot->id ?? $lot->lot_no }}', '{{ $step['key'] }}')" 
                class="px-3 py-1 rounded-md text-xs font-bold transition-colors text-white bg-{{ $step['color'] ?? 'blue' }}-600 hover:bg-{{ $step['color'] ?? 'blue' }}-700">
            {{ $step['label'] }}
        </button>
    @endforeach
</div>
