<div class="bg-white dark:bg-stone-800 rounded-xl p-4 shadow-sm border border-stone-200 dark:border-stone-700 task-card"
    data-task-id="{{ $task->id }}" draggable="true" @dragstart="handleDragStart({{ json_encode($task) }})">
    <div class="flex justify-between items-start mb-3">
        <div class="flex-1">
            <h4 class="font-bold text-stone-800 dark:text-white text-sm mb-1">{{ $task->title }}</h4>
            <div class="flex items-center gap-2 mb-2">
                <span
                    class="text-xs px-2 py-1 rounded-full {{ $task->project_type === 'video_editing'
                        ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300'
                        : ($task->project_type === 'graphic_design'
                            ? 'bg-pink-100 text-pink-800 dark:bg-pink-900/30 dark:text-pink-300'
                            : ($task->project_type === 'audio_production'
                                ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300'
                                : 'bg-stone-100 text-stone-800 dark:bg-stone-800 dark:text-stone-300')) }}">
                    {{ $task->project_type_label }}
                </span>
                @if ($task->priority === 'urgent-important')
                    <span
                        class="text-xs px-2 py-1 rounded-full bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300">P1</span>
                @endif
            </div>
        </div>
        <button class="text-stone-400 hover:text-stone-600 dark:hover:text-stone-300">
            <i class="fa-solid fa-ellipsis-vertical"></i>
        </button>
    </div>

    <p class="text-sm text-stone-600 dark:text-stone-400 mb-3 line-clamp-2">{{ $task->description }}</p>

    <!-- Links Section -->
    @if (is_array($task->links) && count($task->links) > 0)
        <div class="mb-3">
            <div class="flex items-center gap-1 mb-2">
                <i class="fa-solid fa-link text-xs text-stone-500"></i>
                <span class="text-xs text-stone-500 dark:text-stone-400">Links:</span>
            </div>
            <div class="flex flex-wrap gap-1">
                @foreach (array_slice($task->links, 0, 3) as $link)
                    <a href="{{ $link['url'] }}" target="_blank"
                        class="text-xs px-2 py-1 rounded-full flex items-center gap-1 {{ $link['type'] === 'drive'
                            ? 'text-blue-500 bg-blue-50 dark:bg-blue-900/20'
                            : ($link['type'] === 'canva'
                                ? 'text-pink-500 bg-pink-50 dark:bg-pink-900/20'
                                : ($link['type'] === 'figma'
                                    ? 'text-purple-500 bg-purple-50 dark:bg-purple-900/20'
                                    : ($link['type'] === 'adobe'
                                        ? 'text-red-500 bg-red-50 dark:bg-red-900/20'
                                        : 'text-emerald-500 bg-emerald-50 dark:bg-emerald-900/20'))) }}">
                        <i
                            class="fa-solid {{ $link['type'] === 'drive'
                                ? 'fa-google-drive'
                                : ($link['type'] === 'canva'
                                    ? 'fa-palette'
                                    : ($link['type'] === 'figma'
                                        ? 'fa-figma'
                                        : ($link['type'] === 'adobe'
                                            ? 'fa-adobe'
                                            : ($link['type'] === 'notion'
                                                ? 'fa-book'
                                                : ($link['type'] === 'miro'
                                                    ? 'fa-chalkboard'
                                                    : 'fa-link'))))) }} text-xs"></i>
                        <span>{{ $link['label'] ?? ucfirst($link['type']) }}</span>
                    </a>
                @endforeach
                @if (count($task->links) > 3)
                    <span
                        class="text-xs px-2 py-1 rounded-full bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400">
                        +{{ count($task->links) - 3 }} more
                    </span>
                @endif
            </div>
        </div>
    @endif

    <!-- Progress Bar -->
    <div class="mb-3">
        <div class="flex justify-between items-center mb-1">
            <span class="text-xs text-stone-500 dark:text-stone-400">Progress</span>
            <span class="text-xs font-medium text-stone-700 dark:text-stone-300">{{ $task->progress }}%</span>
        </div>
        <div class="w-full bg-stone-200 dark:bg-stone-700 rounded-full h-1.5">
            <div class="bg-orange-500 h-1.5 rounded-full" style="width: {{ $task->progress }}%"></div>
        </div>
    </div>

    <!-- Footer -->
    <div class="flex justify-between items-center">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-clock text-xs text-stone-400"></i>
            <span class="text-xs text-stone-500 dark:text-stone-400">
                {{ $task->due_date ? $task->due_date->format('M d') : 'No due date' }}
            </span>
        </div>

        <div class="flex items-center gap-2">
            <!-- Add Quick Link Button -->
            <button @click="addQuickLink({{ $task->id }})"
                class="text-xs text-stone-400 hover:text-orange-500 dark:hover:text-orange-400" title="Add link">
                <i class="fa-solid fa-plus"></i>
            </button>

            <!-- Tags -->
            @if (is_array($task->tags) && count($task->tags) > 0)
                <span class="text-xs text-stone-400">•</span>
                <div class="flex items-center gap-1">
                    @foreach (array_slice($task->tags, 0, 2) as $tag)
                        <span
                            class="text-xs px-1.5 py-0.5 rounded bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-400">
                            {{ $tag }}
                        </span>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</div>
