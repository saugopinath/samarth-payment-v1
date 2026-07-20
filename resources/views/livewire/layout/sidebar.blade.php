<?php

use Livewire\Volt\Component;

new class extends Component
{
    // Sidebar logic
}; ?>

<aside 
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 text-white transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 flex flex-col shadow-xl"
>
    <!-- Sidebar Header -->
    <div class="flex items-center justify-center h-16 bg-slate-950 border-b border-slate-800">
        <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center gap-2 px-4 w-full">
            <x-application-logo class="h-8 w-auto fill-current text-amber-500" />
            <span class="text-xl font-bold tracking-wider text-white uppercase truncate">Samarth</span>
        </a>
    </div>

    <!-- Sidebar Navigation -->
    <div class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
        
        <!-- Dashboard Link -->
        <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('dashboard') ? 'bg-amber-500 text-white shadow-md' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            <span class="font-medium">Dashboard</span>
        </a>

        <!-- Menu Section: Management -->
       

        <!-- Submenu Example: Users -->
        <div x-data="{ open: {{ request()->routeIs('management.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('management.*') ? 'bg-slate-800/50 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span class="font-medium">Users & Roles</span>
                </div>
                <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div x-show="open" x-collapse class="pl-11 pr-3 py-1 space-y-1">
                <a href="{{ route('management.users') }}" wire:navigate class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('management.users') ? 'bg-amber-500 text-white font-medium shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Users</a>
                <a href="{{ route('management.roles') }}" wire:navigate class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('management.roles') ? 'bg-amber-500 text-white font-medium shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Roles & Ranks</a>
                <a href="{{ route('management.permissions') }}" wire:navigate class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('management.permissions') ? 'bg-amber-500 text-white font-medium shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Permissions</a>
                <a href="{{ route('management.role-office-mappings') }}" wire:navigate class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('management.role-office-mappings') ? 'bg-amber-500 text-white font-medium shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Role Office Mappings</a>
                <a href="{{ route('management.departments') }}" wire:navigate class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('management.departments') ? 'bg-amber-500 text-white font-medium shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Departments</a>
                <a href="{{ route('management.offices') }}" wire:navigate class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('management.offices') ? 'bg-amber-500 text-white font-medium shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Offices</a>
                <a href="{{ route('management.schemes') }}" wire:navigate class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('management.schemes') ? 'bg-amber-500 text-white font-medium shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Schemes Config</a>
                <a href="{{ route('management.scheme-doc-mappings') }}" wire:navigate class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('management.scheme-doc-mappings') ? 'bg-amber-500 text-white font-medium shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Scheme Doc Mappings</a>
                <a href="{{ route('management.codemasters') }}" wire:navigate class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('management.codemasters') ? 'bg-amber-500 text-white font-medium shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Codemasters</a>
                <a href="{{ route('management.document-types') }}" wire:navigate class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('management.document-types') ? 'bg-amber-500 text-white font-medium shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Document Types</a>
            </div>
        </div>
        

       
     

        <div x-data="{ open: {{ request()->routeIs('DynamicWorkflow.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('DynamicWorkflow.*') ? 'bg-slate-800/50 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <span class="font-medium">Workflows</span>
                </div>
                <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div x-show="open" x-collapse class="pl-11 pr-3 py-1 space-y-1">
                <a href="{{ route('DynamicWorkflow.index') }}" wire:navigate class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('DynamicWorkflow.index') ? 'bg-amber-500 text-white font-medium shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Workflow List</a>
                <a href="{{ route('DynamicWorkflow.wizard') }}" wire:navigate class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('DynamicWorkflow.wizard') ? 'bg-amber-500 text-white font-medium shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Workflow Wizard</a>
            </div>
        </div>

        <!-- Dynamic Forms / Schemes -->
        <div x-data="{ open: {{ request()->routeIs('DynamicForm.*') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('DynamicForm.*') ? 'bg-slate-800/50 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span class="font-medium">Pension Schemes</span>
                </div>
                <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div x-show="open" x-collapse class="pl-11 pr-3 py-1 space-y-1">
                <a href="{{ route('DynamicForm.index') }}" wire:navigate class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('DynamicForm.index') ? 'bg-amber-500 text-white font-medium shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Apply for Scheme</a>
            </div>
        </div>

        <!-- Payments -->
        <div x-data="{ open: {{ request()->routeIs('admin.payment-lot-generation', 'admin.block-unblock-payment', 'admin.map-financial-year', 'admin.financial-year-months') ? 'true' : 'false' }} }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg transition-colors {{ request()->routeIs('admin.payment-lot-generation', 'admin.block-unblock-payment', 'admin.map-financial-year', 'admin.financial-year-months') ? 'bg-slate-800/50 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span class="font-medium">Payments</span>
                </div>
                <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div x-show="open" x-collapse class="pl-11 pr-3 py-1 space-y-1">
                <a href="{{ route('admin.payment-lot-generation') }}" wire:navigate class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.payment-lot-generation') ? 'bg-amber-500 text-white font-medium shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Lot Generation</a>
                <a href="{{ route('admin.block-unblock-payment') }}" wire:navigate class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.block-unblock-payment') ? 'bg-amber-500 text-white font-medium shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Block-Unblock</a>
                <a href="{{ route('admin.map-financial-year') }}" wire:navigate class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.map-financial-year') ? 'bg-amber-500 text-white font-medium shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Map Fin Year</a>
                <a href="{{ route('admin.financial-year-months') }}" wire:navigate class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.financial-year-months') ? 'bg-amber-500 text-white font-medium shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">Fin Year Months</a>
            </div>
        </div>
        
        <!-- Submenu Example: Settings -->
        <div x-data="{ open: false }">
            <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white transition-colors">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span class="font-medium">Settings</span>
                </div>
                <svg :class="{'rotate-180': open}" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>
            <div x-show="open" x-collapse class="pl-11 pr-3 py-1 space-y-1">
                <a href="#" class="block px-3 py-2 text-sm text-slate-400 rounded-lg hover:text-white hover:bg-slate-800 transition-colors">General Settings</a>
                <a href="#" class="block px-3 py-2 text-sm text-slate-400 rounded-lg hover:text-white hover:bg-slate-800 transition-colors">Security</a>
            </div>
        </div>
    </div>
</aside>
