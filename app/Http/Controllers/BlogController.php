<?php

namespace App\Http\Controllers;

use App\Support\DummyBlogData;

class BlogController extends Controller
{
    public function index()
    {
        return view('blog.index', [
            // BACKEND: swap for BlogPost::published()->latest('published_at')->paginate(12)
            'posts' => DummyBlogData::paginate(12),
        ]);
    }

    public function show(string $slug)
    {
        // BACKEND: swap for BlogPost::published()->where('slug', $slug)->firstOrFail()
        $post = DummyBlogData::find($slug);

        abort_if($post === null, 404);

        return view('blog.show', [
            'post' => $post,
        ]);
    }
}
