@extends('layouts.app-dashboard')

@section('title', 'Creative Studio | Student-Freelancer Hub')
@section('page-title', 'Creative Studio')

@section('content')
    <div class="space-y-6" x-data="creativeStudio()">
        <!-- Header Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-stone-500 dark:text-stone-400">Total Projects</p>
                        <p class="text-2xl font-bold text-stone-800 dark:text-white">{{ $stats['total_projects'] }}</p>
                    </div>
                    <i class="fa-solid fa-layer-group text-2xl text-orange-500"></i>
                </div>
            </div>

            <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-stone-500 dark:text-stone-400">Active</p>
                        <p class="text-2xl font-bold text-stone-800 dark:text-white">{{ $stats['active_projects'] }}</p>
                    </div>
                    <i class="fa-solid fa-spinner text-2xl text-blue-500"></i>
                </div>
            </div>

            <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-stone-500 dark:text-stone-400">Completed</p>
                        <p class="text-2xl font-bold text-stone-800 dark:text-white">{{ $stats['completed_projects'] }}</p>
                    </div>
                    <i class="fa-solid fa-check-circle text-2xl text-emerald-500"></i>
                </div>
            </div>

            <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-stone-500 dark:text-stone-400">Overdue</p>
                        <p class="text-2xl font-bold text-stone-800 dark:text-white">{{ $stats['overdue_projects'] }}</p>
                    </div>
                    <i class="fa-solid fa-clock text-2xl text-rose-500"></i>
                </div>
            </div>
        </div>

        <!-- Filter Controls -->
        <div class="bg-white dark:bg-stone-900 rounded-xl p-4 border border-stone-200 dark:border-stone-800">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold text-stone-800 dark:text-white">Creative Projects</h3>
                    <p class="text-sm text-stone-500 dark:text-stone-400">Kanban board untuk proyek freelance & konten.</p>
                </div>

                <div class="flex items-center gap-3">
                    <select x-model="filters.projectType"
                        class="bg-stone-100 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-2 text-sm">
                        <option value="all">All Project Types</option>
                        @foreach ($projectTypes as $key => $label)
                            <option value="{{ $key }}" {{ $projectType == $key ? 'selected' : '' }}>
                                {{ $label }}</option>
                        @endforeach
                    </select>

                    <select x-model="filters.priority"
                        class="bg-stone-100 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-2 text-sm">
                        <option value="all">All Priorities</option>
                        <option value="urgent-important">Priority 1</option>
                        <option value="important-not-urgent">Priority 2</option>
                        <option value="urgent-not-important">Priority 3</option>
                        <option value="not-urgent-not-important">Priority 4</option>
                    </select>

                    <button @click="showNewProjectModal = true"
                        class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors shadow-lg shadow-orange-500/20 flex items-center">
                        <i class="fa-solid fa-plus mr-2"></i>New Project
                    </button>
                </div>
            </div>
        </div>

        <!-- Kanban Board -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 h-full overflow-x-auto pb-4">
            <!-- TODO Column -->
            <div class="flex flex-col bg-stone-100 dark:bg-stone-900/50 rounded-2xl p-4 kanban-col min-w-[320px]">
                <div class="flex justify-between items-center mb-4 px-1">
                    <span class="font-bold text-stone-700 dark:text-stone-300 text-sm">Script & Concept</span>
                    <span
                        class="bg-stone-200 dark:bg-stone-800 text-xs px-2 py-1 rounded-full text-stone-600 dark:text-stone-400">
                        {{ $todoTasks->count() }}
                    </span>
                </div>
                <div class="space-y-3 min-h-[400px]" id="todo-column" @drop.prevent="handleDrop($event, 'todo')"
                    @dragover.prevent>
                    @foreach ($todoTasks as $task)
                        @include('partials.creative-kanban-card', ['task' => $task])
                    @endforeach
                    @if ($todoTasks->isEmpty())
                        <div
                            class="flex-1 flex flex-col items-center justify-center text-stone-300 dark:text-stone-700 border-2 border-dashed border-stone-200 dark:border-stone-800 rounded-xl p-6 h-full">
                            <i class="fa-solid fa-lightbulb text-3xl mb-2"></i>
                            <span class="text-sm text-center">Drag projects here</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- IN PROGRESS Column -->
            <div
                class="flex flex-col bg-orange-50 dark:bg-orange-900/10 border border-orange-100 dark:border-orange-900/30 rounded-2xl p-4 kanban-col min-w-[320px]">
                <div class="flex justify-between items-center mb-4 px-1">
                    <span class="font-bold text-orange-700 dark:text-orange-400 text-sm">Editing / Production</span>
                    <span
                        class="bg-orange-200 dark:bg-orange-900/40 text-xs px-2 py-1 rounded-full text-orange-800 dark:text-orange-300">
                        {{ $doingTasks->count() }}
                    </span>
                </div>
                <div class="space-y-3 min-h-[400px]" id="doing-column" @drop.prevent="handleDrop($event, 'doing')"
                    @dragover.prevent>
                    @foreach ($doingTasks as $task)
                        @include('partials.creative-kanban-card', ['task' => $task])
                    @endforeach
                    @if ($doingTasks->isEmpty())
                        <div
                            class="flex-1 flex flex-col items-center justify-center text-orange-300 dark:text-orange-800/50 border-2 border-dashed border-orange-200 dark:border-orange-900/30 rounded-xl p-6 h-full">
                            <i class="fa-solid fa-spinner text-3xl mb-2"></i>
                            <span class="text-sm text-center">Currently in progress</span>
                        </div>
                    @endif
                </div>
            </div>

            <!-- DONE Column -->
            <div
                class="flex flex-col bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-100 dark:border-emerald-900/30 rounded-2xl p-4 kanban-col min-w-[320px]">
                <div class="flex justify-between items-center mb-4 px-1">
                    <span class="font-bold text-emerald-700 dark:text-emerald-400 text-sm">Ready to Publish</span>
                    <span
                        class="bg-emerald-200 dark:bg-emerald-900/40 text-xs px-2 py-1 rounded-full text-emerald-800 dark:text-emerald-300">
                        {{ $doneTasks->count() }}
                    </span>
                </div>
                <div class="space-y-3 min-h-[400px]" id="done-column" @drop.prevent="handleDrop($event, 'done')"
                    @dragover.prevent>
                    @foreach ($doneTasks as $task)
                        @include('partials.creative-kanban-card', ['task' => $task])
                    @endforeach
                    @if ($doneTasks->isEmpty())
                        <div
                            class="flex-1 flex flex-col items-center justify-center text-emerald-300 dark:text-emerald-800/50 border-2 border-dashed border-emerald-200 dark:border-emerald-900/30 rounded-xl p-6 h-full">
                            <i class="fa-solid fa-check-circle text-3xl mb-2"></i>
                            <span class="text-sm text-center">Drag finished items here</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Project Type Distribution -->
        <div class="bg-white dark:bg-stone-900 rounded-xl p-6 border border-stone-200 dark:border-stone-800">
            <h3 class="text-lg font-bold text-stone-800 dark:text-white mb-4">Project Distribution</h3>
            <div class="grid grid-cols-2 md:grid-cols-6 gap-4">
                @foreach ($projectTypeCounts as $type => $data)
                    @if ($data['count'] > 0)
                        <div class="bg-stone-50 dark:bg-stone-800/50 rounded-lg p-4 text-center">
                            <div class="text-2xl font-bold text-stone-800 dark:text-white mb-1">{{ $data['count'] }}</div>
                            <div class="text-sm text-stone-600 dark:text-stone-400">{{ $data['label'] }}</div>
                            <div class="text-xs text-stone-500 dark:text-stone-500 mt-1">
                                {{ $data['active'] }} active
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- New Project Modal -->
        <div id="new-project-modal"
            class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center p-4"
            x-show="showNewProjectModal" @click.away="showNewProjectModal = false">
            <div class="bg-white dark:bg-stone-900 rounded-2xl w-full max-w-md shadow-xl" @click.stop>
                <div class="p-6 border-b border-stone-200 dark:border-stone-800">
                    <h3 class="text-lg font-bold text-stone-800 dark:text-white">Create New Creative Project</h3>
                </div>

                <form id="new-project-form" @submit.prevent="createProject">
                    <div class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Project
                                Title</label>
                            <input type="text" x-model="newProject.title" required
                                class="w-full bg-stone-100 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Project
                                Type</label>
                            <select x-model="newProject.project_type" required
                                class="w-full bg-stone-100 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <option value="">Select Type</option>
                                @foreach ($projectTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Priority</label>
                                <select x-model="newProject.priority" required
                                    class="w-full bg-stone-100 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                                    <option value="urgent-important">Priority 1</option>
                                    <option value="important-not-urgent">Priority 2</option>
                                    <option value="urgent-not-important">Priority 3</option>
                                    <option value="not-urgent-not-important">Priority 4</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Due
                                    Date</label>
                                <input type="date" x-model="newProject.due_date" required
                                    class="w-full bg-stone-100 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Estimated
                                Time</label>
                            <input type="text" x-model="newProject.estimated_time" placeholder="e.g., 2h, 30m"
                                class="w-full bg-stone-100 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        </div>

                        <div>
                            <label
                                class="block text-sm font-medium text-stone-700 dark:text-stone-300 mb-1">Description</label>
                            <textarea x-model="newProject.description" rows="3"
                                class="w-full bg-stone-100 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-orange-500"></textarea>
                        </div>

                        <!-- Links Section -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-stone-700 dark:text-stone-300">Project
                                    Links</label>
                                <button type="button" @click="addLinkField"
                                    class="text-sm text-orange-500 hover:text-orange-600">
                                    <i class="fa-solid fa-plus mr-1"></i>Add Link
                                </button>
                            </div>

                            <div class="space-y-2" id="link-fields">
                                <template x-for="(link, index) in newProject.links" :key="index">
                                    <div class="flex gap-2 items-center">
                                        <select x-model="link.type"
                                            class="flex-1 bg-stone-100 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-1 text-sm">
                                            <option value="drive">Google Drive</option>
                                            <option value="canva">Canva</option>
                                            <option value="figma">Figma</option>
                                            <option value="adobe">Adobe</option>
                                            <option value="notion">Notion</option>
                                            <option value="miro">Miro</option>
                                            <option value="other">Other</option>
                                        </select>

                                        <input type="url" x-model="link.url" placeholder="URL"
                                            class="flex-2 bg-stone-100 dark:bg-stone-800 border border-stone-300 dark:border-stone-700 rounded-lg px-3 py-1 text-sm">

                                        <button type="button" @click="removeLinkField(index)"
                                            class="text-rose-500 hover:text-rose-600">
                                            <i class="fa-solid fa-times"></i>
                                        </button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 border-t border-stone-200 dark:border-stone-800 flex justify-end gap-3">
                        <button type="button" @click="showNewProjectModal = false"
                            class="px-4 py-2 text-stone-600 dark:text-stone-400 hover:text-stone-800 dark:hover:text-stone-200">
                            Cancel
                        </button>
                        <button type="submit"
                            class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                            Create Project
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
        <script>
            function creativeStudio() {
                return {
                    showNewProjectModal: false,
                    filters: {
                        projectType: '{{ $projectType }}',
                        priority: '{{ $priority }}'
                    },
                    newProject: {
                        title: '',
                        description: '',
                        project_type: '',
                        priority: 'not-urgent-not-important',
                        due_date: new Date().toISOString().split('T')[0],
                        estimated_time: '',
                        links: []
                    },
                    draggingTask: null,

                    init() {
                        this.initSortable();
                        this.setupEventListeners();
                    },

                    initSortable() {
                        ['todo-column', 'doing-column', 'done-column'].forEach(colId => {
                            new Sortable(document.getElementById(colId), {
                                group: 'creative-board',
                                animation: 150,
                                ghostClass: 'opacity-50',
                                onEnd: (evt) => {
                                    const taskId = evt.item.dataset.taskId;
                                    const newStatus = evt.to.id.replace('-column', '');
                                    this.updateTaskStatus(taskId, newStatus);
                                }
                            });
                        });
                    },

                    setupEventListeners() {
                        // Filter changes
                        this.$watch('filters.projectType', (value) => {
                            if (value !== 'all') {
                                window.location.href = '{{ route('dashboard.creative.index') }}?project_type=' + value;
                            } else {
                                window.location.href = '{{ route('dashboard.creative.index') }}';
                            }
                        });

                        this.$watch('filters.priority', (value) => {
                            if (value !== 'all') {
                                window.location.href = '{{ route('dashboard.creative.index') }}?priority=' + value;
                            } else {
                                window.location.href = '{{ route('dashboard.creative.index') }}';
                            }
                        });
                    },

                    handleDragStart(task) {
                        this.draggingTask = task;
                    },

                    handleDrop(event, newStatus) {
                        if (this.draggingTask) {
                            this.updateTaskStatus(this.draggingTask.id, newStatus);
                            this.draggingTask = null;
                        }
                    },

                    async updateTaskStatus(taskId, newStatus) {
                        try {
                            const response = await fetch(`/dashboard/creative/${taskId}/status`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify({
                                    status: newStatus,
                                    _method: 'PATCH'
                                })
                            });

                            const data = await response.json();
                            if (data.success) {
                                showNotification('Status updated successfully', 'success');
                            }
                        } catch (error) {
                            console.error('Error updating status:', error);
                            showNotification('Failed to update status', 'error');
                        }
                    },

                    addLinkField() {
                        this.newProject.links.push({
                            type: 'drive',
                            url: '',
                            label: ''
                        });
                    },

                    removeLinkField(index) {
                        this.newProject.links.splice(index, 1);
                    },

                    async createProject() {
                        try {
                            const response = await fetch('{{ route('dashboard.creative.store') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify(this.newProject)
                            });

                            if (response.ok) {
                                showNotification('Project created successfully', 'success');
                                this.showNewProjectModal = false;
                                this.resetNewProjectForm();
                                setTimeout(() => window.location.reload(), 1500);
                            } else {
                                const error = await response.json();
                                showNotification(error.message || 'Failed to create project', 'error');
                            }
                        } catch (error) {
                            console.error('Error creating project:', error);
                            showNotification('Failed to create project', 'error');
                        }
                    },

                    resetNewProjectForm() {
                        this.newProject = {
                            title: '',
                            description: '',
                            project_type: '',
                            priority: 'not-urgent-not-important',
                            due_date: new Date().toISOString().split('T')[0],
                            estimated_time: '',
                            links: []
                        };
                    },

                    async addQuickLink(taskId) {
                        const type = prompt('Link type (drive, canva, figma, etc):');
                        const url = prompt('URL:');
                        const label = prompt('Label (optional):');

                        if (type && url) {
                            try {
                                const response = await fetch(`/dashboard/creative/${taskId}/links`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    },
                                    body: JSON.stringify({
                                        type,
                                        url,
                                        label
                                    })
                                });

                                const data = await response.json();
                                if (data.success) {
                                    showNotification('Link added successfully', 'success');
                                    setTimeout(() => window.location.reload(), 1000);
                                }
                            } catch (error) {
                                console.error('Error adding link:', error);
                                showNotification('Failed to add link', 'error');
                            }
                        }
                    },

                    getLinkIcon(type) {
                        const icons = {
                            'drive': 'fa-google-drive',
                            'canva': 'fa-palette',
                            'figma': 'fa-figma',
                            'adobe': 'fa-adobe',
                            'notion': 'fa-book',
                            'miro': 'fa-chalkboard',
                            'milanote': 'fa-sticky-note',
                            'github': 'fa-github',
                            'dropbox': 'fa-dropbox',
                            'other': 'fa-link'
                        };
                        return icons[type] || 'fa-link';
                    },

                    getLinkColor(type) {
                        const colors = {
                            'drive': 'text-blue-500 bg-blue-50 dark:bg-blue-900/20',
                            'canva': 'text-pink-500 bg-pink-50 dark:bg-pink-900/20',
                            'figma': 'text-purple-500 bg-purple-50 dark:bg-purple-900/20',
                            'adobe': 'text-red-500 bg-red-50 dark:bg-red-900/20',
                            'notion': 'text-stone-700 bg-stone-100 dark:bg-stone-800',
                            'miro': 'text-amber-500 bg-amber-50 dark:bg-amber-900/20',
                            'milanote': 'text-rose-500 bg-rose-50 dark:bg-rose-900/20',
                            'github': 'text-gray-800 dark:text-gray-200 bg-gray-100 dark:bg-gray-800',
                            'dropbox': 'text-blue-600 bg-blue-50 dark:bg-blue-900/20',
                            'other': 'text-emerald-500 bg-emerald-50 dark:bg-emerald-900/20'
                        };
                        return colors[type] || 'text-gray-500 bg-gray-100 dark:bg-gray-800';
                    }
                };
            }

            // Make showNotification available globally
            window.showNotification = function(message, type = 'info') {
                const notification = document.createElement('div');
                notification.className = `fixed bottom-4 right-4 px-4 py-3 rounded-lg shadow-lg z-50 flex items-center ${
            type === 'success' ? 'bg-emerald-500 text-white' :
            type === 'error' ? 'bg-red-500 text-white' :
            type === 'warning' ? 'bg-yellow-500 text-white' :
            'bg-blue-500 text-white'
        }`;

                notification.innerHTML = `
            <i class="fa-solid ${
                type === 'success' ? 'fa-check-circle' :
                type === 'error' ? 'fa-exclamation-circle' :
                type === 'warning' ? 'fa-exclamation-triangle' :
                'fa-info-circle'
            } mr-2"></i>
            <span>${message}</span>
        `;

                document.body.appendChild(notification);

                setTimeout(() => {
                    notification.classList.add('opacity-0', 'transition-opacity', 'duration-300');
                    setTimeout(() => notification.remove(), 300);
                }, 3000);
            };
        </script>
    @endpush

    <style>
        .kanban-col {
            min-height: 600px;
        }

        .sortable-ghost {
            opacity: 0.5;
            background: #f3f4f6;
        }

        .sortable-drag {
            opacity: 0.9;
            transform: rotate(5deg);
        }

        .progress-bar {
            height: 4px;
            background: linear-gradient(90deg, #f97316 var(--progress), #e5e7eb var(--progress));
        }
    </style>
@endsection
