<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockMovementRequest;
use App\Http\Requests\UpdateStockMovementRequest;
use App\Models\StockMovement;

class StockMovementController extends Controller
{
    public function movements()
    {
        $movements = StockMovement::query()
            ->with('creator:id,name,login')
            ->orderByDesc('movement_date')
            ->orderByDesc('id')
            ->get();

        $movementsData = $movements->map(function (StockMovement $movement) {
            return [
                'id' => $movement->id,
                'type' => $movement->movement_type,
                'reference_piece' => $movement->part_reference,
                'quantite' => (int) $movement->quantity,
                'date_mouvement' => optional($movement->movement_date)->format('Y-m-d') ?: '-',
                'description' => $movement->description ?: '-',
                'auteur' => $movement->creator?->name ?: $movement->creator?->login ?: '-',
                'edit_url' => route('stock.edit', $movement),
                'delete_url' => route('stock.destroy', $movement),
            ];
        })->values();

        return view('pages.stock-movements', [
            'movementsData' => $movementsData,
        ]);
    }

    public function create()
    {
        return view('pages.forms.stock-create');
    }

    public function store(StoreStockMovementRequest $request)
    {
        StockMovement::query()->create($request->validated() + [
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('stock.movements')
            ->with('success', 'Mouvement ajouté avec succès.');
    }

    public function edit(StockMovement $movement)
    {
        return view('pages.forms.stock-edit', [
            'movement' => $movement,
        ]);
    }

    public function update(UpdateStockMovementRequest $request, StockMovement $movement)
    {
        $movement->update($request->validated());

        return redirect()
            ->route('stock.movements')
            ->with('success', 'Mouvement mis à jour avec succès.');
    }

    public function destroy(StockMovement $movement)
    {
        $movement->delete();

        return redirect()
            ->route('stock.movements')
            ->with('success', 'Mouvement supprimé avec succès.');
    }
}
