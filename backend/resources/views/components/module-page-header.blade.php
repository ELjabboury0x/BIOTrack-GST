@props([
    'breadcrumb' => 'Module',
    'addRoute' => null,
    'addLabel' => 'Ajouter',
    'addIcon' => 'fa-plus',
])

<div class="mb-6 flex items-center justify-between animate-fade-in">
    <div>
        <p class="text-gray-400 text-sm flex items-center gap-1.5">
            <i class="fas fa-home text-xs"></i>
            <span>/</span>
            {{ $breadcrumb }}
        </p>
    </div>

    @if ($addRoute && auth()->user()?->role !== 'major')
        <a href="{{ route($addRoute) }}"
           class="gst-hover-scale px-6 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl hover:shadow-lg hover:shadow-blue-500/25 transition-all duration-200 font-semibold flex items-center gap-2">
            <i class="fas {{ $addIcon }}"></i> {{ $addLabel }}
        </a>
    @endif
</div>
