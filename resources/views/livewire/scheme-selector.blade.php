<?php

use Livewire\Volt\Component;
use App\Models\Scheme;

new class extends Component {
    public $schemeId = null;
    public $schemes = [];
    
    public function mount()
    {
        $userId = auth()->id();
        $user = auth()->user();
        
        // Fetch scheme IDs the user is mapped to
        $mappedSchemeIds = \App\Models\UserRoleSchemeOfficeMapping::where('user_id', $userId)
            ->pluck('scheme_id')
            ->unique();
            
        $registrar = app(\Spatie\Permission\PermissionRegistrar::class);
        $originalTeamId = $registrar->getPermissionsTeamId();
            
        // Load only those schemes
        $this->schemes = Scheme::whereIn('id', $mappedSchemeIds)
            ->where('allow_entry', true)
            ->where('is_active', true)
            ->get()
            ->filter(function ($scheme) use ($user, $registrar) {
                // Switch the context to the scheme's team
                $registrar->setPermissionsTeamId($scheme->id);
                // Clear the loaded relations so Spatie fetches the correct team permissions
                $user->unsetRelation('roles');
                $user->unsetRelation('permissions');
                
                try {
                    return $user->hasPermissionTo('application entry');
                } catch (\Exception $e) {
                    return false;
                }
            })
            ->values();
            
        // Restore original team ID
        $registrar->setPermissionsTeamId($originalTeamId);
    }
    
    public function updatedSchemeId($value)
    {
        $this->dispatch('scheme-selected', schemeId: $value);
    }
};
?>
<div class="p-6 sm:p-10 bg-slate-50 dark:bg-slate-900/50 rounded-t-xl">
    <div class="max-w-xl">
        <label for="schemeSelect" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Available Schemes</label>
        <select id="schemeSelect" wire:model.live="schemeId" class="block w-full border-slate-300 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 focus:border-amber-500 focus:ring-amber-500 rounded-lg shadow-sm transition-colors">
            <option value="">-- Choose a Scheme --</option>
            @foreach($schemes as $scheme)
                <option value="{{ $scheme->id }}">{{ $scheme->display_name ?? $scheme->name ?? 'Scheme '.$scheme->id }}</option>
            @endforeach
        </select>
    </div>
</div>
