<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\Country;
use App\Models\Devise;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReferenceDataController extends Controller
{
    public function categories()
    {
        $categories = Category::forOrganization()
            ->withCount('events')
            ->orderByDesc('statut')
            ->orderBy('libelle')
            ->get();

        return view('reference-data.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'libelle' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'libelle')
                    ->where(fn ($query) => $query->where('organization_id', auth()->user()->organization_id)),
            ],
        ]);

        Category::create([
            'organization_id' => auth()->user()->organization_id,
            'libelle' => trim($validated['libelle']),
            'statut' => 1,
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'libelle' => [
                'required',
                'string',
                'max:100',
                Rule::unique('categories', 'libelle')
                    ->where(fn ($query) => $query->where('organization_id', auth()->user()->organization_id))
                    ->ignore($category->id),
            ],
            'statut' => ['nullable', 'boolean'],
        ]);

        $category->update([
            'libelle' => trim($validated['libelle']),
            'statut' => $request->boolean('statut') ? 1 : 0,
        ]);

        return redirect()->route('categories.index')
            ->with('success', 'Catégorie mise à jour avec succès.');
    }

    public function destroyCategory(Category $category)
    {
        if ($category->events()->exists()) {
            return redirect()->route('categories.index')
                ->with('error', 'Impossible de supprimer une catégorie utilisée par des événements.');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Catégorie supprimée avec succès.');
    }

    public function devises()
    {
        $devises = Devise::forOrganization()
            ->withCount('events')
            ->orderByDesc('statut')
            ->orderBy('libelle')
            ->get();

        return view('reference-data.devises', compact('devises'));
    }

    public function storeDevise(Request $request)
    {
        $validated = $request->validate([
            'libelle' => [
                'required',
                'string',
                'max:100',
                Rule::unique('devises', 'libelle')
                    ->where(fn ($query) => $query->where('organization_id', auth()->user()->organization_id)),
            ],
        ]);

        Devise::create([
            'organization_id' => auth()->user()->organization_id,
            'libelle' => strtoupper(trim($validated['libelle'])),
            'statut' => 1,
        ]);

        return redirect()->route('devises.index')
            ->with('success', 'Devise créée avec succès.');
    }

    public function updateDevise(Request $request, Devise $devise)
    {
        $validated = $request->validate([
            'libelle' => [
                'required',
                'string',
                'max:100',
                Rule::unique('devises', 'libelle')
                    ->where(fn ($query) => $query->where('organization_id', auth()->user()->organization_id))
                    ->ignore($devise->id),
            ],
            'statut' => ['nullable', 'boolean'],
        ]);

        $devise->update([
            'libelle' => strtoupper(trim($validated['libelle'])),
            'statut' => $request->boolean('statut') ? 1 : 0,
        ]);

        return redirect()->route('devises.index')
            ->with('success', 'Devise mise à jour avec succès.');
    }

    public function destroyDevise(Devise $devise)
    {
        if ($devise->events()->exists()) {
            return redirect()->route('devises.index')
                ->with('error', 'Impossible de supprimer une devise utilisée par des événements.');
        }

        $devise->delete();

        return redirect()->route('devises.index')
            ->with('success', 'Devise supprimée avec succès.');
    }

    public function localisations()
    {
        $countries = Country::forOrganization()
            ->withCount('cities')
            ->orderByDesc('statut')
            ->orderBy('nom')
            ->get();

        $cities = City::forOrganization()
            ->with('country')
            ->orderByDesc('statut')
            ->orderBy('nom')
            ->get();

        return view('reference-data.localisations', compact('countries', 'cities'));
    }

    public function storeCountry(Request $request)
    {
        $validated = $request->validate([
            'nom' => [
                'required',
                'string',
                'max:120',
                Rule::unique('countries', 'nom')
                    ->where(fn ($query) => $query->where('organization_id', auth()->user()->organization_id)),
            ],
            'indicatif' => ['nullable', 'string', 'max:12'],
            'currency' => ['nullable', 'string', 'max:12'],
            'flag' => ['nullable', 'string', 'max:16'],
        ]);

        Country::create([
            'organization_id' => auth()->user()->organization_id,
            'nom' => trim($validated['nom']),
            'indicatif' => $this->normalizeDialCode($validated['indicatif'] ?? null),
            'currency' => strtoupper(trim($validated['currency'] ?? '')),
            'flag' => trim($validated['flag'] ?? ''),
            'statut' => 1,
        ]);

        return redirect()->route('localisations.index')
            ->with('success', 'Pays créé avec succès.');
    }

    public function updateCountry(Request $request, Country $country)
    {
        $validated = $request->validate([
            'nom' => [
                'required',
                'string',
                'max:120',
                Rule::unique('countries', 'nom')
                    ->where(fn ($query) => $query->where('organization_id', auth()->user()->organization_id))
                    ->ignore($country->id),
            ],
            'indicatif' => ['nullable', 'string', 'max:12'],
            'currency' => ['nullable', 'string', 'max:12'],
            'flag' => ['nullable', 'string', 'max:16'],
            'statut' => ['nullable', 'boolean'],
        ]);

        $country->update([
            'nom' => trim($validated['nom']),
            'indicatif' => $this->normalizeDialCode($validated['indicatif'] ?? null),
            'currency' => strtoupper(trim($validated['currency'] ?? '')),
            'flag' => trim($validated['flag'] ?? ''),
            'statut' => $request->boolean('statut') ? 1 : 0,
        ]);

        return redirect()->route('localisations.index')
            ->with('success', 'Pays mis à jour avec succès.');
    }

    public function destroyCountry(Country $country)
    {
        if ($country->cities()->exists() || Event::forOrganization()->where('pays', $country->nom)->exists()) {
            return redirect()->route('localisations.index')
                ->with('error', 'Impossible de supprimer un pays utilisé par des villes ou des événements.');
        }

        $country->delete();

        return redirect()->route('localisations.index')
            ->with('success', 'Pays supprimé avec succès.');
    }

    public function storeCity(Request $request)
    {
        $validated = $request->validate([
            'country_id' => ['required', Rule::exists('countries', 'id')->where(fn ($query) => $query->where('organization_id', auth()->user()->organization_id))],
            'nom' => [
                'required',
                'string',
                'max:120',
                Rule::unique('cities', 'nom')
                    ->where(fn ($query) => $query
                        ->where('organization_id', auth()->user()->organization_id)
                        ->where('country_id', $request->country_id)),
            ],
        ]);

        City::create([
            'organization_id' => auth()->user()->organization_id,
            'country_id' => $validated['country_id'],
            'nom' => trim($validated['nom']),
            'statut' => 1,
        ]);

        return redirect()->route('localisations.index')
            ->with('success', 'Ville créée avec succès.');
    }

    public function updateCity(Request $request, City $city)
    {
        $validated = $request->validate([
            'country_id' => ['required', Rule::exists('countries', 'id')->where(fn ($query) => $query->where('organization_id', auth()->user()->organization_id))],
            'nom' => [
                'required',
                'string',
                'max:120',
                Rule::unique('cities', 'nom')
                    ->where(fn ($query) => $query
                        ->where('organization_id', auth()->user()->organization_id)
                        ->where('country_id', $request->country_id))
                    ->ignore($city->id),
            ],
            'statut' => ['nullable', 'boolean'],
        ]);

        $city->update([
            'country_id' => $validated['country_id'],
            'nom' => trim($validated['nom']),
            'statut' => $request->boolean('statut') ? 1 : 0,
        ]);

        return redirect()->route('localisations.index')
            ->with('success', 'Ville mise à jour avec succès.');
    }

    public function destroyCity(City $city)
    {
        if (Event::forOrganization()->where('ville', $city->nom)->exists()) {
            return redirect()->route('localisations.index')
                ->with('error', 'Impossible de supprimer une ville utilisée par des événements.');
        }

        $city->delete();

        return redirect()->route('localisations.index')
            ->with('success', 'Ville supprimée avec succès.');
    }

    private function normalizeDialCode(?string $value): ?string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return str_starts_with($value, '+') ? $value : '+' . $value;
    }
}
