@props([
    'breadcrumb' => 'Module',
    'addRoute' => null,
    'addLabel' => 'Ajouter',
    'addIcon' => 'fa-plus',
    'buttonStyle' => null,
])

<div class="mb-6 flex items-center justify-between animate-fade-in">
    <div>
        <p class="text-gray-400 text-sm flex items-center gap-1.5">
            <i class="fas fa-home text-xs"></i>
            <span>/</span>
            {{ $breadcrumb }}
        </p>
    </div>

    <div class="module-page-header-actions flex items-center gap-3">
        @if ($addRoute && auth()->user()?->role !== 'major')
            @php
                $addButtonClass = ($buttonStyle === 'equipments')
                    ? 'inline-flex h-10 items-center gap-2 rounded-lg bg-blue-600 px-4 text-sm font-semibold text-white transition-all duration-200 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300'
                    : 'gst-hover-scale px-6 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl hover:shadow-lg hover:shadow-blue-500/25 transition-all duration-200 font-semibold flex items-center gap-2';
            @endphp
            <a href="{{ route($addRoute) }}"
               class="module-page-header-add-btn {{ $addButtonClass }}">
                <i class="fas {{ $addIcon }}"></i> {{ $addLabel }}
            </a>
        @endif
    </div>
</div>
