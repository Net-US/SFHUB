@extends('layouts.app-dashboard')

@section('title', 'Focus Today - StudentHub')

@section('content')
    <div class="min-h-screen bg-stone-50 dark:bg-stone-950 transition-colors duration-300">
        <!-- Main Focus Dashboard -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 p-4 md:p-8 animate-fade-in-up">
            <!-- Main Focus Card -->
            <div
                class="lg:col-span-2 bg-gradient-to-br from-stone-800 to-stone-900 dark:from-stone-800 dark:to-black text-white rounded-3xl p-8 relative overflow-hidden shadow-xl">
                <div class="relative z-10">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <span
                                class="bg-white/20 backdrop-blur-md px-3 py-1 rounded-full text-xs font-medium border border-white/10">
                                Mode: {{ Auth::user()->role ?? 'Mahasiswa + Freelancer' }}
                            </span>
                            <h2 class="text-3xl md:text-4xl font-bold mt-3 mb-2">Selamat {{ $timeGreeting }},
                                {{ Auth::user()->name }}!</h2>
                            <p class="text-stone-300">
                                Waktu saat ini:
                                <span id="clock-display" class="font-mono text-orange-400">
                                    {{ $currentTime->format('H:i') }}
                                </span>
                                • {{ $currentDay }}
                            </p>
                        </div>
                        <div class="bg-white/10 p-3 rounded-full backdrop-blur-sm">
                            <i class="fa-solid fa-bolt text-2xl text-yellow-400"></i>
                        </div>
                    </div>

                    <!-- Current Activity -->
                    <div class="bg-white/10 border border-white/10 rounded-2xl p-4 backdrop-blur-md mb-6">
                        <h3 class="text-sm font-bold text-orange-300 uppercase mb-2">Aktivitas Saat Ini</h3>
                        <div class="flex items-center">
                            <div
                                class="w-10 h-10 rounded-full {{ str_replace('text-', 'bg-', explode(' ', $currentActivity['color'])[0]) }} flex items-center justify-center mr-4 shadow-lg">
                                <i class="fa-solid {{ $currentActivity['icon'] ?? 'fa-clock' }}"></i>
                            </div>
                            <div>
                                <p class="font-bold text-lg">{{ $currentActivity['activity'] }}</p>
                                <p class="text-sm text-stone-300">
                                    @if (isset($currentActivity['location']))
                                        {{ $currentActivity['location'] }} •
                                    @endif
                                    Sampai: {{ $currentActivity['end'] ?? '23:59' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Productivity Suggestions -->
                    <div class="bg-white/5 border border-white/10 rounded-2xl p-4 backdrop-blur-sm">
                        <h3 class="text-sm font-bold text-emerald-300 uppercase mb-2">Rekomendasi Produktivitas</h3>
                        <ul class="text-sm text-stone-300 space-y-1">
                            @foreach ($productivitySuggestion as $suggestion)
                                <li class="flex items-start">
                                    <i class="fa-solid fa-lightbulb text-emerald-400 mt-1 mr-2 text-xs"></i>
                                    <span>{{ $suggestion }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Time Slider Simulation -->
                    <div class="mt-6">
                        <label class="text-xs text-stone-400 mb-1 block">Simulasi Waktu (Debug Mode)</label>
                        <input type="range" min="6" max="23" value="{{ $currentTime->format('G') }}"
                            class="w-full h-2 bg-white/20 rounded-lg appearance-none cursor-pointer accent-orange-500"
                            id="time-slider" oninput="updateDashboardTime(this.value)">
                        <div class="flex justify-between text-[10px] text-stone-500 mt-1">
                            <span>06:00</span><span>12:00</span><span>18:00</span><span>23:00</span>
                        </div>
                    </div>
                </div>
                <!-- Decor -->
                <div
                    class="absolute top-0 right-0 w-64 h-64 bg-orange-500 rounded-full mix-blend-overlay filter blur-3xl opacity-20 transform translate-x-1/2 -translate-y-1/2">
                </div>
                <div
                    class="absolute bottom-0 left-0 w-40 h-40 bg-blue-500 rounded-full mix-blend-overlay filter blur-3xl opacity-20 transform -translate-x-1/2 translate-y-1/2">
                </div>
            </div>

            <!-- Quick Stats / Eisenhower Matrix Summary -->
            <div
                class="bg-white dark:bg-stone-900 rounded-3xl p-6 shadow-sm border border-stone-200 dark:border-stone-800 flex flex-col">
                <h3 class="font-bold text-stone-800 dark:text-white mb-4">Prioritas Hari Ini</h3>

                <div class="space-y-3 flex-1 overflow-y-auto">
                    @foreach ($priorityTasks->take(5) as $task)
                        <div class="p-3 {{ $task['priority_color'] }} border-l-4
                        @if ($task['priority'] == 'urgent-important') border-red-500
                        @elseif($task['priority'] == 'important-not-urgent') border-blue-500
                        @elseif($task['priority'] == 'urgent-not-important') border-orange-500
                        @else border-gray-400 @endif
                        rounded-r-xl cursor-pointer hover:shadow-sm transition-shadow"
                            onclick="completeTask({{ $task['id'] }})">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-[10px] font-bold uppercase">
                                        @if ($task['priority'] == 'urgent-important')
                                            Do First
                                        @elseif($task['priority'] == 'important-not-urgent')
                                            Schedule
                                        @elseif($task['priority'] == 'urgent-not-important')
                                            Delegate
                                        @else
                                            Eliminate
                                        @endif
                                    </span>
                                    <p class="font-medium text-stone-800 dark:text-stone-200 text-sm mt-1">
                                        {{ $task['title'] }}</p>
                                    <span class="text-xs text-stone-500 dark:text-stone-400">{{ $task['category'] }}</span>
                                </div>
                                <span
                                    class="text-xs font-bold
                                @if ($task['due_text'] == 'Hari Ini') text-red-600 dark:text-red-400
                                @elseif($task['due_text'] == 'Besok') text-orange-600 dark:text-orange-400
                                @else text-stone-500 dark:text-stone-400 @endif
                            ">
                                    {{ $task['due_text'] }}
                                </span>
                            </div>
                            <div class="mt-2 flex items-center text-xs text-stone-500 dark:text-stone-400">
                                <i class="fa-regular fa-clock mr-1"></i>
                                <span>{{ $task['estimated_time'] ?? '30m' }}</span>
                                <button
                                    class="ml-auto text-xs px-2 py-1 bg-white/50 dark:bg-stone-800/50 rounded hover:bg-white/70 dark:hover:bg-stone-700/50 transition-colors"
                                    onclick="event.stopPropagation(); showTaskDetails({{ $task['id'] }})">
                                    Details
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Quick Add Task -->
                <div class="mt-4 pt-4 border-t border-stone-200 dark:border-stone-800">
                    <div class="flex gap-2">
                        <input type="text" id="quick-task-input" placeholder="Tambah task cepat..."
                            class="flex-1 bg-stone-100 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-orange-500 dark:text-white">
                        <button onclick="quickAddTask()"
                            class="px-3 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg text-sm font-medium transition-colors">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Timeline Visualizer -->
            <div
                class="lg:col-span-3 bg-white dark:bg-stone-900 rounded-3xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-stone-800 dark:text-white">Jadwal Hari Ini (Time Blocking)</h3>
                    <span
                        class="text-xs bg-stone-100 dark:bg-stone-800 px-2 py-1 rounded text-stone-500">{{ $currentDay }}</span>
                </div>

                <!-- Time Blocks -->
                <div class="relative h-16 mb-2">
                    @for ($hour = 6; $hour <= 23; $hour++)
                        @php
                            $activity = null;
                            foreach ($todaySchedule as $sched) {
                                if ($hour >= $sched['start'] && $hour < $sched['end']) {
                                    $activity = $sched;
                                    break;
                                }
                            }
                        @endphp
                        <div class="absolute h-full {{ $activity ? $activity['color'] : 'bg-stone-200 dark:bg-stone-700' }} border border-stone-300 dark:border-stone-600 hover:opacity-90 transition-opacity cursor-pointer"
                            style="left: {{ (($hour - 6) / 17) * 100 }}%; width: {{ (1 / 17) * 100 }}%;"
                            title="{{ $activity ? $activity['activity'] : 'Free Time' }} ({{ $hour }}:00 - {{ $hour + 1 }}:00)"
                            onclick="showActivityDetails(@json($activity))">
                            @if ($activity && $hour == $activity['start'])
                                <div class="text-xs text-center truncate px-1 pt-1 font-medium">
                                    {{ substr($activity['activity'], 0, 10) }}...
                                </div>
                            @endif
                        </div>
                    @endfor

                    <!-- Current Time Indicator -->
                    <div class="absolute top-0 bottom-0 w-0.5 bg-red-500 z-10"
                        style="left: {{ (($currentTime->format('G') - 6 + $currentTime->format('i') / 60) / 17) * 100 }}%;">
                        <div class="absolute -top-2 -left-1.5 w-3 h-3 bg-red-500 rounded-full"></div>
                    </div>
                </div>

                <div class="flex justify-between text-xs text-stone-400 px-1">
                    @for ($hour = 6; $hour <= 23; $hour += 3)
                        <span>{{ str_pad($hour, 2, '0', STR_PAD_LEFT) }}:00</span>
                    @endfor
                </div>

                <!-- Schedule Legend -->
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach (['academic', 'creative', 'pkl', 'exam', 'personal', 'routine'] as $type)
                        <div class="flex items-center text-xs">
                          
                            <span class="text-stone-600 dark:text-stone-400">
                                {{ match ($type) {
                                    'academic' => 'Kuliah',
                                    'creative' => 'Proyek',
                                    'pkl' => 'PKL/Kerja',
                                    'exam' => 'Ujian',
                                    'personal' => 'Personal',
                                    'routine' => 'Rutin',
                                    default => $type,
                                } }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Bottom Section: Upcoming Deadlines & Exams -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 p-4 md:p-8">
            <!-- Upcoming Deadlines -->
            <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-stone-800 dark:text-white">Deadline Mendekat (3 Hari)</h3>
                    <span
                        class="text-xs bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 px-2 py-1 rounded-full">
                        {{ count($upcomingDeadlines) }} items
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse($upcomingDeadlines as $deadline)
                        <div
                            class="flex items-center justify-between p-3 border border-stone-200 dark:border-stone-700 rounded-lg hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">
                            <div class="flex items-center">
                                <div
                                    class="w-8 h-8 rounded-full {{ $deadline['type'] == 'exam' ? 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400' : 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400' }} flex items-center justify-center mr-3">
                                    <i
                                        class="fa-solid {{ $deadline['type'] == 'exam' ? 'fa-file-pen' : 'fa-flag' }} text-xs"></i>
                                </div>
                                <div>
                                    <p class="font-medium text-stone-800 dark:text-white text-sm">{{ $deadline['title'] }}
                                    </p>
                                    <p class="text-xs text-stone-500 dark:text-stone-400">
                                        {{ $deadline['category'] ?? 'General' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p
                                    class="text-sm font-bold {{ $deadline['deadline']->isToday() ? 'text-red-600 dark:text-red-400' : 'text-stone-700 dark:text-stone-300' }}">
                                    {{ $deadline['deadline']->format('d M') }}
                                </p>
                                <p class="text-xs text-stone-500 dark:text-stone-400">
                                    @php
                                        $daysDiff = Carbon::now()->diffInDays($deadline['deadline'], false);
                                    @endphp
                                    @if ($daysDiff == 0)
                                        Hari ini!
                                    @elseif($daysDiff == 1)
                                        Besok
                                    @else
                                        {{ $daysDiff }} hari lagi
                                    @endif
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-stone-500 dark:text-stone-400">
                            <i class="fa-solid fa-check-circle text-3xl mb-2"></i>
                            <p>Tidak ada deadline mendesak dalam 3 hari ke depan.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- This Week's Exams -->
            <div class="bg-white dark:bg-stone-900 rounded-3xl p-6 shadow-sm border border-stone-200 dark:border-stone-800">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="font-bold text-stone-800 dark:text-white">Ujian Minggu Ini</h3>
                    <span
                        class="text-xs bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 px-2 py-1 rounded-full">
                        {{ count($thisWeekExams) }} ujian
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse($thisWeekExams as $exam)
                        <div
                            class="p-3 border border-stone-200 dark:border-stone-700 rounded-lg hover:bg-stone-50 dark:hover:bg-stone-800 transition-colors">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-bold text-stone-800 dark:text-white">{{ $exam['title'] }}</h4>
                                <span
                                    class="text-xs px-2 py-1 rounded-full
                                @if ($exam['days_remaining'] <= 1) bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300
                                @elseif($exam['days_remaining'] <= 3) bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-300
                                @else bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 @endif
                            ">
                                    {{ $exam['date'] }}
                                </span>
                            </div>
                            <div class="flex items-center text-sm text-stone-600 dark:text-stone-400 mb-2">
                                <i class="fa-solid fa-book-open mr-2"></i>
                                <span>{{ $exam['course'] }}</span>
                            </div>
                            <div class="flex justify-between text-xs text-stone-500 dark:text-stone-400">
                                <div class="flex items-center">
                                    <i class="fa-solid fa-clock mr-1"></i>
                                    <span>{{ $exam['time'] }}</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fa-solid fa-location-dot mr-1"></i>
                                    <span>{{ $exam['location'] ?? 'TBA' }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-stone-500 dark:text-stone-400">
                            <i class="fa-solid fa-graduation-cap text-3xl mb-2"></i>
                            <p>Tidak ada ujian minggu ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Time simulation
        function updateDashboardTime(hour) {
            document.getElementById('clock-display').innerText = String(hour).padStart(2, '0') + ':00';

            // Update current time indicator position
            const indicator = document.querySelector('.bg-red-500.w-0\\.5');
            if (indicator) {
                const position = ((hour - 6) / 17) * 100;
                indicator.style.left = position + '%';
            }

            // You could also fetch updated activity via AJAX
            fetch('/focus/update-time', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        hour: hour
                    })
                })
                .then(response => response.json())
                .then(data => {
                    // Update current activity display
                    const activityDiv = document.querySelector('.bg-white\\/10.border');
                    if (activityDiv && data.current_activity) {
                        activityDiv.querySelector('p.font-bold.text-lg').innerText = data.current_activity.activity;
                        // Update other activity details as needed
                    }
                });
        }

        // Complete task
        function completeTask(taskId) {
            fetch(`/focus/tasks/${taskId}/complete`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove task from DOM
                        const taskElement = document.querySelector(`[onclick="completeTask(${taskId})"]`);
                        if (taskElement) {
                            taskElement.remove();
                        }

                        // Show success message
                        showNotification('Task completed successfully!', 'success');
                    }
                });
        }

        // Quick add task
        function quickAddTask() {
            const input = document.getElementById('quick-task-input');
            const title = input.value.trim();

            if (!title) {
                showNotification('Please enter a task title', 'error');
                return;
            }

            fetch('/focus/tasks/quick-add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        title: title,
                        priority: 'important-not-urgent',
                        category: 'general'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Clear input
                        input.value = '';

                        // Add task to priority list
                        addTaskToDOM(data.task);

                        showNotification('Task added successfully!', 'success');
                    }
                });
        }

        function addTaskToDOM(task) {
            const container = document.querySelector('.space-y-3.flex-1');

            const taskHtml = `
            <div class="p-3 ${task.priority_color} border-l-4 border-blue-500 rounded-r-xl cursor-pointer hover:shadow-sm transition-shadow"
                onclick="completeTask(${task.id})">
                <div class="flex justify-between items-center">
                    <div>
                        <span class="text-[10px] font-bold uppercase">Schedule</span>
                        <p class="font-medium text-stone-800 dark:text-stone-200 text-sm mt-1">${task.title}</p>
                        <span class="text-xs text-stone-500 dark:text-stone-400">${task.category}</span>
                    </div>
                    <span class="text-xs font-bold text-stone-500 dark:text-stone-400">
                        Today
                    </span>
                </div>
                <div class="mt-2 flex items-center text-xs text-stone-500 dark:text-stone-400">
                    <i class="fa-regular fa-clock mr-1"></i>
                    <span>30m</span>
                    <button class="ml-auto text-xs px-2 py-1 bg-white/50 dark:bg-stone-800/50 rounded hover:bg-white/70 dark:hover:bg-stone-700/50 transition-colors"
                        onclick="event.stopPropagation(); showTaskDetails(${task.id})">
                        Details
                    </button>
                </div>
            </div>
        `;

            container.insertAdjacentHTML('afterbegin', taskHtml);
        }

        // Notification helper
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `fixed bottom-4 right-4 px-4 py-2 rounded-lg shadow-lg z-50 transition-all duration-300 ${
            type === 'success' ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white'
        }`;
            notification.innerHTML = `
            <i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
            ${message}
        `;

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        // Activity details modal
        function showActivityDetails(activity) {
            if (!activity) return;

            const modal = `
            <div class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
                <div class="bg-white dark:bg-stone-900 rounded-2xl p-6 w-full max-w-md shadow-xl">
                    <h3 class="text-lg font-bold text-stone-900 dark:text-white mb-2">${activity.activity}</h3>
                    <p class="text-sm text-stone-600 dark:text-stone-400 mb-4">
                        ${activity.start}:00 - ${activity.end}:00 • ${activity.type}
                    </p>
                    ${activity.description ? `<p class="text-stone-700 dark:text-stone-300 mb-4">${activity.description}</p>` : ''}
                    ${activity.location ? `<p class="text-sm text-stone-500 dark:text-stone-400 mb-4"><i class="fa-solid fa-location-dot mr-2"></i>${activity.location}</p>` : ''}
                    <button onclick="this.parentElement.parentElement.remove()"
                        class="w-full py-2 bg-stone-800 dark:bg-stone-700 hover:bg-stone-900 dark:hover:bg-stone-600 text-white rounded-lg transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        `;

            document.body.insertAdjacentHTML('beforeend', modal);
        }
    </script>

    <style>
        .animate-fade-in-up {
            animation: fadeInUp 0.5s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endsection
