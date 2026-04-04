<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $board->name }} — Project Board Export</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #2d2d2d;
            background: #ffffff;
        }
        .container { width: 100%; padding: 0; }

        /* Header */
        .header {
            background-color: #1a1a2e;
            color: #ffffff;
            padding: 20px 30px;
        }
        .header h1 {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 4px;
            letter-spacing: 0.5px;
        }
        .header .subtitle {
            font-size: 11px;
            color: #a5b4fc;
            margin-bottom: 2px;
        }
        .header .meta {
            font-size: 9px;
            color: #d1d5db;
        }

        /* Content */
        .content { padding: 20px 30px; }

        /* Column group */
        .column-group { margin-bottom: 18px; }
        .column-heading {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 6px 10px;
            margin-bottom: 8px;
            border-radius: 3px;
        }
        .column-count {
            font-weight: 400;
            font-size: 10px;
            margin-left: 4px;
        }

        /* Task table */
        .task-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .task-table th {
            background-color: #f3f4f6;
            text-align: left;
            padding: 6px 10px;
            font-size: 9px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e5e7eb;
        }
        .task-table td {
            padding: 6px 10px;
            font-size: 9.5px;
            color: #374151;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }
        .task-table tr.completed td {
            color: #9ca3af;
            text-decoration: line-through;
        }

        /* Priority badges */
        .priority-urgent { color: #dc2626; font-weight: 700; }
        .priority-high { color: #d97706; font-weight: 600; }
        .priority-medium { color: #2563eb; }
        .priority-low { color: #6b7280; }

        /* Category */
        .category {
            display: inline-block;
            padding: 1px 6px;
            background-color: #ede9fe;
            color: #6d28d9;
            border-radius: 3px;
            font-size: 8.5px;
            font-weight: 600;
        }

        /* Tags */
        .tag {
            display: inline-block;
            padding: 1px 5px;
            background-color: #e0e7ff;
            color: #3730a3;
            border-radius: 2px;
            font-size: 8px;
            font-weight: 500;
            margin-right: 2px;
        }

        /* Summary section */
        .summary {
            margin-top: 20px;
            padding: 12px 16px;
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
        }
        .summary h3 {
            font-size: 11px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 6px;
        }
        .summary-row {
            font-size: 9.5px;
            color: #4b5563;
            margin-bottom: 3px;
        }
        .summary-value {
            font-weight: 700;
            color: #1f2937;
        }

        /* Empty state */
        .empty-state {
            padding: 12px;
            text-align: center;
            color: #9ca3af;
            font-size: 10px;
            font-style: italic;
        }

        /* Footer */
        .footer {
            margin-top: 24px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
            font-size: 8px;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <h1>{{ $board->name }}</h1>
            <div class="subtitle">Project Board Export</div>
            <div class="meta">Generated on {{ $generatedAt }}</div>
        </div>

        <div class="content">
            {{-- Column sections --}}
            @foreach($board->columns as $column)
                @php
                    $columnColor = $column->color ?? '#4f46e5';
                    $taskCount = $column->tasks->count();
                    $isCompletedColumn = $column->is_completed_column;
                @endphp
                <div class="column-group">
                    <div class="column-heading" style="color: {{ $columnColor }}; border-bottom: 2px solid {{ $columnColor }}; background-color: {{ $columnColor }}15;">
                        {{ $column->name }}
                        <span class="column-count">({{ $taskCount }} {{ $taskCount === 1 ? 'task' : 'tasks' }})</span>
                    </div>

                    @if($taskCount > 0)
                        <table class="task-table">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 30%;">Task</th>
                                    <th style="width: 12%;">Priority</th>
                                    <th style="width: 13%;">Target Date</th>
                                    <th style="width: 15%;">Category</th>
                                    <th style="width: 25%;">Tags</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($column->tasks as $index => $task)
                                    <tr class="{{ $task->completed_at !== null ? 'completed' : '' }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $task->title }}</td>
                                        <td>
                                            @if($task->priority)
                                                <span class="priority-{{ $task->priority }}">{{ ucfirst($task->priority) }}</span>
                                            @else
                                                <span style="color: #9ca3af;">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($task->target_date)
                                                {{ $task->target_date->format('M j, Y') }}
                                            @else
                                                <span style="color: #9ca3af;">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($task->category)
                                                <span class="category">{{ $task->category->name }}</span>
                                            @else
                                                <span style="color: #9ca3af;">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if(is_array($task->tags) && count($task->tags) > 0)
                                                @foreach($task->tags as $tag)
                                                    <span class="tag">{{ $tag }}</span>
                                                @endforeach
                                            @else
                                                <span style="color: #9ca3af;">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-state">No tasks in this column</div>
                    @endif
                </div>
            @endforeach

            {{-- Summary --}}
            @if($totalCount > 0)
                <div class="summary">
                    <h3>Summary</h3>
                    <div class="summary-row">Total tasks: <span class="summary-value">{{ $totalCount }}</span></div>
                    <div class="summary-row">Completed: <span class="summary-value" style="color: #16a34a;">{{ $completedCount }}</span></div>
                    <div class="summary-row">Pending: <span class="summary-value" style="color: #d97706;">{{ $pendingCount }}</span></div>
                    <div class="summary-row">Completion rate: <span class="summary-value">{{ $completionRate }}%</span></div>
                </div>
            @endif

            <div class="footer">
                Generated on {{ $generatedAt }}
            </div>
        </div>
    </div>
</body>
</html>
