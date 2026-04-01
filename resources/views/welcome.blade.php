<x-layouts.app>

    {{-- ==================== NAVIGATION ==================== --}}
    <nav class="fixed top-0 left-0 right-0 z-50 bg-dark-950/80 backdrop-blur-md border-b border-white/[0.04]"
         x-data="{ open: false }">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-16">
                <a href="#hero" class="text-xl font-extrabold text-white tracking-tight">
                    {{ $user->name ?? 'Portfolio' }}
                </a>

                <div class="hidden md:flex items-center space-x-8">
                    <a href="#about" class="text-sm text-gray-500 hover:text-white transition-colors duration-200">About</a>
                    <a href="#skills" class="text-sm text-gray-500 hover:text-white transition-colors duration-200">Skills</a>
                    <a href="#experience" class="text-sm text-gray-500 hover:text-white transition-colors duration-200">Experience</a>
                    <a href="#projects" class="text-sm text-gray-500 hover:text-white transition-colors duration-200">Projects</a>
                    @if($testimonials->isNotEmpty())
                        <a href="#testimonials" class="text-sm text-gray-500 hover:text-white transition-colors duration-200">Testimonials</a>
                    @endif
                    @if($blogPosts->isNotEmpty())
                        <a href="#blog" class="text-sm text-gray-500 hover:text-white transition-colors duration-200">Blog</a>
                    @endif
                    <a href="#contact" class="text-sm text-gray-500 hover:text-white transition-colors duration-200">Contact</a>
                    <a href="{{ route('resume.download') }}"
                       class="px-5 py-2 bg-accent text-black text-xs font-extrabold uppercase tracking-widest rounded-lg hover:bg-accent-light transition-colors duration-200">
                        Resume
                    </a>
                </div>

                <button @click="open = !open" class="md:hidden text-gray-400 hover:text-white transition-colors">
                    <svg x-show="!open" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="open" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <div x-show="open" x-cloak x-transition class="md:hidden bg-dark-950 border-b border-white/[0.04]">
            <div class="px-4 py-4 space-y-3">
                <a @click="open = false" href="#about" class="block text-gray-400 hover:text-white transition-colors">About</a>
                <a @click="open = false" href="#skills" class="block text-gray-400 hover:text-white transition-colors">Skills</a>
                <a @click="open = false" href="#experience" class="block text-gray-400 hover:text-white transition-colors">Experience</a>
                <a @click="open = false" href="#projects" class="block text-gray-400 hover:text-white transition-colors">Projects</a>
                @if($testimonials->isNotEmpty())
                    <a @click="open = false" href="#testimonials" class="block text-gray-400 hover:text-white transition-colors">Testimonials</a>
                @endif
                @if($blogPosts->isNotEmpty())
                    <a @click="open = false" href="#blog" class="block text-gray-400 hover:text-white transition-colors">Blog</a>
                @endif
                <a @click="open = false" href="#contact" class="block text-gray-400 hover:text-white transition-colors">Contact</a>
                <a href="{{ route('resume.download') }}" class="block text-accent font-bold">Download Resume</a>
            </div>
        </div>
    </nav>

    {{-- ==================== HERO ==================== --}}
    <section id="hero" class="relative min-h-screen flex items-center pt-16 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-accent/[0.03] via-transparent to-transparent"></div>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 w-full relative z-10">
            <div class="flex flex-col md:flex-row items-center gap-12 md:gap-16">
                <div class="flex-1 text-center md:text-left">
                    <p class="text-accent font-mono text-sm mb-4 tracking-wider uppercase animate-fade-in-up">Hi, I'm</p>
                    <h1 class="text-5xl md:text-7xl font-extrabold text-white mb-4 animate-fade-in-up" style="animation-delay: 100ms">
                        {{ $user->name }}
                    </h1>
                    <h2 class="text-xl md:text-2xl text-accent font-semibold mb-6 animate-fade-in-up" style="animation-delay: 200ms">
                        {{ $profile->tagline ?? 'Developer' }}
                    </h2>
                    <p class="text-gray-400 text-lg max-w-xl mb-8 leading-relaxed animate-fade-in-up" style="animation-delay: 300ms">
                        {{ Str::before($profile->bio ?? '', "\n") }}
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start animate-fade-in-up" style="animation-delay: 400ms">
                        <a href="{{ route('resume.download') }}"
                           class="px-8 py-3.5 bg-accent text-black font-extrabold text-sm uppercase tracking-widest rounded-xl hover:bg-accent-light transition-all duration-300 text-center">
                            Download Resume
                        </a>
                        <a href="#projects"
                           class="px-8 py-3.5 border border-accent/30 text-accent hover:bg-accent/10 font-semibold text-sm rounded-xl transition-all duration-300 text-center">
                            View Projects
                        </a>
                    </div>
                </div>
                <div class="flex-shrink-0 animate-float">
                    <div class="relative">
                        <div class="w-64 h-64 md:w-80 md:h-80 rounded-full overflow-hidden border-2 border-accent/20 shadow-2xl shadow-accent/10">
                            @if($profile->profile_image)
                                <img src="{{ asset('storage/' . $profile->profile_image) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full bg-dark-800 flex items-center justify-center">
                                    <span class="text-6xl font-extrabold text-accent/30">{{ substr($user->name, 0, 1) }}</span>
                                </div>
                            @endif
                        </div>
                        <div class="absolute -bottom-2 -right-2 w-20 h-20 bg-accent/10 rounded-full blur-xl"></div>
                        <div class="absolute -top-2 -left-2 w-16 h-16 bg-accent/10 rounded-full blur-xl"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== ABOUT ==================== --}}
    <section id="about" class="py-24 md:py-32">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                 class="opacity-0 translate-y-8 transition-all duration-700">
                <h2 class="text-3xl md:text-4xl font-extrabold text-white text-center mb-16">About Me</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                     class="opacity-0 translate-y-8 transition-all duration-700" style="transition-delay: 100ms">
                    @foreach(explode("\n\n", $profile->bio ?? '') as $paragraph)
                        <p class="text-gray-400 leading-relaxed text-lg {{ !$loop->first ? 'mt-4' : '' }}">
                            {{ $paragraph }}
                        </p>
                    @endforeach
                </div>

                @if($skills->isNotEmpty())
                    <div class="grid grid-cols-2 gap-4">
                        @foreach ($skills as $skill)
                            <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                                 class="opacity-0 translate-y-8 transition-all duration-700 bg-dark-800 border border-white/[0.04] rounded-2xl p-6 text-center hover:border-accent/20 transition-all duration-300"
                                 style="transition-delay: {{ ($loop->index + 2) * 100 }}ms">
                                @if($skill->icon)
                                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-accent/10 border border-accent/20 mb-3">
                                        <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $skill->icon }}"/>
                                        </svg>
                                    </div>
                                @endif
                                <h3 class="text-white font-semibold text-sm">{{ $skill->title }}</h3>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ==================== SKILLS & TECHNOLOGIES ==================== --}}
    <section id="skills" class="py-24 md:py-32">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                 class="opacity-0 translate-y-8 transition-all duration-700">
                <h2 class="text-3xl md:text-4xl font-extrabold text-white text-center mb-16">Skills & Technologies</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @php
                    $categoryLabels = [
                        'frontend' => 'Frontend',
                        'backend' => 'Backend',
                        'database_tools' => 'Database & Tools',
                    ];
                @endphp
                @foreach ($technologies as $category => $techs)
                    <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                         class="opacity-0 translate-y-8 transition-all duration-700 bg-dark-800 border border-white/[0.04] rounded-2xl p-6"
                         style="transition-delay: {{ $loop->index * 100 }}ms">
                        <h3 class="text-lg font-bold text-white mb-5">{{ $categoryLabels[$category] ?? Str::headline(str_replace('_', ' ', $category)) }}</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($techs as $tech)
                                <span class="px-3 py-1.5 bg-accent/10 text-accent-light text-sm rounded-full border border-accent/10 hover:bg-accent/20 transition-colors duration-200">
                                    {{ $tech->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ==================== EXPERIENCE ==================== --}}
    @if($workExperiences->isNotEmpty())
        <section id="experience" class="py-24 md:py-32">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                     class="opacity-0 translate-y-8 transition-all duration-700">
                    <h2 class="text-3xl md:text-4xl font-extrabold text-white text-center mb-16">Experience</h2>
                </div>

                <div class="max-w-3xl mx-auto relative">
                    <div class="absolute left-4 md:left-1/2 top-0 bottom-0 w-px bg-white/[0.06] md:-translate-x-px"></div>

                    @foreach ($workExperiences as $index => $exp)
                        <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                             class="opacity-0 translate-y-8 transition-all duration-700 relative pl-12 md:pl-0 {{ !$loop->last ? 'mb-12' : '' }}"
                             style="transition-delay: {{ ($index + 1) * 100 }}ms">
                            <div class="absolute left-2.5 md:left-1/2 w-3 h-3 bg-accent rounded-full md:-translate-x-1.5 mt-2 ring-4 ring-dark-950"></div>
                            <div class="{{ $index % 2 === 0 ? 'md:w-1/2 md:pr-12' : 'md:w-1/2 md:ml-auto md:pl-12' }}">
                                <div class="bg-dark-800 border border-white/[0.04] rounded-2xl p-6 hover:border-accent/20 transition-all duration-300">
                                    <span class="text-accent font-mono text-sm">
                                        {{ $exp->start_date->format('Y') }} — {{ $exp->is_current ? 'Present' : $exp->end_date->format('Y') }}
                                    </span>
                                    <h3 class="text-xl font-bold text-white mt-1">{{ $exp->role }}</h3>
                                    <p class="text-accent-light font-medium mb-3">{{ $exp->company }}</p>
                                    @if($exp->responsibilities->isNotEmpty())
                                        <ul class="text-gray-400 text-sm space-y-2">
                                            @foreach($exp->responsibilities as $r)
                                                <li class="flex items-start gap-2">
                                                    <span class="text-accent mt-1">&#9656;</span>
                                                    <span>{{ $r->description }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ==================== PROJECTS ==================== --}}
    @if($projects->isNotEmpty())
        <section id="projects" class="py-24 md:py-32">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                     class="opacity-0 translate-y-8 transition-all duration-700">
                    <h2 class="text-3xl md:text-4xl font-extrabold text-white text-center mb-16">Projects</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    @foreach ($projects as $project)
                        <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                             class="opacity-0 translate-y-8 transition-all duration-700 group"
                             style="transition-delay: {{ ($loop->index + 1) * 100 }}ms">
                            <div class="bg-dark-800 border border-white/[0.04] rounded-2xl overflow-hidden hover:border-accent/20 transition-all duration-300 h-full flex flex-col">
                                <div class="relative overflow-hidden">
                                    @if($project->cover_image)
                                        <img src="{{ asset('storage/' . $project->cover_image) }}" alt="{{ $project->title }}" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-500">
                                    @else
                                        <div class="w-full h-48 bg-dark-700 flex items-center justify-center group-hover:bg-dark-600 transition-colors duration-500">
                                            <svg class="w-12 h-12 text-accent/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="absolute inset-0 bg-gradient-to-t from-dark-800 to-transparent"></div>
                                    @if($project->is_featured)
                                        <span class="absolute top-3 right-3 px-2.5 py-1 bg-accent/20 text-accent text-[10px] font-bold uppercase tracking-wider rounded-full border border-accent/20">Featured</span>
                                    @endif
                                </div>
                                <div class="p-6 flex flex-col flex-1">
                                    <h3 class="text-xl font-bold text-white mb-2">{{ $project->title }}</h3>
                                    <p class="text-gray-400 text-sm mb-4 flex-1">{{ $project->short_description }}</p>
                                    <div class="flex flex-wrap gap-2 mb-4">
                                        @foreach($project->tech_stack ?? [] as $tech)
                                            <span class="px-2 py-1 bg-accent/10 text-accent-light text-xs rounded-full">{{ $tech }}</span>
                                        @endforeach
                                    </div>
                                    @if($project->demo_url || $project->github_url)
                                        <div class="flex gap-3 pt-2 border-t border-white/[0.04]">
                                            @if($project->demo_url)
                                                <a href="{{ $project->demo_url }}" target="_blank" rel="noopener" class="text-sm text-accent hover:text-accent-light transition-colors">Live Demo &rarr;</a>
                                            @endif
                                            @if($project->github_url)
                                                <a href="{{ $project->github_url }}" target="_blank" rel="noopener" class="text-sm text-gray-400 hover:text-white transition-colors">GitHub &rarr;</a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ==================== TESTIMONIALS ==================== --}}
    @if($testimonials->isNotEmpty())
        <section id="testimonials" class="py-24 md:py-32">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                     class="opacity-0 translate-y-8 transition-all duration-700">
                    <h2 class="text-3xl md:text-4xl font-extrabold text-white text-center mb-16">Testimonials</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                    @foreach ($testimonials as $testimonial)
                        <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                             class="opacity-0 translate-y-8 transition-all duration-700 bg-dark-800 border border-white/[0.04] rounded-2xl p-6"
                             style="transition-delay: {{ ($loop->index + 1) * 100 }}ms">
                            <div class="flex gap-1 mb-4">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $testimonial->rating ? 'text-accent' : 'text-gray-700' }}" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                @endfor
                            </div>
                            <blockquote class="text-gray-400 text-sm leading-relaxed mb-4">"{{ $testimonial->review }}"</blockquote>
                            <div class="flex items-center gap-3 pt-4 border-t border-white/[0.04]">
                                @if($testimonial->client_photo)
                                    <img src="{{ asset('storage/' . $testimonial->client_photo) }}" alt="{{ $testimonial->client_name }}" class="w-10 h-10 rounded-full object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-full bg-accent/10 flex items-center justify-center">
                                        <span class="text-accent font-bold text-sm">{{ substr($testimonial->client_name, 0, 1) }}</span>
                                    </div>
                                @endif
                                <div>
                                    <p class="text-white font-semibold text-sm">{{ $testimonial->client_name }}</p>
                                    @if($testimonial->company)
                                        <p class="text-gray-500 text-xs">{{ $testimonial->company }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ==================== EDUCATION ==================== --}}
    @if($education->isNotEmpty())
        <section id="education" class="py-24 md:py-32">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                     class="opacity-0 translate-y-8 transition-all duration-700">
                    <h2 class="text-3xl md:text-4xl font-extrabold text-white text-center mb-16">Education</h2>
                </div>

                <div class="max-w-2xl mx-auto space-y-6">
                    @foreach ($education as $edu)
                        <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                             class="opacity-0 translate-y-8 transition-all duration-700"
                             style="transition-delay: {{ ($loop->index + 1) * 100 }}ms">
                            <div class="bg-dark-800 border border-white/[0.04] rounded-2xl p-8 hover:border-accent/20 transition-all duration-300">
                                <div class="flex items-start gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-14 h-14 rounded-xl bg-accent/10 border border-accent/20 flex items-center justify-center">
                                            <svg class="w-7 h-7 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <h3 class="text-xl font-bold text-white">{{ $edu->role }}</h3>
                                        <p class="text-accent-light font-medium mt-1">{{ $edu->company }}</p>
                                        @if($edu->description)
                                            <p class="text-gray-400 text-sm mt-1">{{ $edu->description }}</p>
                                        @endif
                                        <p class="text-gray-500 font-mono text-sm mt-2">
                                            {{ $edu->start_date->format('Y') }} — {{ $edu->end_date ? $edu->end_date->format('Y') : 'Present' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ==================== BLOG ==================== --}}
    @if($blogPosts->isNotEmpty())
        <section id="blog" class="py-24 md:py-32">
            <div class="max-w-6xl mx-auto px-4 sm:px-6">
                <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                     class="opacity-0 translate-y-8 transition-all duration-700">
                    <h2 class="text-3xl md:text-4xl font-extrabold text-white text-center mb-16">Latest Articles</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 max-w-4xl mx-auto">
                    @foreach ($blogPosts as $post)
                        <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                             class="opacity-0 translate-y-8 transition-all duration-700 group"
                             style="transition-delay: {{ ($loop->index + 1) * 100 }}ms">
                            <div class="bg-dark-800 border border-white/[0.04] rounded-2xl p-6 hover:border-accent/20 transition-all duration-300 h-full flex flex-col">
                                <div class="flex items-center gap-3 text-xs text-gray-500 mb-3">
                                    <span>{{ $post->published_at->format('M d, Y') }}</span>
                                    @if($post->reading_time_minutes)
                                        <span>&middot;</span>
                                        <span>{{ $post->reading_time_minutes }} min read</span>
                                    @endif
                                </div>
                                <h3 class="text-lg font-bold text-white mb-2 group-hover:text-accent transition-colors">{{ $post->title }}</h3>
                                <p class="text-gray-400 text-sm flex-1">{{ $post->excerpt }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ==================== CONTACT ==================== --}}
    <section id="contact" class="py-24 md:py-32">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                 class="opacity-0 translate-y-8 transition-all duration-700">
                <h2 class="text-3xl md:text-4xl font-extrabold text-white text-center mb-4">Get In Touch</h2>
                <p class="text-gray-500 text-center max-w-xl mx-auto mb-16">
                    Have a project in mind or want to collaborate? Feel free to reach out.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 max-w-4xl mx-auto">
                <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                     class="opacity-0 translate-y-8 transition-all duration-700 space-y-6"
                     style="transition-delay: 100ms">

                    @if($profile->secondary_email)
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-accent/10 border border-accent/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Email</p>
                                <p class="text-white">{{ $profile->secondary_email }}</p>
                            </div>
                        </div>
                    @endif

                    @if($profile->location)
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-accent/10 border border-accent/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Location</p>
                                <p class="text-white">{{ $profile->location }}</p>
                            </div>
                        </div>
                    @endif

                    @if($profile->availability_status)
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-accent/10 border border-accent/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Availability</p>
                                <p class="text-white">{{ $profile->availability_status }}</p>
                            </div>
                        </div>
                    @endif

                    @if($profile->linkedin_url || $profile->github_url)
                        <div class="flex gap-4 pt-4">
                            @if($profile->github_url)
                                <a href="{{ $profile->github_url }}" target="_blank" rel="noopener"
                                   class="w-10 h-10 rounded-xl bg-dark-800 border border-white/[0.04] flex items-center justify-center text-gray-400 hover:text-white hover:border-accent/20 transition-all">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                                </a>
                            @endif
                            @if($profile->linkedin_url)
                                <a href="{{ $profile->linkedin_url }}" target="_blank" rel="noopener"
                                   class="w-10 h-10 rounded-xl bg-dark-800 border border-white/[0.04] flex items-center justify-center text-gray-400 hover:text-white hover:border-accent/20 transition-all">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                     class="opacity-0 translate-y-8 transition-all duration-700"
                     style="transition-delay: 200ms">
                    <livewire:contact-form />
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== FOOTER ==================== --}}
    <footer class="py-8 border-t border-white/[0.04]">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-gray-600 text-sm">
                    &copy; {{ date('Y') }} {{ $user->name }}. All rights reserved.
                </p>
                <a href="#hero"
                   class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-accent transition-colors duration-200">
                    Back to top
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    </svg>
                </a>
            </div>
        </div>
    </footer>

    <x-chatbot-widget />

</x-layouts.app>
