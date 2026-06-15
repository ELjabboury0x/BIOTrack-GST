<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Equipment;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ExternalCompanyController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q', ''));

        $query = Company::query()
            ->withCount('equipments')
            ->orderBy('name');

        if ($search !== '') {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $companies = $query->get();

        // Rename the count field for consistency
        $companies = $companies->map(function ($company) {
            $company->linked_equipments_count = $company->equipments_count;
            unset($company->equipments_count);
            return $company;
        });

        // Add "Unknown" (Inconnue) entry for equipment without any company
        $unknownCount = Equipment::query()
            ->whereNull('company_id')
            ->count();

        if ($unknownCount > 0 && ($search === '' || str_contains('inconnue', strtolower($search)) || str_contains('unknown', strtolower($search)))) {
            $unknownCompany = (object) [
                'id' => null,
                'name' => 'Inconnue',
                'linked_equipments_count' => $unknownCount,
                'created_at' => null,
            ];
            $companies = $companies->prepend($unknownCompany);
        }

        return view('pages.external-companies.index', [
            'search' => $search,
            'companiesData' => $companies,
        ]);
    }

    public function create()
    {
        return view('pages.forms.external-company-create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180', Rule::unique('companies', 'name')],
        ]);

        Company::query()->create([
            'name' => trim((string) $validated['name']),
        ]);

        return redirect()
            ->route('external-companies.index')
            ->with('success', 'Société externe ajoutée avec succès.');
    }

    public function importExcel(Request $request)
    {
        $validated = $request->validate([
            'companies_file' => 'required|file|mimes:xlsx,xls|max:51200',
        ]);

        $created = 0;
        $existing = 0;
        $invalid = 0;

        try {
            $spreadsheet = IOFactory::load($validated['companies_file']->getRealPath());
            $sheet = $spreadsheet->getSheet(0);
            $highestRow = (int) $sheet->getHighestDataRow();
            $highestColumn = $sheet->getHighestDataColumn();

            for ($row = 1; $row <= $highestRow; $row++) {
                $values = $sheet->rangeToArray("A{$row}:{$highestColumn}{$row}", null, true, true, false);
                $cells = $values[0] ?? [];

                $name = '';
                foreach ($cells as $cellValue) {
                    $candidate = trim((string) $cellValue);
                    if ($candidate !== '') {
                        $name = $candidate;
                        break;
                    }
                }

                if ($name === '') {
                    continue;
                }

                if ($row === 1) {
                    $header = mb_strtolower($name);
                    if (str_contains($header, 'societe') || str_contains($header, 'société') || str_contains($header, 'company') || str_contains($header, 'nom')) {
                        continue;
                    }
                }

                if (mb_strlen($name) > 180) {
                    $invalid++;
                    continue;
                }

                $company = Company::query()->firstOrCreate(['name' => $name]);
                if ($company->wasRecentlyCreated) {
                    $created++;
                } else {
                    $existing++;
                }
            }

            return redirect()
                ->route('external-companies.index')
                ->with('success', "Import terminé. Nouvelles sociétés: {$created}, déjà existantes: {$existing}, ignorées: {$invalid}.");
        } catch (\Throwable $e) {
            return redirect()
                ->route('external-companies.index')
                ->with('error', 'Import Excel impossible: ' . $e->getMessage());
        }
    }

    public function destroy(Company $company)
    {
        $linkedEquipmentsCount = $company->equipments()->count();
        $linkedMarketsCount = $company->markets()->count();
        $linkedPlanningCount = (int) DB::table('external_company_plannings')
            ->where('company_id', $company->id)
            ->count();

        if ($linkedEquipmentsCount > 0 || $linkedMarketsCount > 0 || $linkedPlanningCount > 0) {
            $reasons = [];
            if ($linkedEquipmentsCount > 0) {
                $reasons[] = $linkedEquipmentsCount . ' équipement(s)';
            }
            if ($linkedMarketsCount > 0) {
                $reasons[] = $linkedMarketsCount . ' marché(s)';
            }
            if ($linkedPlanningCount > 0) {
                $reasons[] = $linkedPlanningCount . ' planning(s)';
            }

            return redirect()
                ->route('external-companies.index')
                ->with('error', 'Suppression impossible: cette société est liée à ' . implode(', ', $reasons) . '.');
        }

        try {
            $company->delete();
        } catch (QueryException $e) {
            return redirect()
                ->route('external-companies.index')
                ->with('error', 'Suppression impossible: la société est encore liée à d\'autres données.');
        }

        return redirect()
            ->route('external-companies.index')
            ->with('success', 'Société externe supprimée avec succès.');
    }
}
