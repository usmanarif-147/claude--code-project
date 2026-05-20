<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $board->name }} Export</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.5;
            color: #333333;
            background: #ffffff;
            padding: 30px 40px;
        }
        .header {
            text-align: center;
            padding-bottom: 12px;
            border-bottom: 2px solid #7c3aed;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 20px;
            font-weight: 700;
            color: #111111;
            margin-bottom: 4px;
        }
        .header .subtitle {
            font-size: 9px;
            color: #888888;
        }
        .summary {
            display: table;
            width: 100%;
            margin-bottom: 24px;
            border-collapse: collapse;
        }
        .summary-cell {
            display: table-cell;
            width: 25%;
            text-align: center;
            background: #f5f3ff;
            border: 1px solid #e0d9ff;
            padding: 10px 8px;
            border-radius: 6px;
        }
        .summary-cell + .summary-cell {
            margin-left: 8px;
        }
        .summary-value {
            font-size: 18px;
            font-weight: 700;
            color: #7c3aed;
            display: block;
        }
        .summary-label {
            font-size: 8px;
            color: #888888;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .column-section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .column-header {
            display: table;
            width: 100%;
            margin-bottom: 8px;
            padding-bottom: 6px;
            border-bottom: 1px solid #eeeeee;
        }
        .column-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
            vertical-align: middle;
        }
        .column-title {
            font-size: 12px;
            font-weight: 700;
            color: #111111;
            display: inline;
        }
        .column-count {
            font-size: 9px;
            color: #888888;
            margin-left: 6px;
        }
        .empty-column {
            font-size: 9px;
            color: #aaaaaa;
            font-style: italic;
            padding: 6px 0;
        }
        .task {
            border: 1px solid #eeeeee;
            border-radius: 4px;
            padding: 8px 10px;
            margin-bottom: 6px;
            background: #fafafa;
            page-break-inside: avoid;
        }
        .task-title {
            font-size: 10px;
            font-weight: 700;
            color: #111111;
            margin-bottom: 3px;
        }
        .task-title.completed {
            text-decoration: line-through;
            color: #999999;
        }
        .task-description {
            font-size: 9px;
            color: #555555;
            margin-bottom: 5px;
            line-height: 1.4;
        }
        .task-meta {
            font-size: 8px;
            color: #888888;
        }
        .badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: 700;
            color: #ffffff;
            margin-right: 4px;
        }
        .badge-urgent { background: #ef4444; }
        .badge-high   { background: #f97316; }
        .badge-medium { background: #f59e0b; color: #1a1a1a; }
        .badge-low    { background: #6b7280; }
        .meta-item {
            display: inline-block;
            margin-right: 8px;
        }
        .tags {
            margin-top: 3px;
        }
        .tag {
            display: inline-block;
            background: #ede9fe;
            color: #7c3aed;
            border-radius: 3px;
            padding: 1px 5px;
            font-size: 8px;
            margin-right: 3px;
        }
        .divider {
            border-top: 1px solid #eeeeee;
            margin: 16px 0;
        }
        .footer {
            text-align: center;
            font-size: 8px;
            color: #aaaaaa;
            margin-top: 30px;
            padding-top: 10px;
            border-top: 1px solid #eeeeee;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $board->name }}</h1>
        <div class="subtitle">Generated on {{ $generatedAt }}</div>
    </div>

    {{-- Summary stats --}}
    <table class="summary" cellpadding="0" cellspacing="4">
        <tr>
            <td class="summary-cell">
                <span class="summary-value">{{ $totalCount }}</span>
                <span class="summary-label">Total Tasks</span>
            </td>
            <td class="summary-cell">
                <span class="summary-value">{{ $completedCount }}</span>
                <span class="summary-label">Completed</span>
            </td>
            <td class="summary-cell">
                <span class="summary-value">{{ $pendingCount }}</span>
                <span class="summary-label">Pending</span>
            </td>
            <td class="summary-cell">
                <span class="summary-value">{{ $completionRate }}%</span>
                <span class="summary-label">Completion</span>
            </td>
        </tr>
    </table>

    {{-- Columns and tasks --}}
    @foreach ($board->columns as $column)
        <div class="column-section">
            <div class="column-header">
                <span class="column-dot" style="background: {{ $column->color ?? '#7c3aed' }};"></span>
                <span class="column-title">{{ strtoupper($column->name) }}</span>
                <span class="column-count">({{ $column->tasks->count() }} {{ Str::plural('task', $column->tasks->count()) }})</span>
            </div>

            @if ($column->tasks->isEmpty())
                <div class="empty-column">No tasks in this column.</div>
            @else
                @foreach ($column->tasks as $task)
                    <div class="task">
                        <div class="task-title {{ $task->completed_at ? 'completed' : '' }}">
                            {{ $task->title }}
                        </div>

                        @if ($task->description)
                            <div class="task-description">{{ Str::limit($task->description, 200) }}</div>
                        @endif

                        <div class="task-meta">
                            @if ($task->priority)
                                <span class="badge badge-{{ $task->priority }}">{{ ucfirst($task->priority) }}</span>
                            @endif

                            @if ($task->target_date)
                                <span class="meta-item">Due: {{ $task->target_date->format('M j, Y') }}</span>
                            @endif

                            @if ($task->completed_at)
                                <span class="meta-item">Done: {{ $task->completed_at->format('M j, Y') }}</span>
                            @endif
                        </div>

                        @if (is_array($task->tags) && count($task->tags) > 0)
                            <div class="tags">
                                @foreach ($task->tags as $tag)
                                    <span class="tag">{{ $tag }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif
        </div>

        @if (!$loop->last)
            <div class="divider"></div>
        @endif
    @endforeach

    <div class="footer">
        Project Board Export &mdash; {{ $generatedAt }}
    </div>
</body>
</html>
