<?php

namespace App\Http\Controllers;

use App\Models\ProjectManagement\ProjectBoard;

class SharedProjectController extends Controller
{
    public function show(string $token)
    {
        $board = ProjectBoard::query()
            ->where('share_token', $token)
            ->where('is_shared', true)
            ->first();

        if (! $board) {
            abort(404);
        }

        $board->load([
            'columns' => fn ($q) => $q->orderBy('sort_order'),
            'columns.tasks' => fn ($q) => $q->orderBy('position'),
        ]);

        return view('shared.project', [
            'board' => $board,
        ]);
    }
}
