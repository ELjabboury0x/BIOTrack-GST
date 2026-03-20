<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSparePartRequest;
use App\Http\Requests\UpdateSparePartRequest;
use App\Models\SparePart;

class SparePartController extends Controller
{
    public function index()
    {
        $parts = SparePart::query()
            ->orderBy('code')
            ->orderBy('id')
            ->get();

        $piecesData = $parts->map(function (SparePart $part) {
            return [
                'id' => $part->id,
                'code' => $part->code,
                'nom' => $part->name,
                'description' => $part->description ?: '-',
                'quantite' => (int) $part->quantity,
                'prix_unitaire' => number_format((float) $part->unit_price, 2, '.', ' ') . ' MAD',
                'fournisseur' => $part->supplier ?: '-',
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
        return view('pages.forms.pieces-create');
    }

    public function store(StoreSparePartRequest $request)
    {
        SparePart::query()->create($request->validated());

        return redirect()
            ->route('pieces')
            ->with('success', 'Pièce ajoutée avec succès.');
    }

    public function edit(SparePart $piece)
    {
        return view('pages.forms.pieces-edit', [
            'piece' => $piece,
        ]);
    }

    public function update(UpdateSparePartRequest $request, SparePart $piece)
    {
        $piece->update($request->validated());

        return redirect()
            ->route('pieces')
            ->with('success', 'Pièce modifiée avec succès.');
    }

    public function destroy(SparePart $piece)
    {
        $piece->delete();

        return redirect()
            ->route('pieces')
            ->with('success', 'Pièce supprimée avec succès.');
    }
}
