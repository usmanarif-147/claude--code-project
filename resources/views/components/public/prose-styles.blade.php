{{-- Build-free, themed prose styles for long-form HTML content on the public side.
     Shared by blog and project detail pages. Apply with class="article-prose". --}}
<style>
    .article-prose { color: #d1d5db; line-height: 1.8; font-size: 1.05rem; }
    .article-prose > * + * { margin-top: 1.25rem; }
    .article-prose h2 { color: #fff; font-size: 1.6rem; font-weight: 800; margin-top: 2.5rem; margin-bottom: 0.5rem; }
    .article-prose h3 { color: #fff; font-size: 1.25rem; font-weight: 700; margin-top: 2rem; margin-bottom: 0.5rem; }
    .article-prose p { color: #9ca3af; }
    .article-prose a { color: #a78bfa; text-decoration: underline; }
    .article-prose a:hover { color: #c4b5fd; }
    .article-prose strong { color: #fff; }
    .article-prose ul, .article-prose ol { padding-left: 1.5rem; color: #9ca3af; }
    .article-prose ul { list-style: disc; }
    .article-prose ol { list-style: decimal; }
    .article-prose li { margin-top: 0.4rem; }
    .article-prose blockquote {
        border-left: 3px solid #7c3aed; padding: 0.25rem 0 0.25rem 1.25rem;
        color: #d1d5db; font-style: italic; margin-left: 0;
    }
    .article-prose pre {
        background: #111118; border: 1px solid #1a1a24; border-radius: 0.75rem;
        padding: 1.25rem; overflow-x: auto; font-size: 0.9rem; line-height: 1.6;
    }
    .article-prose code { font-family: 'Fira Code', monospace; color: #c4b5fd; }
    .article-prose pre code { color: #e5e7eb; }
    .article-prose :not(pre) > code {
        background: #1a1a24; padding: 0.15rem 0.4rem; border-radius: 0.35rem; font-size: 0.85em;
    }
</style>
