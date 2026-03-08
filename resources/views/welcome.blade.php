<x-layouts.app>

    {{-- ==================== NAVIGATION ==================== --}}
    <nav class="fixed top-0 left-0 right-0 z-50 bg-dark-900/80 backdrop-blur-md border-b border-dark-700/50"
         x-data="{ open: false }">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-16">
                <a href="#hero" class="text-xl font-bold text-white tracking-tight">
                    <span class="text-accent">U</span>sman<span class="text-accent">.</span>
                </a>

                {{-- Desktop links --}}
                <div class="hidden md:flex items-center space-x-8">
                    <a href="#about" class="text-sm text-gray-400 hover:text-white transition-colors duration-200">About</a>
                    <a href="#skills" class="text-sm text-gray-400 hover:text-white transition-colors duration-200">Skills</a>
                    <a href="#experience" class="text-sm text-gray-400 hover:text-white transition-colors duration-200">Experience</a>
                    <a href="#projects" class="text-sm text-gray-400 hover:text-white transition-colors duration-200">Projects</a>
                    <a href="#achievements" class="text-sm text-gray-400 hover:text-white transition-colors duration-200">Achievements</a>
                    <a href="#education" class="text-sm text-gray-400 hover:text-white transition-colors duration-200">Education</a>
                    <a href="#contact" class="px-4 py-2 bg-accent hover:bg-accent-light text-white text-sm font-medium rounded-lg transition-all duration-300">Contact</a>
                </div>

                {{-- Mobile hamburger --}}
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

        {{-- Mobile menu --}}
        <div x-show="open" x-cloak x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden bg-dark-800 border-b border-dark-700/50">
            <div class="px-4 py-4 space-y-3">
                <a @click="open = false" href="#about" class="block text-gray-400 hover:text-white transition-colors">About</a>
                <a @click="open = false" href="#skills" class="block text-gray-400 hover:text-white transition-colors">Skills</a>
                <a @click="open = false" href="#experience" class="block text-gray-400 hover:text-white transition-colors">Experience</a>
                <a @click="open = false" href="#projects" class="block text-gray-400 hover:text-white transition-colors">Projects</a>
                <a @click="open = false" href="#achievements" class="block text-gray-400 hover:text-white transition-colors">Achievements</a>
                <a @click="open = false" href="#education" class="block text-gray-400 hover:text-white transition-colors">Education</a>
                <a @click="open = false" href="#contact" class="block text-accent hover:text-accent-light transition-colors font-medium">Contact</a>
            </div>
        </div>
    </nav>

    {{-- ==================== HERO ==================== --}}
    <section id="hero" class="relative min-h-screen flex items-center pt-16 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-accent/5 via-transparent to-transparent"></div>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 w-full relative z-10">
            <div class="flex flex-col md:flex-row items-center gap-12">
                <div class="flex-1 text-center md:text-left">
                    <p class="text-accent font-mono text-sm mb-4 animate-fade-in-up">Hi, I'm</p>
                    <h1 class="text-4xl md:text-6xl font-extrabold text-white mb-4 animate-fade-in-up" style="animation-delay: 100ms">
                        Usman Arif
                    </h1>
                    <h2 class="text-xl md:text-2xl text-accent-light font-semibold mb-6 animate-fade-in-up" style="animation-delay: 200ms">
                        Full-Stack Developer
                    </h2>
                    <p class="text-gray-400 text-lg max-w-xl mb-8 leading-relaxed animate-fade-in-up" style="animation-delay: 300ms">
                        Experienced full-stack developer specializing in Laravel, Livewire, and modern web technologies. Passionate about building clean, efficient, and scalable web applications.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start animate-fade-in-up" style="animation-delay: 400ms">
                        <a href="#contact" class="px-8 py-3 bg-accent hover:bg-accent-light text-white font-semibold rounded-lg transition-all duration-300 hover:shadow-lg hover:shadow-accent/25 text-center">
                            Get In Touch
                        </a>
                        <a href="#projects" class="px-8 py-3 border border-accent/50 text-accent hover:bg-accent/10 font-semibold rounded-lg transition-all duration-300 text-center">
                            View Projects
                        </a>
                    </div>
                </div>
                <div class="flex-shrink-0 animate-float">
                    <div class="relative">
                        <div class="w-64 h-64 md:w-80 md:h-80 rounded-full overflow-hidden border-4 border-accent/30 shadow-2xl shadow-accent/10">
                            <img src="https://picsum.photos/400/400?random=1" alt="Usman Arif" class="w-full h-full object-cover">
                        </div>
                        <div class="absolute -bottom-2 -right-2 w-20 h-20 bg-accent/20 rounded-full blur-xl"></div>
                        <div class="absolute -top-2 -left-2 w-16 h-16 bg-accent-light/20 rounded-full blur-xl"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== ABOUT ==================== --}}
    <section id="about" class="py-20 md:py-28">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                 class="opacity-0 translate-y-8 transition-all duration-700">
                <h2 class="text-3xl md:text-4xl font-bold text-white text-center mb-4">About Me</h2>
                <div class="w-16 h-1 bg-accent mx-auto mb-12 rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                     class="opacity-0 translate-y-8 transition-all duration-700" style="transition-delay: 100ms">
                    <p class="text-gray-400 leading-relaxed text-lg">
                        I'm a dedicated Full-Stack Developer with 4+ years of professional experience in building robust web applications. My expertise lies in the Laravel ecosystem, where I leverage tools like Livewire, Filament, and Tailwind CSS to create seamless user experiences.
                    </p>
                    <p class="text-gray-400 leading-relaxed text-lg mt-4">
                        I thrive on transforming complex business requirements into elegant, maintainable code. Whether it's architecting a new system from scratch or optimizing an existing application, I bring a problem-solving mindset and attention to detail.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    @php
                        $strengths = [
                            ['icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z', 'title' => 'Problem Solving', 'delay' => '200ms'],
                            ['icon' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01', 'title' => 'Creativity', 'delay' => '300ms'],
                            ['icon' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15', 'title' => 'Adaptability', 'delay' => '400ms'],
                            ['icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'title' => 'Optimization', 'delay' => '500ms'],
                        ];
                    @endphp

                    @foreach ($strengths as $s)
                        <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                             class="opacity-0 translate-y-8 transition-all duration-700 bg-dark-800 border border-dark-700 rounded-xl p-6 text-center hover:border-accent/50 hover:scale-105 hover:shadow-lg"
                             style="transition-delay: {{ $s['delay'] }}">
                            <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-accent/10 mb-3">
                                <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $s['icon'] }}"/>
                                </svg>
                            </div>
                            <h3 class="text-white font-semibold text-sm">{{ $s['title'] }}</h3>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== SKILLS ==================== --}}
    <section id="skills" class="py-20 md:py-28 bg-dark-800/50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                 class="opacity-0 translate-y-8 transition-all duration-700">
                <h2 class="text-3xl md:text-4xl font-bold text-white text-center mb-4">Skills & Technologies</h2>
                <div class="w-16 h-1 bg-accent mx-auto mb-12 rounded-full"></div>
            </div>

            @php
                $skillGroups = [
                    ['title' => 'Frontend', 'delay' => '100ms', 'skills' => ['HTML5', 'CSS3', 'JavaScript', 'Alpine.js', 'Livewire', 'Tailwind CSS', 'Bootstrap', 'jQuery']],
                    ['title' => 'Backend', 'delay' => '200ms', 'skills' => ['PHP', 'Laravel', 'Filament', 'REST APIs', 'Python', 'Node.js']],
                    ['title' => 'Database & Tools', 'delay' => '300ms', 'skills' => ['MySQL', 'PostgreSQL', 'Redis', 'Git', 'Docker', 'Linux', 'AWS', 'CI/CD']],
                ];
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @foreach ($skillGroups as $group)
                    <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                         class="opacity-0 translate-y-8 transition-all duration-700 bg-dark-800 border border-dark-700 rounded-xl p-6"
                         style="transition-delay: {{ $group['delay'] }}">
                        <h3 class="text-lg font-semibold text-white mb-4">{{ $group['title'] }}</h3>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($group['skills'] as $skill)
                                <span class="px-3 py-1 bg-accent/10 text-accent-light text-sm rounded-full border border-accent/20 hover:bg-accent/20 transition-colors duration-200">
                                    {{ $skill }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ==================== EXPERIENCE ==================== --}}
    <section id="experience" class="py-20 md:py-28">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                 class="opacity-0 translate-y-8 transition-all duration-700">
                <h2 class="text-3xl md:text-4xl font-bold text-white text-center mb-4">Experience</h2>
                <div class="w-16 h-1 bg-accent mx-auto mb-12 rounded-full"></div>
            </div>

            <div class="max-w-3xl mx-auto relative">
                {{-- Timeline line --}}
                <div class="absolute left-4 md:left-1/2 top-0 bottom-0 w-0.5 bg-dark-700 md:-translate-x-0.5"></div>

                {{-- Horizam --}}
                <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                     class="opacity-0 translate-y-8 transition-all duration-700 relative pl-12 md:pl-0 mb-12"
                     style="transition-delay: 100ms">
                    <div class="absolute left-2.5 md:left-1/2 w-3 h-3 bg-accent rounded-full md:-translate-x-1.5 mt-2 ring-4 ring-dark-900"></div>
                    <div class="md:w-1/2 md:pr-12">
                        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 hover:border-accent/50 transition-all duration-300">
                            <span class="text-accent font-mono text-sm">2022 — Present</span>
                            <h3 class="text-xl font-bold text-white mt-1">Full-Stack Developer</h3>
                            <p class="text-accent-light font-medium mb-3">Horizam</p>
                            <ul class="text-gray-400 text-sm space-y-2">
                                <li class="flex items-start gap-2">
                                    <span class="text-accent mt-1.5">▹</span>
                                    <span>Built and maintained multiple Laravel web applications with Livewire and Filament admin panels</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-accent mt-1.5">▹</span>
                                    <span>Implemented REST APIs, payment integrations, and third-party service integrations</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-accent mt-1.5">▹</span>
                                    <span>Optimized database queries and application performance for large-scale systems</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Softenica --}}
                <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                     class="opacity-0 translate-y-8 transition-all duration-700 relative pl-12 md:pl-0"
                     style="transition-delay: 200ms">
                    <div class="absolute left-2.5 md:left-1/2 w-3 h-3 bg-accent-light rounded-full md:-translate-x-1.5 mt-2 ring-4 ring-dark-900"></div>
                    <div class="md:w-1/2 md:ml-auto md:pl-12">
                        <div class="bg-dark-800 border border-dark-700 rounded-xl p-6 hover:border-accent/50 transition-all duration-300">
                            <span class="text-accent font-mono text-sm">2021 — 2022</span>
                            <h3 class="text-xl font-bold text-white mt-1">Software Developer</h3>
                            <p class="text-accent-light font-medium mb-3">Softenica</p>
                            <ul class="text-gray-400 text-sm space-y-2">
                                <li class="flex items-start gap-2">
                                    <span class="text-accent mt-1.5">▹</span>
                                    <span>Developed web applications using Laravel and Vue.js</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-accent mt-1.5">▹</span>
                                    <span>Collaborated with cross-functional teams to deliver client projects on schedule</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-accent mt-1.5">▹</span>
                                    <span>Participated in code reviews and implemented best practices for code quality</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== PROJECTS ==================== --}}
    <section id="projects" class="py-20 md:py-28 bg-dark-800/50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                 class="opacity-0 translate-y-8 transition-all duration-700">
                <h2 class="text-3xl md:text-4xl font-bold text-white text-center mb-4">Projects</h2>
                <div class="w-16 h-1 bg-accent mx-auto mb-12 rounded-full"></div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Autotheory --}}
                <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                     class="opacity-0 translate-y-8 transition-all duration-700 group"
                     style="transition-delay: 100ms">
                    <div class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden hover:border-accent/50 hover:shadow-lg hover:shadow-accent/5 transition-all duration-300">
                        <div class="relative overflow-hidden">
                            <img src="https://picsum.photos/600/300?random=2" alt="Autotheory" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-dark-800 to-transparent"></div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-white mb-2">Autotheory</h3>
                            <p class="text-gray-400 text-sm mb-4">
                                A comprehensive automotive theory learning platform with interactive quizzes, progress tracking, and adaptive learning paths. Built to help users prepare for driving theory exams efficiently.
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <span class="px-2 py-1 bg-accent/10 text-accent-light text-xs rounded-full">Laravel</span>
                                <span class="px-2 py-1 bg-accent/10 text-accent-light text-xs rounded-full">Livewire</span>
                                <span class="px-2 py-1 bg-accent/10 text-accent-light text-xs rounded-full">Tailwind CSS</span>
                                <span class="px-2 py-1 bg-accent/10 text-accent-light text-xs rounded-full">MySQL</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Workforce & Job Management --}}
                <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                     class="opacity-0 translate-y-8 transition-all duration-700 group"
                     style="transition-delay: 200ms">
                    <div class="bg-dark-800 border border-dark-700 rounded-xl overflow-hidden hover:border-accent/50 hover:shadow-lg hover:shadow-accent/5 transition-all duration-300">
                        <div class="relative overflow-hidden">
                            <img src="https://picsum.photos/600/300?random=3" alt="Workforce & Job Management" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-500">
                            <div class="absolute inset-0 bg-gradient-to-t from-dark-800 to-transparent"></div>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-bold text-white mb-2">Workforce & Job Management System</h3>
                            <p class="text-gray-400 text-sm mb-4">
                                An enterprise workforce management platform for scheduling, job tracking, employee management, and reporting. Features real-time dashboards and automated notifications.
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <span class="px-2 py-1 bg-accent/10 text-accent-light text-xs rounded-full">Laravel</span>
                                <span class="px-2 py-1 bg-accent/10 text-accent-light text-xs rounded-full">Filament</span>
                                <span class="px-2 py-1 bg-accent/10 text-accent-light text-xs rounded-full">Alpine.js</span>
                                <span class="px-2 py-1 bg-accent/10 text-accent-light text-xs rounded-full">REST API</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== ACHIEVEMENTS ==================== --}}
    <section id="achievements" class="py-20 md:py-28">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                 class="opacity-0 translate-y-8 transition-all duration-700">
                <h2 class="text-3xl md:text-4xl font-bold text-white text-center mb-4">Achievements</h2>
                <div class="w-16 h-1 bg-accent mx-auto mb-12 rounded-full"></div>
            </div>

            @php
                $stats = [
                    ['number' => '11+', 'label' => 'Projects Completed', 'delay' => '100ms'],
                    ['number' => '7', 'label' => 'Laravel Builds', 'delay' => '200ms'],
                    ['number' => '4+', 'label' => 'Years Experience', 'delay' => '300ms'],
                    ['number' => '10', 'label' => 'Fiverr Orders', 'delay' => '400ms'],
                ];
            @endphp

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                @foreach ($stats as $stat)
                    <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                         class="opacity-0 translate-y-8 transition-all duration-700 bg-dark-800 border border-dark-700 rounded-xl p-6 text-center hover:border-accent/50 hover:scale-105 hover:shadow-lg"
                         style="transition-delay: {{ $stat['delay'] }}">
                        <div class="text-3xl md:text-4xl font-extrabold text-accent mb-2">{{ $stat['number'] }}</div>
                        <div class="text-gray-400 text-sm">{{ $stat['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ==================== EDUCATION ==================== --}}
    <section id="education" class="py-20 md:py-28 bg-dark-800/50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                 class="opacity-0 translate-y-8 transition-all duration-700">
                <h2 class="text-3xl md:text-4xl font-bold text-white text-center mb-4">Education</h2>
                <div class="w-16 h-1 bg-accent mx-auto mb-12 rounded-full"></div>
            </div>

            <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                 class="max-w-2xl mx-auto opacity-0 translate-y-8 transition-all duration-700"
                 style="transition-delay: 100ms">
                <div class="bg-dark-800 border border-dark-700 rounded-xl p-8 hover:border-accent/50 transition-all duration-300">
                    <div class="flex items-start gap-4">
                        <div class="flex-shrink-0">
                            <div class="w-14 h-14 rounded-lg bg-accent/10 flex items-center justify-center">
                                <svg class="w-7 h-7 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white">B.S. Software Engineering</h3>
                            <p class="text-accent-light font-medium mt-1">University of Management and Technology (UMT)</p>
                            <p class="text-gray-400 text-sm mt-1">Lahore, Pakistan</p>
                            <p class="text-gray-500 font-mono text-sm mt-2">2016 — 2021</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== CONTACT ==================== --}}
    <section id="contact" class="py-20 md:py-28">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                 class="opacity-0 translate-y-8 transition-all duration-700">
                <h2 class="text-3xl md:text-4xl font-bold text-white text-center mb-4">Get In Touch</h2>
                <div class="w-16 h-1 bg-accent mx-auto mb-6 rounded-full"></div>
                <p class="text-gray-400 text-center max-w-xl mx-auto mb-12">
                    Have a project in mind or want to collaborate? Feel free to reach out — I'd love to hear from you.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 max-w-4xl mx-auto">
                {{-- Contact Info --}}
                <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                     class="opacity-0 translate-y-8 transition-all duration-700 space-y-6"
                     style="transition-delay: 100ms">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-accent/10 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <p class="text-white">usman@example.com</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-accent/10 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Location</p>
                            <p class="text-white">Lahore, Pakistan</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-lg bg-accent/10 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Availability</p>
                            <p class="text-white">Open to opportunities</p>
                        </div>
                    </div>
                </div>

                {{-- Livewire Contact Form --}}
                <div x-data x-intersect.once="$el.classList.add('opacity-100', 'translate-y-0')"
                     class="opacity-0 translate-y-8 transition-all duration-700"
                     style="transition-delay: 200ms">
                    <livewire:contact-form />
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== FOOTER ==================== --}}
    <footer class="py-8 border-t border-dark-700/50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-gray-500 text-sm">
                    &copy; {{ date('Y') }} Usman Arif. Built with Laravel, Livewire & Tailwind CSS.
                </p>
                <a href="#hero"
                   class="inline-flex items-center gap-2 text-sm text-gray-400 hover:text-accent transition-colors duration-200">
                    Back to top
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                    </svg>
                </a>
            </div>
        </div>
    </footer>

</x-layouts.app>
