<?php
use function Laravel\Folio\{name, middleware};

name('admin.ifms-payment-settings');
middleware(['auth', 'verified']);
?>
<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-bold text-2xl text-orange-500 leading-tight">
                {{ __('IFMS Payment Settings') }}
            </h2>
            <p class="text-sm text-gray-500 mt-1">Manage API credentials and configuration for IFMS.</p>
        </div>
    </x-slot>

    @volt
    <div class="py-8 bg-[#fdfaf5] min-h-screen">
<?php

new class extends \Livewire\Volt\Component {
    public $settings;
    public $schemes = [];

    public $settingId;
    public $scheme_id = '';
    public $client_id;
    public $client_secret;
    public $party_code;
    public $ddo_code;
    public $hoa_id;
    public $hoa_user;
    public $treasury_code;

    public $isEditMode = false;

    public function mount()
    {
        $this->loadSettings();
        if (class_exists(\App\Models\Scheme::class)) {
            $this->schemes = \App\Models\Scheme::all();
        }
    }

    public function loadSettings()
    {
        $this->settings = \App\Models\IfmsPaymentSetting::all();
    }

    public function save()
    {
        $this->validate([
            'client_id' => 'required',
            'client_secret' => 'required',
        ]);

        \App\Models\IfmsPaymentSetting::updateOrCreate(
            ['id' => $this->settingId],
            [
                'scheme_id' => $this->scheme_id ?: null,
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'party_code' => $this->party_code,
                'ddo_code' => $this->ddo_code,
                'hoa_id' => $this->hoa_id,
                'hoa_user' => $this->hoa_user,
                'treasury_code' => $this->treasury_code,
            ]
        );

        $this->resetFields();
        $this->loadSettings();
        session()->flash('status', 'Settings saved successfully! Sensitive data was encrypted.');
    }

    public function edit($id)
    {
        $setting = \App\Models\IfmsPaymentSetting::findOrFail($id);
        $this->settingId = $setting->id;
        $this->scheme_id = $setting->scheme_id ?? '';
        $this->client_id = $setting->client_id;
        $this->client_secret = $setting->client_secret;
        $this->party_code = $setting->party_code;
        $this->ddo_code = $setting->ddo_code;
        $this->hoa_id = $setting->hoa_id;
        $this->hoa_user = $setting->hoa_user;
        $this->treasury_code = $setting->treasury_code;
        $this->isEditMode = true;
    }

    public function delete($id)
    {
        \App\Models\IfmsPaymentSetting::find($id)?->delete();
        $this->loadSettings();
        session()->flash('status', 'Settings deleted.');
    }

    public function resetFields()
    {
        $this->settingId = null;
        $this->scheme_id = '';
        $this->client_id = null;
        $this->client_secret = null;
        $this->party_code = null;
        $this->ddo_code = null;
        $this->hoa_id = null;
        $this->hoa_user = null;
        $this->treasury_code = null;
        $this->isEditMode = false;
    }
};
?>

<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">IFMS Payment Settings</h2>
    </div>

    @if (session('status'))
        <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
            {{ session('status') }}
        </div>
    @endif

    <!-- Form Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
        <h3 class="text-lg font-semibold mb-4">{{ $isEditMode ? 'Edit Setting' : 'Add New Setting' }}</h3>
        <form wire:submit="save" class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Scheme (Optional)</label>
                <select wire:model="scheme_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Global (Applies to all)</option>
                    @foreach($schemes as $scheme)
                        <option value="{{ $scheme->id }}">{{ $scheme->name ?? $scheme->scheme_name ?? $scheme->id }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Client ID</label>
                <input type="text" wire:model="client_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Client Secret</label>
                <input type="text" wire:model="client_secret" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Party Code</label>
                <input type="text" wire:model="party_code" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">DDO Code</label>
                <input type="text" wire:model="ddo_code" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">HOA ID</label>
                <input type="text" wire:model="hoa_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">HOA User</label>
                <input type="text" wire:model="hoa_user" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Treasury Code</label>
                <input type="text" wire:model="treasury_code" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div class="md:col-span-2 flex justify-end space-x-3 mt-4">
                @if($isEditMode)
                    <button type="button" wire:click="resetFields" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300">Cancel</button>
                @endif
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    {{ $isEditMode ? 'Update Setting' : 'Save Setting' }}
                </button>
            </div>
        </form>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Scheme</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($settings as $setting)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $setting->scheme_id ? 'Scheme ID: ' . $setting->scheme_id : 'Global' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $setting->client_id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                            <button wire:click="edit({{ $setting->id }})" class="text-blue-600 hover:text-blue-900">Edit</button>
                            <button wire:click="delete({{ $setting->id }})" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure?') || event.stopImmediatePropagation()">Delete</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">No settings found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    </div>
</div>
@endvolt
</x-app-layout>
