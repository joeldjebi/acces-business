<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->promoteCategories();
        $this->promoteDevises();
        $this->promoteCountriesAndCities();
    }

    public function down(): void
    {
        // Transition intentionally non-destructive: existing events may now reference global records.
    }

    private function promoteCategories(): void
    {
        if (!Schema::hasTable('categories') || !Schema::hasTable('events')) {
            return;
        }

        $categories = DB::table('categories')
            ->select('libelle', DB::raw('MAX(statut) as statut'))
            ->whereNotNull('libelle')
            ->groupBy('libelle')
            ->get();

        foreach ($categories as $category) {
            $globalId = DB::table('categories')
                ->whereNull('organization_id')
                ->where('libelle', $category->libelle)
                ->value('id');

            if (!$globalId) {
                $globalId = DB::table('categories')->insertGetId([
                    'organization_id' => null,
                    'libelle' => $category->libelle,
                    'statut' => (int) $category->statut,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $oldIds = DB::table('categories')
                ->where('libelle', $category->libelle)
                ->where('id', '!=', $globalId)
                ->pluck('id');

            if ($oldIds->isNotEmpty()) {
                DB::table('events')->whereIn('category_id', $oldIds)->update(['category_id' => $globalId]);
            }
        }
    }

    private function promoteDevises(): void
    {
        if (!Schema::hasTable('devises') || !Schema::hasTable('events')) {
            return;
        }

        $devises = DB::table('devises')
            ->select('libelle', DB::raw('MAX(statut) as statut'))
            ->whereNotNull('libelle')
            ->groupBy('libelle')
            ->get();

        foreach ($devises as $devise) {
            $globalId = DB::table('devises')
                ->whereNull('organization_id')
                ->where('libelle', $devise->libelle)
                ->value('id');

            if (!$globalId) {
                $globalId = DB::table('devises')->insertGetId([
                    'organization_id' => null,
                    'libelle' => $devise->libelle,
                    'statut' => (int) $devise->statut,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $oldIds = DB::table('devises')
                ->where('libelle', $devise->libelle)
                ->where('id', '!=', $globalId)
                ->pluck('id');

            if ($oldIds->isNotEmpty()) {
                DB::table('events')->whereIn('devise_id', $oldIds)->update(['devise_id' => $globalId]);
            }
        }
    }

    private function promoteCountriesAndCities(): void
    {
        if (!Schema::hasTable('countries') || !Schema::hasTable('cities')) {
            return;
        }

        $countries = DB::table('countries')
            ->select('nom', DB::raw('MAX(indicatif) as indicatif'), DB::raw('MAX(currency) as currency'), DB::raw('MAX(flag) as flag'), DB::raw('MAX(statut) as statut'))
            ->whereNotNull('nom')
            ->groupBy('nom')
            ->get();

        $countryMap = [];

        foreach ($countries as $country) {
            $globalId = DB::table('countries')
                ->whereNull('organization_id')
                ->where('nom', $country->nom)
                ->value('id');

            if (!$globalId) {
                $globalId = DB::table('countries')->insertGetId([
                    'organization_id' => null,
                    'nom' => $country->nom,
                    'indicatif' => $country->indicatif,
                    'currency' => $country->currency,
                    'flag' => $country->flag,
                    'statut' => (int) $country->statut,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $oldIds = DB::table('countries')->where('nom', $country->nom)->pluck('id');
            foreach ($oldIds as $oldId) {
                $countryMap[(int) $oldId] = (int) $globalId;
            }
        }

        $cities = DB::table('cities')
            ->join('countries', 'countries.id', '=', 'cities.country_id')
            ->select('cities.nom', 'cities.statut', 'cities.country_id', 'countries.nom as country_name')
            ->whereNotNull('cities.nom')
            ->orderBy('cities.nom')
            ->get();

        foreach ($cities as $city) {
            $globalCountryId = $countryMap[(int) $city->country_id] ?? null;
            if (!$globalCountryId) {
                continue;
            }

            $exists = DB::table('cities')
                ->whereNull('organization_id')
                ->where('country_id', $globalCountryId)
                ->where('nom', $city->nom)
                ->exists();

            if (!$exists) {
                DB::table('cities')->insert([
                    'organization_id' => null,
                    'country_id' => $globalCountryId,
                    'nom' => $city->nom,
                    'statut' => (int) $city->statut,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
