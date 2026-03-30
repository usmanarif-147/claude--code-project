<div>
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white">Blog Posts</h1>
            <p class="text-gray-500 mt-1">Manage your blog articles.</p>
        </div>
        <a href="{{ route('admin.blog.create') }}" wire:navigate
           class="bg-accent-500 hover:bg-accent-600 text-white font-medium rounded-lg px-4 py-2.5 transition-colors text-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            New Post
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search posts..."
                       class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-accent-500 focus:border-transparent text-sm">
            </div>
            <select wire:model.live="statusFilter"
                    class="bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-accent-500 focus:border-transparent text-sm">
                <option value="all">All Status</option>
                <option value="draft">Draft</option>
                <option value="published">Published</option>
            </select>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-dark-700/50">
                        <th class="text-left text-xs font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Cover</th>
                        <th class="text-left text-xs font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Title</th>
                        <th class="text-left text-xs font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Status</th>
                        <th class="text-left text-xs font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Tags</th>
                        <th class="text-left text-xs font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Reading Time</th>
                        <th class="text-left text-xs font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Views</th>
                        <th class="text-left text-xs font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Published</th>
                        <th class="text-right text-xs font-medium text-gray-400 uppercase tracking-wider px-6 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-dark-700">
                    @forelse ($posts as $post)
                        <tr class="hover:bg-dark-700/30 transition-colors">
                            <td class="px-6 py-4">
                                @if ($post->cover_image)
                                    <img src="{{ Storage::url($post->cover_image) }}" alt="{{ $post->title }}" class="w-10 h-10 rounded object-cover">
                                @else
                                    <div class="w-10 h-10 rounded bg-dark-700 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-white font-medium">{{ $post->title }}</div>
                                @if ($post->excerpt)
                                    <div class="text-xs text-gray-400 truncate max-w-[200px]">{{ Str::limit($post->excerpt, 50) }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if ($post->status === 'published')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-500/10 text-green-400">Published</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-400">Draft</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($post->tags->take(3) as $tag)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-accent-500/10 text-accent-400">{{ $tag->tag }}</span>
                                    @endforeach
                                    @if ($post->tags->count() > 3)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-dark-700 text-gray-400">+{{ $post->tags->count() - 3 }}</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-400">{{ $post->reading_time_minutes ?? 0 }} min read</td>
                            <td class="px-6 py-4 text-sm text-gray-400">{{ $post->view_count ?? 0 }}</td>
                            <td class="px-6 py-4 text-sm text-gray-400">
                                @if ($post->published_at)
                                    {{ $post->published_at->format('M d, Y') }}
                                @else
                                    &mdash;
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.blog.edit', $post) }}" wire:navigate
                                       class="text-gray-400 hover:text-accent-400 transition-colors p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                    <button wire:click="delete({{ $post->id }})" wire:confirm="Are you sure you want to delete this post?"
                                            class="text-gray-400 hover:text-red-400 transition-colors p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                No blog posts found. <a href="{{ route('admin.blog.create') }}" wire:navigate class="text-accent-400 hover:underline">Create one</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($posts->hasPages())
            <div class="px-6 py-4 border-t border-dark-700">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
</div>
