@php($prefix = $package?->id ? $package->id . '_' : 'new_')

<div class="col-12">
    <label class="form-label" for="{{ $prefix }}name">Nom</label>
    <input id="{{ $prefix }}name" class="form-control" name="name" value="{{ old('name', $package->name ?? '') }}" required placeholder="Pack SMS">
</div>
<div class="col-md-6">
    <label class="form-label" for="{{ $prefix }}channel">Canal</label>
    <select id="{{ $prefix }}channel" class="form-select" name="channel" required>
        @foreach($channels as $channel => $label)
            <option value="{{ $channel }}" {{ old('channel', $package->channel ?? '') === $channel ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div class="col-md-6">
    <label class="form-label" for="{{ $prefix }}currency">Devise</label>
    <input id="{{ $prefix }}currency" class="form-control" name="currency" value="{{ old('currency', $package->currency ?? 'XOF') }}" required>
</div>
<div class="col-md-6">
    <label class="form-label" for="{{ $prefix }}unit">Prix unité</label>
    <input id="{{ $prefix }}unit" type="number" min="0" class="form-control" name="unit_price" value="{{ old('unit_price', $package->unit_price ?? 0) }}" required>
</div>
<div class="col-md-6">
    <label class="form-label" for="{{ $prefix }}minimum">Quantité min.</label>
    <input id="{{ $prefix }}minimum" type="number" min="1" class="form-control" name="minimum_quantity" value="{{ old('minimum_quantity', $package->minimum_quantity ?? 1) }}" required>
</div>
<div class="col-md-6">
    <label class="form-label" for="{{ $prefix }}sort">Ordre</label>
    <input id="{{ $prefix }}sort" type="number" min="0" class="form-control" name="sort_order" value="{{ old('sort_order', $package->sort_order ?? 0) }}">
</div>
<div class="col-md-6 d-flex align-items-end">
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="{{ $prefix }}active" {{ old('is_active', $package->is_active ?? true) ? 'checked' : '' }}>
        <label class="form-check-label" for="{{ $prefix }}active">Actif</label>
    </div>
</div>
