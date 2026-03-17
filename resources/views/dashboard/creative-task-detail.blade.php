@extends('layouts.app-dashboard')

@section('title', 'Task Detail: ' . $task->title)

@section('content')
    <div class="animate-fade-in-up">
        <!-- Header -->
        <div class="mb-6 flex justify-between items-center">
            <div>
                <a href="{{ route('dashboard.creative') }}"
                    class="text-stone-500 hover:text-stone-700 dark:hover:text-stone-300">
                    <i class="fa-solid fa-arrow-left mr-2"></i>Back to Creative Studio
                </a>
                <h2 class="text-2xl font-bold text-stone-900 dark:text-white mt-2">{{ $task->title }}</h2>
                <div class="flex items-center gap-2 mt-1">
                    <span
                        class="text-sm px-2 py-1 rounded-full bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400">
                        {{ $task->project_type_label }}
                    </span>
                    <span class="text-sm text-stone-500">{{ $task->category }}</span>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <!-- Progress -->
                <div class="text-center">
                    <div class="text-2xl font-bold text-stone-900 dark:text-white">{{ $task->progress }}%</div>
                    <div class="text-xs text-stone-500">Progress</div>
                </div>

                <!-- Complete Button -->
                <button onclick="completeTask({{ $task->id }})"
                    class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg font-medium">
                    <i class="fa-solid fa-check mr-2"></i>Mark Complete
                </button>
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: Subtasks -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-bold text-stone-800 dark:text-white">Workflow Stages</h3>
                        <button onclick="showAddSubtaskModal()"
                            class="px-3 py-1 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-sm">
                            <i class="fa-solid fa-plus mr-1"></i>Add Stage
                        </button>
                    </div>

                    <!-- Subtasks List -->
                    <div class="space-y-3">
                        @foreach ($task->subtasks as $subtask)
                            <div class="subtask-item p-4 border border-stone-200 dark:border-stone-700 rounded-lg"
                                data-subtask-id="{{ $subtask->id }}">
                                <div class="flex items-start">
                                    <!-- Checkbox -->
                                    <div class="mr-4">
                                        <input type="checkbox" onchange="toggleSubtaskStatus({{ $subtask->id }}, this)"
                                            {{ $subtask->status === 'done' ? 'checked' : '' }}
                                            class="w-5 h-5 rounded border-2 border-stone-300 checked:bg-emerald-500">
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-1">
                                        <div class="flex justify-between items-start">
                                            <h4
                                                class="font-medium text-stone-800 dark:text-white {{ $subtask->status === 'done' ? 'line-through text-stone-500' : '' }}">
                                                {{ $subtask->getStageName() }}
                                            </h4>

                                            <div class="flex items-center gap-2">
                                                @if ($subtask->estimated_minutes)
                                                    <span class="text-xs text-stone-500">
                                                        <i
                                                            class="fa-solid fa-clock mr-1"></i>{{ $subtask->estimated_minutes }}m
                                                    </span>
                                                @endif

                                                @if ($subtask->status === 'done' && $subtask->completed_at)
                                                    <span class="text-xs text-emerald-600">
                                                        <i
                                                            class="fa-solid fa-check mr-1"></i>{{ $subtask->completed_at->format('H:i') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        <p class="text-sm text-stone-600 dark:text-stone-400 mt-1">
                                            {{ $subtask->description }}</p>

                                        <!-- Progress Bar -->
                                        <div class="mt-3">
                                            <div class="flex justify-between text-xs text-stone-500 mb-1">
                                                <span>Progress</span>
                                                <span>{{ $subtask->progress }}%</span>
                                            </div>
                                            <div class="w-full bg-stone-200 dark:bg-stone-700 rounded-full h-2">
                                                <div class="bg-orange-500 h-2 rounded-full"
                                                    style="width: {{ $subtask->progress }}%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        @if ($task->subtasks->count() === 0)
                            <div class="text-center py-8">
                                <i class="fa-solid fa-tasks text-3xl text-stone-300 dark:text-stone-700 mb-3"></i>
                                <p class="text-stone-500 dark:text-stone-400">No workflow stages yet</p>
                                <button onclick="createDefaultSubtasks({{ $task->id }})"
                                    class="mt-3 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm">
                                    Create Default Workflow
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Task Description -->
                @if ($task->description)
                    <div
                        class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800 mt-6">
                        <h3 class="text-lg font-bold text-stone-800 dark:text-white mb-3">Description</h3>
                        <div class="prose dark:prose-invert">
                            {{ $task->description }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Right: Task Info & Productivity Log -->
            <div class="space-y-6">
                <!-- Task Info Card -->
                <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
                    <h3 class="text-lg font-bold text-stone-800 dark:text-white mb-4">Task Info</h3>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-stone-500 dark:text-stone-400">Due Date</label>
                            <div class="flex items-center mt-1">
                                <i class="fa-solid fa-calendar text-stone-400 mr-2"></i>
                                <span class="font-medium {{ $task->is_overdue ? 'text-red-600' : '' }}">
                                    {{ $task->due_date ? $task->due_date->format('d M Y') : 'No deadline' }}
                                </span>
                                @if ($task->is_overdue)
                                    <span
                                        class="ml-2 text-xs bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 px-2 py-1 rounded-full">
                                        Overdue
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm text-stone-500 dark:text-stone-400">Priority</label>
                            <div class="flex items-center mt-1">
                                <div
                                    class="w-3 h-3 rounded-full mr-2
                                @if ($task->priority === 'urgent-important') bg-red-500
                                @elseif($task->priority === 'important-not-urgent') bg-blue-500
                                @elseif($task->priority === 'urgent-not-important') bg-amber-500
                                @else bg-stone-400 @endif">
                                </div>
                                <span>{{ $task->priority_label }}</span>
                            </div>
                        </div>

                        @if ($task->estimated_time)
                            <div>
                                <label class="block text-sm text-stone-500 dark:text-stone-400">Estimated Time</label>
                                <div class="flex items-center mt-1">
                                    <i class="fa-solid fa-clock text-stone-400 mr-2"></i>
                                    <span>{{ $task->estimated_time }}</span>
                                </div>
                            </div>
                        @endif

                        @if ($task->actual_time)
                            <div>
                                <label class="block text-sm text-stone-500 dark:text-stone-400">Actual Time</label>
                                <div class="flex items-center mt-1">
                                    <i class="fa-solid fa-check-circle text-emerald-400 mr-2"></i>
                                    <span class="text-emerald-600">{{ $task->actual_time }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 border border-stone-200 dark:border-stone-800">
                    <h3 class="text-lg font-bold text-stone-800 dark:text-white mb-4">Recent Activity</h3>

                    <div class="space-y-3">
                        @php
                            $logs = App\Models\ProductivityLog::where('task_id', $task->id)
                                ->orderBy('created_at', 'desc')
                                ->limit(5)
                                ->get();
                        @endphp

                        @foreach ($logs as $log)
                            <div class="flex items-start">
                                <div class="mr-3 mt-1">
                                    <div
                                        class="w-8 h-8 rounded-full flex items-center justify-center
                                    @if ($log->activity_type === 'task_completed') bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600
                                    @elseif($log->activity_type === 'subtask_completed') bg-blue-100 dark:bg-blue-900/30 text-blue-600
                                    @else bg-stone-100 dark:bg-stone-800 text-stone-600 @endif">
                                        <i class="fa-solid {{ $log->getActivityIcon() }} text-sm"></i>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm text-stone-800 dark:text-white">
                                        {!! $log->getFormattedDetails() !!}
                                    </p>
                                    <p class="text-xs text-stone-500 mt-1">
                                        {{ $log->created_at->diffForHumans() }}
                                        @if ($log->work_minutes)
                                            • {{ $log->work_minutes }} minutes
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach

                        @if ($logs->count() === 0)
                            <p class="text-center py-4 text-stone-500 dark:text-stone-400">
                                No activity yet
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Subtask Modal -->
    <div id="add-subtask-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center p-4 z-50">
        <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-md" onclick="event.stopPropagation()">
            <div class="p-6 border-b border-stone-200 dark:border-stone-800">
                <h3 class="text-lg font-bold text-stone-800 dark:text-white">Add Workflow Stage</h3>
            </div>

            <form id="add-subtask-form" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="task_id" value="{{ $task->id }}">

                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Stage Name</label>
                    <input type="text" name="title" required
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 dark:bg-stone-800">
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Description</label>
                    <textarea name="description" rows="2"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 dark:bg-stone-800"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Estimated
                        Minutes</label>
                    <input type="number" name="estimated_minutes" min="1" value="60"
                        class="w-full border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 dark:bg-stone-800">
                </div>
            </form>

            <div class="p-6 border-t border-stone-200 dark:border-stone-800 flex justify-end gap-3">
                <button onclick="hideAddSubtaskModal()"
                    class="px-4 py-2 text-stone-600 dark:text-stone-400 hover:text-stone-800 dark:hover:text-stone-200">
                    Cancel
                </button>
                <button onclick="submitSubtaskForm()"
                    class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg">
                    Add Stage
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Modal functions
        function showAddSubtaskModal() {
            document.getElementById('add-subtask-modal').classList.remove('hidden');
            document.getElementById('add-subtask-modal').classList.add('flex');
        }

        function hideAddSubtaskModal() {
            document.getElementById('add-subtask-modal').classList.add('hidden');
            document.getElementById('add-subtask-modal').classList.remove('flex');
            document.getElementById('add-subtask-form').reset();
        }

        // Subtask functions
        async function toggleSubtaskStatus(subtaskId, checkbox) {
            const status = checkbox.checked ? 'done' : 'todo';

            try {
                const response = await fetch(`/dashboard/creative/task/{{ $task->id }}/subtask/${subtaskId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        status: status
                    })
                });

                const data = await response.json();
                if (data.success) {
                    showNotification('Subtask status updated', 'success');
                    // Update progress display if needed
                    if (data.task_progress !== undefined) {
                        // Update progress in UI
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Failed to update subtask', 'error');
            }
        }

        async function submitSubtaskForm() {
            const form = document.getElementById('add-subtask-form');
            const formData = new FormData(form);

            try {
                const response = await fetch('/dashboard/creative/task/{{ $task->id }}/subtask', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                });

                const data = await response.json();
                if (data.success) {
                    showNotification('Subtask added successfully', 'success');
                    hideAddSubtaskModal();
                    setTimeout(() => location.reload(), 1000);
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Failed to add subtask', 'error');
            }
        }

        async function createDefaultSubtasks(taskId) {
            if (!confirm('Create default workflow stages for this project type?')) return;

            try {
                const response = await fetch(`/dashboard/creative/task/${taskId}/create-default-subtasks`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    showNotification('Default workflow created', 'success');
                    setTimeout(() => location.reload(), 1000);
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Failed to create workflow', 'error');
            }
        }

        async function completeTask(taskId) {
            if (!confirm('Mark this task as complete?')) return;

            try {
                const response = await fetch(`/tasks/${taskId}/complete`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    showNotification('Task marked as complete!', 'success');
                    setTimeout(() => location.href = '/dashboard/creative', 1500);
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('Failed to complete task', 'error');
            }
        }

        // Close modal on background click
        document.getElementById('add-subtask-modal').addEventListener('click', hideAddSubtaskModal);
    </script>
@endpush
