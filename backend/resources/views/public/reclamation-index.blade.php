<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>BioTrack GST - Réclamation</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-100">
    <div class="max-w-6xl mx-auto py-10 px-4">
        <div class="rounded-2xl border border-sky-100 bg-white shadow-xl overflow-hidden">
            <div class="px-6 md:px-8 py-6 bg-gradient-to-r from-sky-700 via-cyan-700 to-blue-700 text-white">
                <img src="{{ asset('images/logo-gst.png') }}?v={{ filemtime(public_path('images/logo-gst.png')) }}" alt="BioTrack GST" class="h-14 w-auto object-contain mb-3">
                <h1 class="text-2xl md:text-3xl font-bold tracking-tight">Portail Réclamation Biomédicale</h1>
                <p class="text-sm text-sky-100 mt-2">Sélectionnez votre service pour accéder au formulaire de signalement.</p>
            </div>

            <div class="p-6 md:p-8">
                <div class="mb-5 rounded-xl border border-cyan-100 bg-cyan-50 px-4 py-3 text-cyan-800 text-sm">
                    Ce portail est dédié aux pannes d'équipements biomédicaux. Les réclamations sont transmises en temps réel à l'équipe maintenance.
                </div>

                @if (session('error'))
                    <div class="mt-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                @if (($dbUnavailable ?? false) === true)
                    <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800 text-sm">
                        Le service est temporairement indisponible. La base de donnees ne repond pas pour le moment.
                    </div>
                @endif

                <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    @forelse($services as $service)
                        @php
                            $serviceToken = trim((string) ($service->code ?? '')) !== ''
                                ? $service->code
                                : ('ID-' . $service->id);
                        @endphp
                                <a href="{{ route('public.reclamation.form', ['service_code' => $serviceToken]) }}"
                           class="group block rounded-xl border border-slate-200 hover:border-sky-300 bg-white hover:bg-sky-50 px-4 py-4 transition shadow-sm hover:shadow-md">
                            <p class="text-sm font-semibold text-slate-800 group-hover:text-sky-900">{{ $service->name }}</p>
                            <p class="text-xs text-slate-500 mt-1">Code: {{ trim((string) ($service->code ?? '')) !== '' ? $service->code : 'Non défini' }}</p>
                            <p class="text-[11px] text-slate-400 mt-1">Identifiant lien: {{ $serviceToken }}</p>
                            <p class="text-xs text-sky-700 mt-3 font-medium">Ouvrir le formulaire →</p>
                        </a>
                    @empty
                        <div class="col-span-full rounded-lg border border-yellow-200 bg-yellow-50 px-4 py-3 text-yellow-800 text-sm">
                            @if (($dbUnavailable ?? false) === true)
                                Aucun service ne peut etre charge pour le moment.
                            @else
                                Aucun service avec code n'est configure.
                            @endif
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</body>
</html>
