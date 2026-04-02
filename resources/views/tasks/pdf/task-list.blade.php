<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Task List — {{ $dateRange['label'] }}</title>
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

        /* Date group */
        .date-group { margin-bottom: 16px; }
        .date-heading {
            font-size: 12px;
            font-weight: 700;
            color: #4f46e5;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 4px;
            margin-bottom: 8px;
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

        /* Priority badges (inline) */
        .priority-urgent { color: #dc2626; font-weight: 700; }
        .priority-high { color: #d97706; font-weight: 600; }
        .priority-medium { color: #2563eb; }
        .priority-low { color: #6b7280; }

        /* Status */
        .status-completed { color: #16a34a; font-weight: 600; }
        .status-pending { color: #d97706; }
        .status-in-progress { color: #2563eb; }

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

        /* Footer */
        .footer {
            margin-top: 24px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
            font-size: 8px;
            color: #9ca3af;
            text-align: center;
        }

        /* Section type header */
        .section-type {
            font-size: 13px;
            font-weight: 700;
            color: #1f2937;
            margin-top: 16px;
            margin-bottom: 10px;
            padding-bottom: 4px;
            border-bottom: 1px solid #e5e7eb;
        }

        /* Empty state */
        .empty-state {
            padding: 20px;
            text-align: center;
            color: #9ca3af;
            font-size: 10px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        {{-- Header --}}
        <div class="header">
            <h1>Task List</h1>
            <div class="subtitle">{{ $dateRange['label'] }}</div>
            <div class="meta">Generated on {{ $generatedAt }} | Period: {{ ucfirst($period) }}{{ $taskType !== 'all' ? ' | Type: ' . ucfirst($taskType) : '' }}</div>
        </div>

        <div class="content">
            {{-- Personal Tasks --}}
            @if($taskType === 'all' || $taskType === 'personal')
                @if($taskType === 'all')
                    <div class="section-type">Personal Tasks</div>
                @endif

                @if($personalTasks->isNotEmpty())
                    @foreach($personalTasks as $date => $dateTasks)
                        <div class="date-group">
                            <div class="date-heading">{{ \Carbon\Carbon::parse($date)->format('l, M j, Y') }}</div>
                            <table class="task-table">
                                <thead>
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th style="width: 40%;">Task</th>
                                        <th style="width: 15%;">Priority</th>
                                        <th style="width: 15%;">Status</th>
                                        <th style="width: 25%;">Category</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dateTasks as $index => $task)
                                        <tr class="{{ $task->status === 'completed' ? 'completed' : '' }}">
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $task->title }}</td>
                                            <td>
                                                <span class="priority-{{ $task->priority }}">{{ ucfirst($task->priority) }}</span>
                                            </td>
                                            <td>
                                                <span class="status-{{ $task->status }}">{{ ucfirst(str_replace('_', ' ', $task->status)) }}</span>
                                            </td>
                                            <td>
                                                @if($task->category)
                                                    <span class="category">{{ $task->category->name }}</span>
                                                @else
                                                    <span style="color: #9ca3af;">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                @else
                    <div class="empty-state">No personal tasks found for this period.</div>
                @endif
            @endif

            {{-- Project Tasks --}}
            @if($taskType === 'all' || $taskType === 'project')
                @if($taskType === 'all')
                    <div class="section-type">Project Tasks</div>
                @endif

                @if($projectTasks->isNotEmpty())
                    {{-- Same table structure as personal tasks, rendered when ProjectTask model exists --}}
                @else
                    <div class="empty-state">No project tasks found for this period.</div>
                @endif
            @endif

            {{-- Summary --}}
            @php
                $allTasks = $personalTasks->flatten(1)->merge($projectTasks->flatten(1));
                $totalCount = $allTasks->count();
                $completedCount = $allTasks->where('status', 'completed')->count();
                $pendingCount = $allTasks->where('status', '!=', 'completed')->count();
            @endphp
            @if($totalCount > 0)
                <div class="summary">
                    <h3>Summary</h3>
                    <div class="summary-row">Total tasks: <span class="summary-value">{{ $totalCount }}</span></div>
                    <div class="summary-row">Completed: <span class="summary-value" style="color: #16a34a;">{{ $completedCount }}</span></div>
                    <div class="summary-row">Pending: <span class="summary-value" style="color: #d97706;">{{ $pendingCount }}</span></div>
                    @if($totalCount > 0)
                        <div class="summary-row">Completion rate: <span class="summary-value">{{ round(($completedCount / $totalCount) * 100) }}%</span></div>
                    @endif
                </div>
            @endif

            <div class="footer">
                Generated from Task Manager | {{ $generatedAt }}
            </div>
        </div>
    </div>
</body>
</html>
