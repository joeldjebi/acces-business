@php
    $prefix = $plan?->id ? $plan->id . '_' : 'new_';
    $features = old('features_text', $plan ? implode("\n", $plan->features ?? []) : '');
@endphp

<div class="col-lg-2">
    <label class="form-label" for="{{ $prefix }}slug">Slug</label>
    <input id="{{ $prefix }}slug" class="form-control" name="slug" value="{{ old('slug', $plan->slug ?? '') }}" required placeholder="starter">
</div>
<div class="col-lg-2">
    <label class="form-label" for="{{ $prefix }}name">Nom</label>
    <input id="{{ $prefix }}name" class="form-control" name="name" value="{{ old('name', $plan->name ?? '') }}" required placeholder="Starter">
</div>
<div class="col-lg-4">
    <label class="form-label" for="{{ $prefix }}tagline">Promesse</label>
    <input id="{{ $prefix }}tagline" class="form-control" name="tagline" value="{{ old('tagline', $plan->tagline ?? '') }}" placeholder="Pour lancer...">
</div>
<div class="col-lg-2">
    <label class="form-label" for="{{ $prefix }}monthly">Mensuel</label>
    <input id="{{ $prefix }}monthly" type="number" class="form-control" name="monthly_price" value="{{ old('monthly_price', $plan->monthly_price ?? 0) }}" min="0" required>
</div>
<div class="col-lg-2">
    <label class="form-label" for="{{ $prefix }}yearly">Annuel</label>
    <input id="{{ $prefix }}yearly" type="number" class="form-control" name="yearly_price" value="{{ old('yearly_price', $plan->yearly_price ?? 0) }}" min="0" required>
</div>
<div class="col-lg-2">
    <label class="form-label" for="{{ $prefix }}currency">Devise</label>
    <input id="{{ $prefix }}currency" class="form-control" name="currency" value="{{ old('currency', $plan->currency ?? 'XOF') }}" required>
</div>
<div class="col-lg-2">
    <label class="form-label" for="{{ $prefix }}events">Événements</label>
    <input id="{{ $prefix }}events" type="number" class="form-control" name="events_limit" value="{{ old('events_limit', $plan->events_limit ?? '') }}" min="1" placeholder="Illimité">
</div>
<div class="col-lg-2">
    <label class="form-label" for="{{ $prefix }}users">Utilisateurs</label>
    <input id="{{ $prefix }}users" type="number" class="form-control" name="users_limit" value="{{ old('users_limit', $plan->users_limit ?? '') }}" min="1" placeholder="Illimité">
</div>
<div class="col-lg-2">
    <label class="form-label" for="{{ $prefix }}invitations">Invitations</label>
    <input id="{{ $prefix }}invitations" type="number" class="form-control" name="invitations_limit" value="{{ old('invitations_limit', $plan->invitations_limit ?? '') }}" min="1" placeholder="Illimité">
</div>
<div class="col-lg-2">
    <label class="form-label" for="{{ $prefix }}sort">Ordre</label>
    <input id="{{ $prefix }}sort" type="number" class="form-control" name="sort_order" value="{{ old('sort_order', $plan->sort_order ?? 0) }}" min="0">
</div>
<div class="col-lg-12">
    <label class="form-label" for="{{ $prefix }}features">Fonctionnalités</label>
    <textarea id="{{ $prefix }}features" class="form-control" name="features_text" placeholder="Une fonctionnalité par ligne">{{ $features }}</textarea>
</div>
<div class="col-12">
    <div class="form-check">
        <input type="hidden" name="is_active" value="0">
        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="{{ $prefix }}active" {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }}>
        <label class="form-check-label" for="{{ $prefix }}active">Plan actif et disponible pour les clients</label>
    </div>
</div>
