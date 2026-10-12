@php
    $ieOrganization = auth()->user()->organization;
    $iePlatform = \App\Support\InvitationExperience::platform();
    $ieTemplates = \App\Support\InvitationExperience::templates();
    $ieOrg = \App\Support\InvitationExperience::organization($ieOrganization);
    $ieChoice = isset($event) && $event ? \App\Support\InvitationExperience::eventChoice($event) : ['template' => 'inherit', 'music' => 'inherit'];
    $ieTemplate = old('invitation_experience.template', $ieChoice['template']);
    $ieMusic = old('invitation_experience.music', $ieChoice['music']);
    $ieCustomAllowed = \App\Support\InvitationExperience::canUseCustomMusic($ieOrganization) && $ieOrg['custom_music'];
    $ieOrgTemplateName = $ieOrg['enabled'] ? ($ieTemplates[$ieOrg['template']]['name'] ?? $iePlatform['templates'][$iePlatform['default_template']]['name']) : 'Aucune';
    $ieOrgMusicName = ['platform' => 'Musique de la plateforme', 'custom' => 'Ma musique', 'none' => 'Sans musique'][$ieOrg['music_source']] ?? 'Musique de la plateforme';
    $ieIcons = ['classic' => 'bi-envelope-heart', 'prestige' => 'bi-gem', 'modern' => 'bi-book'];
@endphp

@if($iePlatform['enabled'] && $ieTemplates)
    <div class="form-group">
        <span class="form-label">Animation d’ouverture</span>
        <div class="choice-grid">
            <label class="choice">
                <input type="radio" name="invitation_experience[template]" value="inherit" @checked($ieTemplate === 'inherit')>
                <span class="choice-body"><i class="bi bi-building"></i><strong>Réglage de la structure</strong><small>{{ $ieOrgTemplateName }}</small></span>
            </label>
            @foreach($ieTemplates as $key => $tpl)
                <label class="choice">
                    <input type="radio" name="invitation_experience[template]" value="{{ $key }}" @checked($ieTemplate === $key)>
                    <span class="choice-body"><i class="bi {{ $ieIcons[$key] ?? 'bi-stars' }}"></i><strong>{{ $tpl['name'] }}</strong><small>{{ \Illuminate\Support\Str::limit($tpl['description'], 70) }}</small></span>
                </label>
            @endforeach
            <label class="choice">
                <input type="radio" name="invitation_experience[template]" value="none" @checked($ieTemplate === 'none')>
                <span class="choice-body"><i class="bi bi-slash-circle"></i><strong>Aucune</strong><small>Les invités arrivent directement sur le formulaire.</small></span>
            </label>
        </div>
        @error('invitation_experience.template')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <span class="form-label">Musique</span>
        <div class="choice-grid">
            <label class="choice">
                <input type="radio" name="invitation_experience[music]" value="inherit" @checked($ieMusic === 'inherit')>
                <span class="choice-body"><i class="bi bi-building"></i><strong>Réglage de la structure</strong><small>{{ $ieOrgMusicName }}</small></span>
            </label>
            <label class="choice">
                <input type="radio" name="invitation_experience[music]" value="platform" @checked($ieMusic === 'platform')>
                <span class="choice-body"><i class="bi bi-music-note-beamed"></i><strong>Plateforme</strong><small>{{ $iePlatform['music_title'] ?: 'Mélodie douce' }}</small></span>
            </label>
            <label class="choice" @if(!$ieCustomAllowed) title="{{ \App\Support\InvitationExperience::canUseCustomMusic($ieOrganization) ? 'Ajoutez d’abord votre musique dans Branding.' : \App\Support\InvitationExperience::customMusicDeniedReason($ieOrganization) }}" style="cursor: not-allowed; opacity: .6" @endif>
                <input type="radio" name="invitation_experience[music]" value="custom" @checked($ieMusic === 'custom' && $ieCustomAllowed) @disabled(!$ieCustomAllowed)>
                <span class="choice-body"><i class="bi {{ $ieCustomAllowed ? 'bi-vinyl' : 'bi-lock' }}"></i><strong>Ma musique</strong><small>{{ $ieCustomAllowed ? ($ieOrg['custom_music_title'] ?: 'Votre morceau') : (\App\Support\InvitationExperience::canUseCustomMusic($ieOrganization) ? 'À ajouter dans Branding' : 'Forfait payant requis') }}</small></span>
            </label>
            <label class="choice">
                <input type="radio" name="invitation_experience[music]" value="none" @checked($ieMusic === 'none')>
                <span class="choice-body"><i class="bi bi-volume-mute"></i><strong>Sans musique</strong><small>Animation silencieuse</small></span>
            </label>
        </div>
        @error('invitation_experience.music')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        @if(auth()->user()->isSuperAdmin())
            <small class="form-text"><a href="{{ route('saas.branding') }}#experience" target="_blank" rel="noopener">Gérer l’expérience d’invitation de la structure <i class="bi bi-box-arrow-up-right"></i></a></small>
        @endif
    </div>
@endif
