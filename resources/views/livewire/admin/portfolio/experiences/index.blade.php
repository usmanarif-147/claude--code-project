<div>
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">Experiences</span>
    </div>

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Experiences</h1>
            <p class="text-gray-500 mt-1">Manage your work experience.</p>
        </div>
        <a href="{{ route('admin.experiences.create') }}" wire:navigate
           class="bg-primary hover:bg-primary-hover text-white font-medium rounded-lg px-4 py-2.5 transition-colors text-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Experience
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search by role or company..."
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
            </div>
            <select wire:model.live="typeFilter"
                    class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                <option value="all">All Types</option>
                <option value="work">Work</option>
                <option value="education">Education</option>
            </select>
            <select wire:model.live="activeFilter"
                    class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent text-sm">
                <option value="all">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-dark-700/50">
                        <th class="text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Role</th>
                        <th class="text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Type</th>
                        <th class="text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Company</th>
                        <th class="text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Period</th>
                        <th class="text-left text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Status</th>
                        <th class="text-right text-xs font-mono font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700">
                    @forelse ($experiences as $experience)
                        <tr class="hover:bg-dark-700/30 transition-colors">
                            <td class="px-6 py-4 text-sm text-white font-medium">{{ $experience->role }}</td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $experience->type === 'work' ? 'bg-blue-500/10 text-blue-400' : 'bg-purple-500/10 text-purple-400' }}">
                                    {{ ucfirst($experience->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-400">{{ $experience->company }}</td>
                            <td class="px-6 py-4 text-sm text-gray-400">
                                {{ $experience->start_date->format('M Y') }} — {{ $experience->is_current ? 'Present' : $experience->end_date?->format('M Y') }}
                            </td>
                            <td class="px-6 py-4">
                                @if ($experience->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400">Active</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-500/10 text-gray-400">Inactive</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.experiences.edit', $experience) }}" wire:navigate
                                       class="text-gray-400 hover:text-primary-light transition-colors p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <x-admin.confirm-button
                                        title="Delete Experience?"
                                        text="This experience and all its responsibilities will be permanently removed."
                                        action="$wire.delete({{ $experience->id }})"
                                        confirm-text="Yes, delete it"
                                        class="text-gray-400 hover:text-red-400 transition-colors p-1"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </x-admin.confirm-button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                No experiences found. <a href="{{ route('admin.experiences.create') }}" wire:navigate class="text-primary-light hover:underline">Create one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($experiences->hasPages())
            <div class="px-6 py-4 border-t border-dark-700">
                {{ $experiences->links() }}
            </div>
        @endif
    </div>
</div>
