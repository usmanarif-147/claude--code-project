<div class="w-full max-w-md">
    <div class="bg-dark-800 border border-dark-700 rounded-2xl shadow-xl p-8">
        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-2xl font-mono font-bold text-white uppercase tracking-wider">Welcome Back</h1>
            <p class="text-gray-500 mt-2 text-sm">Sign in to your admin panel</p>
        </div>

        <form wire:submit="login" class="space-y-5">
            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-400 mb-1.5">Email</label>
                <input wire:model="email" type="email" id="email" autocomplete="email"
                       class="w-full px-4 py-2.5 bg-dark-700 border border-dark-600 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                       placeholder="you@example.com">
                @error('email')
                    <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password --}}
            <div>
                <label for="password" class="block text-sm font-medium text-gray-400 mb-1.5">Password</label>
                <input wire:model="password" type="password" id="password" autocomplete="current-password"
                       class="w-full px-4 py-2.5 bg-dark-700 border border-dark-600 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-colors"
                       placeholder="Enter your password">
                @error('password')
                    <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Remember me --}}
            <div class="flex items-center">
                <input wire:model="remember" type="checkbox" id="remember"
                       class="w-4 h-4 rounded border-dark-600 bg-dark-700 text-primary focus:ring-primary focus:ring-offset-0">
                <label for="remember" class="ml-2 text-sm text-gray-400">Remember me</label>
            </div>

            {{-- Submit --}}
            <button type="submit"
                    class="w-full py-2.5 px-4 bg-primary hover:bg-primary-hover text-white font-medium rounded-lg transition-colors flex items-center justify-center gap-2 disabled:opacity-50"
                    wire:loading.attr="disabled">
                <svg wire:loading class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span wire:loading.remove>Sign In</span>
                <span wire:loading>Signing in...</span>
            </button>
        </form>
    </div>
</div>
