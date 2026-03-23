<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSparePartRequest;
use App\Http\Requests\UpdateSparePartRequest;
use App\Models\Service;
use App\Models\SparePart;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SparePartController extends Controller
{
    public function index()
    {
        $parts = SparePart::query()
            ->orderBy('code')
            ->orderBy('id')
            ->get();

        $piecesData = $parts->map(function (SparePart $part) {
            $phaseLabel = match ($part->phase) {
                'decharge' => 'Decharge',
                'retour' => 'Reception / Retour',
                default => 'Standard',
            };

            $mainDate = $part->phase === 'retour'
                ? optional($part->return_date)->format('Y-m-d')
                : optional($part->discharge_date)->format('Y-m-d');

            return [
                'id' => $part->id,
                'phase' => $phaseLabel,
                'date_mouvement' => $mainDate ?: '-',
                'code' => $part->code,
                'nom' => $part->name,
                'sn' => $part->serial_number ?: '-',
                'description' => $part->description ?: '-',
                'quantite' => (int) $part->quantity,
                'fournisseur' => $part->supplier ?: '-',
                'etat' => $part->condition_state ?: '-',
                'mode_saisie' => $part->entry_mode === 'pdf' ? 'Import PDF' : 'Formulaire',
                'pdf' => $part->document_pdf_path ? 'Oui' : 'Non',
                'edit_url' => route('pieces.edit', $part),
                'delete_url' => route('pieces.destroy', $part),
            ];
        })->values();

        return view('pages.pieces', [
            'piecesData' => $piecesData,
        ]);
    }

    public function create()
    {
        return view('pages.forms.pieces-create', $this->formOptions());
    }

    public function store(StoreSparePartRequest $request)
    {
        $payload = $this->buildPayload($request->validated(), $request->file('document_pdf'));

        SparePart::query()->create($payload);

        return redirect()
            ->route('pieces')
            ->with('success', 'Pièce ajoutée avec succès.');
    }

    public function edit(SparePart $piece)
    {
        return view('pages.forms.pieces-edit', [
            'piece' => $piece,
        ] + $this->formOptions());
    }

    public function update(UpdateSparePartRequest $request, SparePart $piece)
    {
        $validated = $request->validated();
        $payload = $this->buildPayload($validated, $request->file('document_pdf'), $piece);

        if (!empty($payload['document_pdf_path']) && $piece->document_pdf_path && $piece->document_pdf_path !== $payload['document_pdf_path']) {
            Storage::disk('public')->delete($piece->document_pdf_path);
        }

        $piece->update($payload);

        return redirect()
            ->route('pieces')
            ->with('success', 'Pièce modifiée avec succès.');
    }

    public function destroy(SparePart $piece)
    {
        if ($piece->document_pdf_path) {
            Storage::disk('public')->delete($piece->document_pdf_path);
        }

        $piece->delete();

        return redirect()
            ->route('pieces')
            ->with('success', 'Pièce supprimée avec succès.');
    }

    private function formOptions(): array
    {
        $services = Service::query()
            ->excludeHiddenForUi()
            ->orderBy('name')
            ->get(['id', 'name']);

        $technicians = User::query()
            ->whereIn('role', [User::ROLE_TECHNICIAN, User::ROLE_TECHNICIEN])
            ->orderBy('name')
            ->get(['id', 'name']);

        $majors = User::query()
            ->where('role', User::ROLE_MAJOR)
            ->orderBy('name')
            ->get(['id', 'name']);

        $actionUsers = User::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return [
            'services' => $services,
            'technicians' => $technicians,
            'majors' => $majors,
            'actionUsers' => $actionUsers,
        ];
    }

    private function buildPayload(array $validated, ?UploadedFile $documentPdf, ?SparePart $existing = null): array
    {
        $phase = (string) ($validated['phase'] ?? ($existing?->phase ?: 'decharge'));
        $entryMode = (string) ($validated['entry_mode'] ?? ($documentPdf ? 'pdf' : ($existing?->entry_mode ?: 'form')));

        $name = $validated['name'] ?? $existing?->name;
        if (!$name) {
            $name = $phase === 'retour' ? 'Retour piece - document PDF' : 'Decharge piece - document PDF';
        }

        $code = $validated['code'] ?? $existing?->code;
        if (!$code) {
            $code = 'SP-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));
        }

        $payload = [
            'code' => $code,
            'name' => $name,
            'description' => $validated['description'] ?? $existing?->description,
            'quantity' => isset($validated['quantity']) ? (int) $validated['quantity'] : (int) ($existing?->quantity ?? 0),
            'unit_price' => isset($validated['unit_price']) ? (float) $validated['unit_price'] : (float) ($existing?->unit_price ?? 0),
            'supplier' => $validated['supplier'] ?? $existing?->supplier,
            'phase' => $phase,
            'entry_mode' => $entryMode,
            'discharge_date' => $phase === 'decharge' ? ($validated['discharge_date'] ?? $existing?->discharge_date) : null,
            'return_date' => $phase === 'retour' ? ($validated['return_date'] ?? $existing?->return_date) : null,
            'serial_number' => $phase === 'decharge' ? ($validated['serial_number'] ?? $existing?->serial_number) : null,
            'action_user_id' => $phase === 'decharge' ? ($validated['action_user_id'] ?? $existing?->action_user_id ?? auth()->id()) : null,
            'assistant_technician_id' => $phase === 'decharge' ? ($validated['assistant_technician_id'] ?? $existing?->assistant_technician_id) : null,
            'service_id' => $validated['service_id'] ?? $existing?->service_id,
            'major_signer_id' => $phase === 'decharge' ? ($validated['major_signer_id'] ?? $existing?->major_signer_id) : null,
            'return_technician_id' => $phase === 'retour' ? ($validated['return_technician_id'] ?? $existing?->return_technician_id) : null,
            'condition_state' => $phase === 'retour' ? ($validated['condition_state'] ?? $existing?->condition_state) : null,
            'comment' => $phase === 'retour' ? ($validated['comment'] ?? $existing?->comment) : null,
            'document_pdf_path' => $existing?->document_pdf_path,
        ];

        if ($documentPdf) {
            $payload['document_pdf_path'] = $documentPdf->store('spare-parts/documents', 'public');
        }

        return $payload;
    }
}
