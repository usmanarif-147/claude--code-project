<div>
    {{-- 1. BREADCRUMB --}}
    <div class="flex items-center gap-2 text-xs text-gray-500 mb-2">
        <a href="{{ route('admin.dashboard') }}" wire:navigate class="hover:text-gray-300 transition-colors">Dashboard</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-400">Job Search</span>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <a href="{{ route('admin.job-search.applications.index') }}" wire:navigate class="hover:text-gray-300 transition-colors">Applications</a>
        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        <span class="text-gray-300">{{ $applicationId ? 'Edit' : 'Create' }}</span>
    </div>

    {{-- 2. PAGE HEADER --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">{{ $applicationId ? 'Edit Application' : 'Create Application' }}</h1>
            <p class="text-gray-500 mt-1">{{ $applicationId ? 'Update application details.' : 'Track a new job application.' }}</p>
        </div>
        <a href="{{ route('admin.job-search.applications.index') }}" wire:navigate
           class="text-gray-400 hover:text-white font-medium rounded-lg px-4 py-2.5 transition-colors text-sm flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    </div>

    {{-- 3. FORM --}}
    <form wire:submit="save">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- MAIN CONTENT (2/3) --}}
            <div class="xl:col-span-2 space-y-6">

                {{-- Job Details Card --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
                    <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-5">Job Details</h2>

                    <div class="space-y-5">
                        {{-- Company --}}
                        <div>
                            <label for="company" class="block text-sm font-medium text-gray-300 mb-1.5">Company <span class="text-red-400">*</span></label>
                            <input type="text" id="company" wire:model="company"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g. Google">
                            @error('company') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        {{-- Position --}}
                        <div>
                            <label for="position" class="block text-sm font-medium text-gray-300 mb-1.5">Position <span class="text-red-400">*</span></label>
                            <input type="text" id="position" wire:model="position"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g. Senior Software Engineer">
                            @error('position') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        {{-- URL --}}
                        <div>
                            <label for="url" class="block text-sm font-medium text-gray-300 mb-1.5">Job URL</label>
                            <input type="url" id="url" wire:model="url"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="https://example.com/jobs/123">
                            @error('url') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        {{-- Salary Offered --}}
                        <div>
                            <label for="salary_offered" class="block text-sm font-medium text-gray-300 mb-1.5">Salary Offered</label>
                            <input type="text" id="salary_offered" wire:model="salary_offered"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="e.g. $120k-$150k or Competitive">
                            @error('salary_offered') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Notes Card --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
                    <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-5">Notes</h2>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-300 mb-1.5">Notes</label>
                        <textarea id="notes" wire:model="notes" rows="6"
                                  class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white placeholder-gray-500 focus:ring-2 focus:ring-primary focus:border-transparent"
                                  placeholder="Interview dates, recruiter contact, feedback, next steps..."></textarea>
                        @error('notes') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- SIDEBAR (1/3) --}}
            <div class="xl:col-span-1 space-y-6">

                {{-- Status & Dates Card --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
                    <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-5">Status & Dates</h2>

                    <div class="space-y-5">
                        {{-- Status --}}
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-300 mb-1.5">Status <span class="text-red-400">*</span></label>
                            <select id="status" wire:model="status"
                                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="saved">Saved</option>
                                <option value="applied">Applied</option>
                                <option value="interview">Interview</option>
                                <option value="offer">Offer</option>
                                <option value="rejected">Rejected</option>
                            </select>
                            @error('status') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        {{-- Applied Date --}}
                        <div>
                            <label for="applied_date" class="block text-sm font-medium text-gray-300 mb-1.5">Applied Date</label>
                            <input type="date" id="applied_date" wire:model="applied_date"
                                   class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                            @error('applied_date') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>

                        {{-- Linked Job Listing --}}
                        <div>
                            <label for="job_listing_id" class="block text-sm font-medium text-gray-300 mb-1.5">Linked Job Listing</label>
                            <select id="job_listing_id" wire:model="job_listing_id"
                                    class="w-full bg-dark-700 border border-dark-600 rounded-lg px-4 py-2.5 text-white focus:ring-2 focus:ring-primary focus:border-transparent">
                                <option value="">None</option>
                                @foreach($jobListings as $listing)
                                    <option value="{{ $listing->id }}">{{ $listing->company_name }} - {{ $listing->title }}</option>
                                @endforeach
                            </select>
                            @error('job_listing_id') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Actions Card --}}
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-6">
                    <h2 class="text-base font-mono font-semibold text-white uppercase tracking-wider mb-5">Actions</h2>

                    <div class="space-y-3">
                        <button type="submit"
                                class="w-full bg-primary hover:bg-primary-hover text-white font-medium rounded-lg px-5 py-2.5 transition-colors flex items-center justify-center gap-2">
                            <span wire:loading.remove wire:target="save">
                                {{ $applicationId ? 'Update Application' : 'Create Application' }}
                            </span>
                            <span wire:loading wire:target="save" class="flex items-center gap-2">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                Saving...
                            </span>
                        </button>
                        <a href="{{ route('admin.job-search.applications.index') }}" wire:navigate
                           class="w-full inline-flex items-center justify-center bg-dark-700 hover:bg-dark-600 text-gray-300 font-medium rounded-lg px-5 py-2.5 transition-colors">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
