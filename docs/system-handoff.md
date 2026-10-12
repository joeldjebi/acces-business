# Guide de reprise technique - Acces Business

Document de reference pour un agent ou developpeur qui reprend le systeme. Il decrit l'architecture et les comportements observes dans le code. Quand ce document et le code divergent, le code et les migrations font foi ; mettre alors ce guide a jour.

## 1. Identite et perimetre

Acces Business est une application web Laravel de gestion d'evenements et d'invitations. Elle couvre la creation d'evenements, la gestion des destinataires, le recueil des reponses, la delegation a un representant, les cartes d'invitation, les notifications email/SMS, le check-in QR, les relances, une console SaaS multi-organisation et une landing page publique administrable.

Le produit a deux espaces nettement distincts :

- L'administration de plateforme (PA, role `platform_admin`) gere la plateforme, les organisations, les plans, les forfaits SMS, les referentiels globaux et le contenu de la landing page.
- L'espace d'une organisation (roles `super_admin`, `admin`, `manager`, `moderateur`) gere les activites de cette organisation, selon les permissions associees aux routes.

Ce guide n'est pas une specification de securite certifiee. Les tests applicatifs existants sont tres limites ; toujours verifier les comportements critiques par inspection et tests cibles avant une mise en production.

## 2. Repere rapide dans le depot

| Zone | Responsabilite |
| --- | --- |
| `routes/web.php` | Routes publiques, PA, tenant, invitations, OTP, Wallet et QR |
| `app/Http/Controllers/` | Authentification, evenements, invitations, referentiels, SaaS, PA, medias |
| `app/Http/Middleware/` | Roles, isolation tenant, en-tetes HTTP |
| `app/Models/` | Organisations, utilisateurs, evenements, inscriptions, campagnes, referentiels |
| `app/Services/` | Email/SMS, historique, communication, cartes et Wallet |
| `app/Jobs/` | Travail asynchrone, notamment diffusion d'une campagne |
| `app/Console/Commands/` et `routes/console.php` | Relances et planification |
| `app/Support/` | Medias, quotas SaaS et regles reutilisables |
| `resources/views/` | Pages Blade, emails et interfaces PA/tenant |
| `database/migrations/` | Schema, evolution et rattachement organisationnel |
| `docs/saas-model.md` | Documentation historique du modele SaaS ; verifier les ecarts avec le code |
| `docs/infrastructure.md` | Notes d'infrastructure existantes |

Classes a ouvrir en premier : `EventController`, `EventAccessController`, `EventOtpController`, `EventRegistrationController`, `PlatformAdminController`, `PlatformLandingController`, `SaasSettingsController`, `EnsureTenantAccess`, `CheckRole`, `EventCommunicationService`, `MailjetService`, `SmsService`, `InvitationCardService`, `WalletPassService`.

## 3. Architecture et isolation des organisations

### 3.1 Modele de tenancy

Le modele est `single database / shared schema / row-level tenancy`. Les donnees metier d'un client sont associees a une organisation via `organization_id`. Les modeles tenant-aware utilisent le concern `App\\Models\\Concerns\\BelongsToOrganization` (verifier son chemin exact dans l'arborescence si le namespace evolue). Il apporte une relation organisation, un scope et l'affectation a la creation dans les cas prevus.

Le middleware `tenant` (`EnsureTenantAccess`) protege les routes d'organisation : il refuse les comptes PA dans cet espace, exige une organisation utilisable, bloque les organisations dont le statut n'est ni `active` ni `trialing`, et verifie les modeles lies a l'organisation resolus par les routes. Les routes PA sont separees sous `/platform/*` avec `auth` et `role:platform_admin`.

Regles imperatives pour toute evolution :

1. Ne jamais faire confiance a un `organization_id` fourni par le navigateur. Le derive de l'utilisateur authentifie ou d'une ressource deja autorisee.
2. Filtrer chaque lecture, modification, suppression, export et statistique par organisation ; ne pas compter uniquement sur la navigation ou l'interface.
3. Pour les identifiants de referentiels ou relations fournis par formulaire, valider l'appartenance tenant avec une regle de validation ou un chargement scope.
4. Proteger les routes et les bindings contre les IDOR. Tester deux organisations distinctes, y compris les exports et les routes imbriquees.
5. Les donnees systeme globales (categories, devises, pays/villes selon la gestion PA actuelle) sont administrables par le PA, pas par les organisateurs. Ne pas reintroduire de CRUD tenant pour ces listes sans decision produit explicite.
6. Toute table metier tenant-aware ajoutee doit avoir sa migration, son index utile, son rattachement organisationnel et les politiques de lecture/ecriture correspondantes.

### 3.2 Roles et acces

- `platform_admin` : administration globale via `/platform/login` puis `/platform/*`.
- `super_admin` : administration principale de son organisation, utilisateurs et reglages SaaS selon les routes.
- `admin` : gestion operationnelle d'evenements, invitations, inscriptions et check-in selon les groupes de routes.
- `manager`, `moderateur` : roles tenant connus ; confirmer les droits effectifs dans `routes/web.php`, les vues et les controles avant de leur attribuer une nouvelle action.

Les noms de role seuls ne garantissent pas une autorisation : route, middleware et verification dans le controleur doivent etre examines ensemble. Le flux de role `canCreateUsers` et les limites de quotas existent aussi.

## 4. Authentification, onboarding et SaaS

Les routes principales sont `/login`, `/platform/login`, `/register`, `/client/login`, `/client/login/{organization:slug}`, `/client/register` et `/client/register/{token}`. L'inscription client cree l'organisation et son administrateur, avec essai de 14 jours sur le plan `starter` selon le flux actuel. Un lien d'onboarding emis par le PA sert a creer le premier administrateur, expire et est consomme a l'activation.

`Organization` contient notamment nom, slug, logo, domaine, plan, statut, dates d'essai/abonnement et reglages JSON. L'etat de l'organisation et le plan influencent l'acces et les limites. Les plans sont en base (`subscription_plans`) avec repli sur `App\\Support\\SaasPlans` lorsque necessaire. Les quotas sont centralises en partie dans `App\\Support\\SaasUsage` : verifier chaque appel avant d'ajouter un nouveau point de consommation. La facturation est preparee en mode manuel ; ne pas presenter un paiement automatique comme implemente sans integration PSP et webhooks verifies.

Les parametres de l'organisation sont stockes dans `organizations.settings`, par exemple le fuseau et `response_reminders`. La personnalisation de marque de l'espace organisation est distincte de la landing page de la plateforme.

## 5. Cycle de vie d'un evenement

### 5.1 Creation et champs

Le modele `Event` regroupe le contenu et les regles d'un evenement. Les champs couvrent notamment titre, description, slug, media, PDF de programme, liens sociaux, categorie, dates/heures, fuseau, lieu, ville/pays, coordonnees et lien Google Maps, contacts organisateur, statut/visibilite, capacite, champs d'inscription et tarification.

Le formulaire utilise des champs datetime locaux pour les dates/heures et le controleur les normalise vers les colonnes de date et d'heure. Verifier toute modification de fuseau et les bornes chronologiques (fin apres debut). Le lieu propose une recherche de geocodage OpenStreetMap/Nominatim en tenant compte de ville et pays ; les coordonnees sont stockees et le lien affiche peut etre formate pour Google Maps. Le geocodage est limite par throttling ; conserver une attribution correcte au fournisseur et eviter les appels a chaque frappe sans debounce.

Le PDF du deroule est facultatif. Les liens sociaux sont une liste JSON label/url extensible. Les types tarifaires distinguent gratuit et payant ; prix et devise ne sont utiles que pour un evenement payant.

Les statuts applicatifs en base comprennent `draft`, `published`, `annule`, `termine`, `reporte` (verifier les constantes de traduction). L'interface simplifie actuellement le choix a brouillon/publie. Ne pas supprimer des valeurs historiques par simple changement de formulaire.

### 5.2 Capacite et date limite

La capacite maximale doit etre controlee au moment de l'envoi des invitations et lors de tout nouveau parcours qui augmente les participants. La validation UI ne suffit pas : verifier le service/controleur d'envoi, les courses concurrentes et les changements de capacite apres invitations. Le nombre concerne et la definition d'une place (invite original, representant, inscription) doivent rester coherents avec les regles produit existantes.

La date limite d'inscription/reponse est appliquee lors de l'acces aux liens d'invitation, de l'affichage et de la soumission des reponses, et lors de l'envoi de nouveaux liens. Toute nouvelle route publique doit reutiliser le controle existant `registrationDeadlineHasPassed()` ou son equivalent courant.

## 6. Invitations et reponses

### 6.1 Envoi et acces par lien

`EventAccessController` gere les liens individuels d'acces/invitation et l'import par CSV. Les acces sont representes notamment par `EventAccessLink`, avec jeton unique, adresse du destinataire, etat d'utilisation et relation evenement/organisation. L'envoi doit verifier l'autorisation, l'appartenance, la capacite, la date limite et les quotas SaaS avant d'envoyer.

Le lien d'invitation personnalise peut etablir un contexte de session deja verifie pour cet invite et rediriger vers `/events/{event}/respond`. Il n'est donc pas necessaire de redemander une confirmation email pour ce parcours. Les routes d'acces public doivent conserver des jetons imprévisibles, a usage et duree controles, sans exposer les informations privees dans les URLs de suivi.

### 6.2 OTP

Les routes `/events/{event}/request-otp` et `/events/{event}/verify-otp` permettent le parcours OTP public quand il est requis, notamment pour les acces non couverts par un lien email individuel. `EventOtpVerification` porte les donnees de verification. Respecter le throttling, la duree de validite, les tentatives et la liaison entre email, evenement et session. Ne pas supprimer l'OTP sans verifier les acces publics directs et les invitations privees.

### 6.3 Reponses

La page `/events/{event}/respond` permet en principe de choisir present, peut-etre ou absent. `EventRegistrationController` gere la presentation et la soumission. Les statuts historiques incluent `en_attente`, `present`, `peut_etre`, `absent` et `represente`. Le statut `represente` signifie que l'invite a delegue ; il ne faut pas le confondre avec la presence confirmee du representant.

Une confirmation peut produire une inscription, une carte PDF avec QR et des emails. Verifier le comportement de re-soumission, les transitions de statut, l'expiration et les actions idempotentes avant toute modification.

## 7. Representation et historique

L'invite indisponible peut designer un representant avec nom/prenoms, fonction, telephone/contact et email. Les champs et jetons de representation sont stockes autour de `EventRegistration`. Le representant recoit un lien de confirmation propre ; il confirme via `/representations/{token}/confirm` puis recoit sa carte si le parcours aboutit. L'invite represente est notifie apres confirmation.

L'organisateur doit pouvoir reconstituer l'histoire : designation, coordonnees saisies, envoi/renvoi de lien, confirmation, notification, emission de carte et check-in. `EventRegistrationActivity` et `EventRegistrationActivityLogger` portent ces traces. Pour une nouvelle action critique, ajouter une activite au bon moment (apres l'action effective, et pas avant un envoi echoue), avec acteur et contexte non sensible. Ne pas enregistrer de secrets, jetons complets ou donnees de paiement dans le journal.

Le bouton de renvoi du lien representant est dans les routes d'administration des inscriptions. Verifier l'autorisation evenement/organisation et ne pas reutiliser un jeton expire/invalide sans renouvellement securise.

## 8. Cartes, QR, Apple et Google Wallet

- `InvitationCardService` genere la carte PDF et les donnees QR ; la route publique de telechargement est `/invitation/download/{token}`.
- `WalletPassService` genere le pass Apple via `/wallet/apple/{token}.pkpass` et les liens d'ajout Google quand le provider/configuration est disponible.
- Le QR doit identifier un acces/inscription valide, etre non devinable et etre valide cote serveur. Un rendu visuel seul n'autorise jamais l'entree.
- Le check-in detecte les doubles scans et enregistre l'heure/l'operateur selon le chemin utilise. Ne pas accepter un check-in sans evenement, organisation et inscription conformes.

La page publique `/events/{event}/response-confirmation` propose aussi les boutons Apple/Google Wallet juste apres une confirmation (present ou peut-etre) : `showResponseConfirmation` retrouve l'inscription a partir du jeton flashe en session (`invitation_token`), limitee a l'evenement de l'URL et aux statuts `present`/`peut_etre`, puis appelle `WalletPassService::linksFor()`, comme l'email de carte. Le jeton n'etant qu'un flash, les boutons disparaissent si la page est rechargee : l'email reste la source durable. L'ordre des boutons s'adapte a l'appareil (Apple en premier sur iOS, Google sur Android). Une vue peut masquer le bandeau flash `success` du layout avec `@section('hide_flash', '1')`.

Les variables de configuration Wallet sont dans `.env.example`/`config/services.php` selon la version. Les identifiants et certificats prives Apple, la cle privee et le JSON du compte de service Google sont des secrets : ne jamais les copier dans ce guide, Git, logs, tickets ou reponse utilisateur. Les fichiers prives doivent etre deployes hors de `public/`, avec permissions minimales. Apres configuration, verifier le bundle `.pkpass`, certificat, cle, WWDR, pass type identifier, equipe, assets et logs de production. Les erreurs Apple sont souvent plus explicites dans `storage/logs/laravel.log` qu'a l'ecran.

## 9. Inscriptions, listes et check-in

Les vues d'administration principales comprennent la liste des inscriptions, le detail invite/representant, les participants confirmes et le tableau de check-in. Les routes sont dans le groupe `role:super_admin,admin` et sous middleware `auth,tenant`.

La page inscriptions fournit recherche/filtres, pagination, detail, communications bulk et historique. Les participants confirmes ont leur propre liste. Le check-in utilise scan/verification QR et une action authentifiee. Chaque requete de listing, detail et action doit etre scindee par l'evenement scope et l'organisation. Les compteurs affiches doivent utiliser la meme definition de statut que la liste ; les representes exigent une presentation distincte de l'invite et du representant.

## 10. Communications email et SMS

### 10.1 Services externes

`MailjetService` encapsule les emails via Mailjet. `SmsService` encapsule l'envoi SMS via Mailjet SMS et la configuration de token dans `config/services.php`. Ne jamais journaliser les tokens. Les erreurs provider doivent etre conservees sous une forme non sensible, liees a l'action/campagne, et rester visibles a l'administrateur.

### 10.2 Campagnes de diffusion aux inscrits

Depuis `/events/{event}/registrations`, l'organisateur peut envoyer un email ou SMS a une audience filtree : tout le monde, accepte/present, refuse/absent, peut-etre, represente. Le formulaire peut contenir piece jointe. Les formats autorises et la limite sont valides par serveur ; la version actuelle accepte PDF, documents Office et certaines images, limite 10 Mo.

`EventBroadcast` conserve canal, audience, sujet/message, metadonnees de fichier, jeton de telechargement, liste des destinataires et compteurs/etat. Les emails avec piece jointe sont envoyes par `SendEventBroadcast`. Pour un SMS avec fichier, un lien securise est genere pour telecharger le fichier ; le fichier est stocke sur un disque prive et servi par `/event-communications/files/{token}`. Ne pas rendre public ce stockage ni utiliser un nom de fichier utilisateur comme chemin.

Les credits SMS sont reserves/deduits selon le parcours de campagne ; les echecs sont reconciliés par le job. En cas de modification, examiner l'atomicite et les reprises (retry) pour eviter double envoi ou double remboursement. Une campagne async exige un worker Laravel actif. Suivre les etats `pending/processing/completed/failed` et les erreurs sans afficher de secret.

### 10.3 SMS de remerciement

Apres l'evenement, l'organisateur peut envoyer un SMS de remerciement aux participants admissibles si le solde de credits de son organisation le permet. Le comportement est centralise dans `EventCommunicationService` et journalise selon le code courant. Revalider credit, telephone, statut/eligibilite et idempotence cote serveur. Un compteur UI ne remplace pas le controle transactionnel du solde.

## 11. Relances automatiques

Le PA/administrateur d'organisation peut configurer les relances dans `/saas/communications`. Elles sont propres a l'organisation, activables/desactivables, et l'heure par defaut est `08:00`. Le reglage se trouve sous `organizations.settings.response_reminders` (au minimum `enabled` et `time` selon le code actuel).

La commande `events:send-response-reminders` parcourt les organisations actives ou en essai, selectionne celles dont l'heure correspond a l'heure courante (ou toutes avec `--force`), prend les inscriptions eligibles via `EventCommunicationService::pendingReminderQuery`, puis envoie une relance via `sendReminder`. Les personnes en attente sont filtrees et le service limite les envois repetes (verifier la fenetre exacte dans le service avant de l'annoncer).

`routes/console.php` programme le traitement chaque minute ; l'heure configuree par organisation decide quand une organisation sera traitee. En production, le cron Laravel doit appeler `php artisan schedule:run` chaque minute dans le bon dossier et avec le bon PHP. La commande a egalement une limite par organisation ; verifier la signature avant de modifier le deploiement. Pour diagnostic, executer manuellement la commande en environnement de test et lire ses compteurs/logs.

## 12. Landing page publique et administration PA

`/` charge `LandingPage::current()` et `mergedSettings()`. Les reglages sont stockes en JSON dans `landing_pages`; `LandingPage::defaults()` fournit les valeurs de repli lorsque des cles manquent. Les sections configurables incluent marque/meta, navigation, hero, visuels, confiance, indicateurs, fonctionnalites, representation, check-in, Wallet, tarifs/offres, securite, FAQ, CTA, footer et theme.

Le PA edite tout via `/platform/landing` (GET/PUT) dans `PlatformLandingController`. Les fichiers logo/hero/Wallet sont charges et ecrits dans `public/images/landing` dans le code actuel. Les autres medias sont servis par `/media/landing/{filename}` via `MediaController`. La normalisation gere les sections, elements repetables, interrupteurs, listes et couleurs. Conserver des valeurs de defaut robustes pour les anciennes lignes JSON et valider les uploads cote serveur.

Rendu public : `resources/views/landing.blade.php` (CSS/JS inline, aucune dependance au build Vite, absent en production) et les composants anonymes `resources/views/components/landing/` (`section-head`, `button`). Les couleurs derivees (or clair/fonce, gris, bordures) sont calculees en CSS via `color-mix()` a partir de `theme.ink/gold/background` : changer le theme dans le PA suffit. Les animations (titre mot par mot, zoom au scroll, texte illumine, compteurs depuis `hero_metrics`, flux check-in) respectent `prefers-reduced-motion` et le contenu reste visible sans JavaScript (classe `no-js`). Les lignes de demonstration representation/check-in restent statiques dans la vue. La section `journey` (ancre `parcours`) affiche un iPhone fixe dont l'ecran change selon l'etape active ; chaque ligne de `journey_steps` choisit son ecran parmi `LandingPage::JOURNEY_SCREENS` (valeur inconnue ramenee a `invitation` par `normalize()`). Les textes des ecrans et des cartes Wallet de demonstration viennent de `demo` ; la section Wallet affiche des cartes Apple/Google dessinees (`components/landing/wallet-pass`, QR decoratif non scannable `components/landing/qr`) et les cles `wallet.apple_*`, `wallet.google_*`, `wallet.items`.

Les valeurs presentes dans `LandingPage::defaults()` sont des donnees de repli, pas les secrets. Pour rendre un texte ou visuel editable, ajouter la cle au formulaire PA, a sa validation/normalisation, aux vues publiques et au guide, avec un repli raisonnable.

### 12.1 Console authentifiee (layout)

`resources/views/layouts/app.blade.php` porte la coque de toutes les pages authentifiees (tenant et PA) : barre laterale groupee par sections (menu construit dans un tableau `$navGroups` en tete du layout), barre superieure collante avec fil d'Ariane et bouton « Nouvel evenement » (tenant), menu mobile (bouton `data-nav-toggle`, fermeture par fond, lien ou Echap). Les jetons de design sont des variables CSS `--app-*` (encre, or, lignes, rayons, ombres) ; les pages doivent les reutiliser plutot que redefinir leurs couleurs. Composant d'en-tete partage : `<x-app.page-header kicker title subtitle>` avec slot `actions` et classes de boutons `ui-btn ui-btn-primary|ghost|danger`. La liste des evenements recoit `registrations_count` et `$statusCounts` (compteurs par statut, scopes organisation) depuis `EventController::index`. Le formulaire de creation garde les memes noms de champs ; statut, visibilite et tarification sont des boutons radio, un apercu en direct est calcule cote navigateur et, apres une erreur de validation serveur, l'etape contenant la premiere erreur s'ouvre automatiquement. Le layout definit aussi un petit kit CSS partage (`ui-card`, `ui-stats`/`ui-stat`, `ui-pill` + tons `success|warning|danger|info|gold`, `ui-table`, `ui-toolbar`/`ui-search`, `ui-person`/`ui-avatar`, `ui-icon-btn`, `ui-empty`, `ui-pager`) a reutiliser dans les nouvelles vues. Les pages d'un evenement (detail, invitations, inscriptions, presences, check-in) partagent `<x-app.event-header :event active="...">`, qui affiche le contexte de l'evenement et des onglets ; les onglets de gestion n'apparaissent que pour `super_admin`/`admin`, comme les routes correspondantes. Les messages flash `success`/`error` sont affiches par le layout : une page ne doit afficher elle-meme que `warning`, sinon ils apparaissent en double. Eviter `animation-fill-mode: both|forwards` sur une animation de `transform` d'un conteneur : le transform residuel cree un contexte d'empilement qui place les modals Bootstrap sous leur fond et coupe les menus deroulants ; utiliser `backwards`. Les paginations de ces vues forcent `links('pagination::bootstrap-5')` car aucune vue de pagination par defaut n'est configuree. Champs de formulaire : un style unique (40 px, 14 px, rayon 10 px) est defini dans le layout pour `.form-control`, `.form-select`, `.form-label`, `.form-text` ; ne pas le redefinir par page. Les formulaires de creation et d'edition d'evenement partagent `events/partials/form-kit` (zones de depot, cartes de choix, interrupteur) via la classe `.event-form`. Le tableau de bord tenant utilise Chart.js 4.4.1 (CDN jsdelivr, version figee) pour l'activite sur 90 jours, la repartition des reponses, l'entonnoir et le top des evenements ; toutes les requetes sont filtrees par `organization_id`, et une version tableau masquee accompagne la courbe d'activite. Pages SaaS : `saas/billing` expose enfin le formulaire `saas.billing.update` (infos de facturation) ; les paiements de plans et de credits restent enregistres manuellement (facture `paid` immediate, sans PSP), ce que l'interface indique explicitement. L'application est en francais : `APP_LOCALE=fr` (defaut de `config/app.php` aussi passe a `fr`) avec les traductions dans `lang/fr/` (validation avec noms de champs lisibles dans `attributes`, auth, mots de passe, pagination) et `lang/fr.json` ; ajouter le nom francais de tout nouveau champ valide dans `lang/fr/validation.php`. Le formulaire de creation d'evenement desactive « Continuer » tant que les champs `required` de l'etape ne sont pas remplis (et, a l'etape 2, tant que la fin n'est pas posterieure au debut) ; le champ `site_web` (site de la structure, facultatif) est place a cote des reseaux sociaux.

### 12.2 Experience d'invitation (animation d'ouverture et musique)

Sur `/events/{event}/respond`, l'invite peut etre accueilli par une animation (enveloppe ou livret) accompagnee d'une musique. Tout passe par `App\Support\InvitationExperience` :

- **3 animations codees** (`classic`, `prestige`, `modern`) dans `components/invitation/envelope.blade.php` (+ `card-inner`). Le PA ne cree pas d'animation : il les active/desactive, les renomme, choisit celle par defaut et la musique par defaut sur `/platform/invitation-experience` (`PlatformInvitationExperienceController`, reglages stockes dans la table `platform_settings`, cle `invitation_experience`).
- **Musique par defaut** : sans fichier, une melodie libre de droits est generee dans le navigateur (Web Audio) ; le PA peut la remplacer par un MP3/M4A/OGG (8 Mo max).
- **Structure** : `/saas/branding#experience` (`saas.branding.experience.update`, super_admin) -> `organizations.settings.invitation_experience` (`enabled`, `template`, `music_source` platform|custom|none, `custom_music`, `custom_music_title`, `rights_confirmed_at`).
- **Evenement** : colonne JSON `events.invitation_experience` (`template` inherit|none|cle, `music` inherit|platform|custom|none), saisie dans les formulaires de creation (etape 7) et d'edition (section « Experience invite »).
- **Regle commerciale** : la musique personnalisee exige `subscription_plans.allows_custom_music` (case « Musique perso. » du PA) **et** un abonnement paye en cours (`SaasUsage::hasPaidSubscription` : statut `active` + `subscription_ends_at` futur ; l'essai ne compte pas). Elle est verifiee a l'enregistrement (structure et evenement) **et** a l'affichage : si l'abonnement expire, l'invite entend la musique de la plateforme.
- Animation : chorégraphie GSAP 3.12.5 (cdnjs, version figée) + particules sur canvas + petits effets sonores synthétisés (cachet, souffle). Sans GSAP ou si l'invité demande à réduire les animations, l'écran d'ouverture est simplement sauté. Fin d'animation : bouton « Découvrir mon invitation » (passage automatique après 6,5 s).
- Fichiers audio : `public/uploads/music`, servis par `/media/music/{filename}` (`MediaController::music`, nom filtre). Le son ne demarre qu'apres le geste de l'invite (contrainte des navigateurs) ; l'animation ne se rejoue pas a la visite suivante (localStorage par evenement et email) et est sautee si l'invite a demande a reduire les animations.
- Migrations : `2026_10_12_000001` a `000003` (additives). Les executer en production avant de deployer les vues.

## 13. Fichiers et medias

Les routes de medias sont centralisees au debut de `routes/web.php`, sous `/media/...`. `EventMedia` et `MediaController` assurent la logique de chemin/diffusion. Les images evenement et logos utilisent des repertoires publics `public/uploads/...` dans la version actuelle ; landing images sous `public/images/landing`. Le deroule evenement est accessible par route PDF. Les pieces jointes de campagne doivent rester sur disque prive avec route tokenisee.

Lorsqu'une image manque en production, verifier dans cet ordre : chemin exact en base, existence/fichier et casse Linux, chemin absolu `public_path`, permissions du compte PHP/Apache, URL construite via la route de media, reponse HTTP (404 vs 403 vs 500), cache de vues/config, logs Laravel et configuration document root. Ne pas supposer qu'un symlink `public/storage` corrige un fichier enregistre sous `public/uploads` ou inversement. Une migration de repertoire doit conserver compatibilite avec les anciens chemins en base ou fournir une migration de donnees explicite.

## 14. Schema et evolution des donnees

Les migrations sont la source de verite du schema ; elles ont ete ajoutees par vagues depuis janvier 2026, avec notamment tenancy/SaaS, representation, journal d'activite, landing pages, medias/programme et campagnes de diffusion. Avant toute migration :

1. lire migrations existantes et modele concernes ; ne pas editer une migration deja appliquee en production pour changer son effet ;
2. ajouter une migration additive avec rollback reflechi et index/contraintes adaptes ;
3. penser aux lignes historiques nulles, donnees en double et backfill ;
4. valider en staging et sauvegarder la base avant prod ;
5. garder la migration compatible avec un deploiement progressif si du code ancien peut tourner pendant l'operation.

Ne pas deduire l'etat actuel d'une table a partir du seul nom d'une migration ou de `docs/saas-model.md` ; confirmer avec les migrations et modeles actifs.

## 15. Configuration, secrets et production

Les integrateurs incluent Mailjet email/SMS, Apple Wallet, Google Wallet, URL/app et connexion DB. Les variables exactes doivent etre lues dans `config/services.php`, `config/*.php` et `.env.example`. Le `.env` de production n'est pas versionne et ne doit jamais etre imprime/partage.

Checklist de deploiement Laravel, a adapter au gestionnaire de processus du serveur :

- sauvegarde DB/fichiers et plan de retour ; deployer le commit attendu ;
- installer les dependances avec la version PHP CLI correspondant au site et toutes les extensions requises ; sur cPanel, utiliser le binaire EA-PHP adapte. `ext-fileinfo` est requis par Flysystem dans les configurations usuelles ; installer/activer l'extension avec le gestionnaire du systeme reel (Ubuntu utilise normalement `apt`, pas `dnf`; cPanel peut fournir ses outils EA4) ;
- `php artisan migrate --force`, uniquement apres verification des migrations et sauvegarde ;
- `php artisan optimize:clear`, puis les caches choisis (`config:cache`, `route:cache`, `view:cache`) selon le processus de deploiement ;
- `php artisan storage:link` seulement si les fichiers sont servis depuis le disk public Laravel ; un lien existant doit etre inspecte (`ls -l`, `readlink`) avant toute suppression/remplacement ;
- droits d'ecriture minimaux pour `storage` et `bootstrap/cache` ; fichiers Wallet prives accessibles au processus PHP, pas au web ;
- worker(s) de queue surveilles et redemarres apres deploiement (`queue:restart`) ;
- cron `* * * * * cd /chemin/app && /chemin/php artisan schedule:run >> /dev/null 2>&1` ;
- HTTPS, `APP_DEBUG=false`, `APP_ENV=production`, `APP_URL` exact, cookies/session securises et sauvegardes ;
- verifier `route:list`, login PA/tenant, envoi email/SMS de test, fichier public/prive, relance, invitation/representant, PDF/QR, check-in et Wallet sur un evenement non critique.

Ne pas lancer `git pull`, migration destructive, nettoyage de stockage, suppression de symlink ou operation de production sans demande explicite et contexte d'acces. Ne jamais demander a l'agent de coller des cles privees dans le chat.

## 16. Diagnostic rapide

| Symptomes | Verifications prioritaires |
| --- | --- |
| 403/404 entre deux organisations | middleware `tenant`, organization_id utilisateur/ressource, scope route-binding, statut organisation |
| bouton absent | vue et conditions Blade, role authentifie, variables passees par controleur, cache de vues, route effective |
| mail non recu | configuration Mailjet, logs, adresse, queue/worker, reponse provider, adresse d'expediteur valide |
| SMS/campagne bloquee | token Mailjet SMS sans l'exposer, credits, telephone normalise, job/queue, statut `EventBroadcast`, erreurs par destinataire |
| document SMS inaccessible | jeton et route, fichier sur disque prive, expiration/autorisation, permissions du processus PHP |
| image 404 en prod | chemin DB vs repertoire public, route `/media`, document root, casse et permissions ; le symlink storage n'est pas universel |
| Apple Wallet refuse | log Laravel, certificat/cle/WWDR, pass type, team, validite cert, assets, format pass et signature |
| relance non partie | cron schedule chaque minute, timezone/heure serveur, setting organisation, statut actif, heure configuree, eligibilite service, queue si applicable |
| erreur upload `fileinfo` | extension active pour le PHP CLI et PHP-FPM/site, binaire PHP utilise par Composer, configuration systeme/EA4 |
| fonction modifiee mais absente | route:list, role/middleware, cache de routes/vues, vue et layout associes |

Toujours consulter `storage/logs/laravel.log` et les logs du serveur web/worker. Rediger les traces partagees pour enlever emails, numeros, jetons et secrets.

## 17. Methode de reprise par un agent

Avant un changement :

1. Confirmer le depot courant, branche, `git status` et l'absence de changements utilisateur a ecraser.
2. Lire la route, middleware, controleur, modele, migration, service et vue qui forment le parcours ; rechercher les usages avec `rg`.
3. Etablir les invariants : tenant, role, statut, date limite, capacite, quota/credits, effets email/SMS/queue et journalisation.
4. Faire le changement le plus local possible en suivant les conventions existantes ; ne pas exposer de secret ou de donnees d'une autre organisation.
5. Ajouter des tests cibles lorsque le projet le permet, en priorite autorisation tenant, transitions d'etat, limites/quota, cas d'echec et compatibilite historique.
6. Verifier syntaxe, migrations, routes, rendu Blade et diff ; ne jamais annoncer un test/deploiement non effectue.
7. Mettre ce guide et le skill a jour si architecture, routes, configuration ou comportement majeur change.

Pour les parcours concurrents (envoi massif, capacite, credits), raisonner en transaction/verrouillage, reprises de jobs et idempotence. Pour les jetons publics, examiner entropie, expiration, consommation, enumeration et fuite dans les logs/referers. Pour les nouveaux champs de formulaire, controler validation serveur, mass assignment, normalisation et rendu echappe Blade.

## 18. Verification de ce document

Le guide est base sur les routes, modeles, controles, services, commandes et migrations de la version inspectee. Les details sensibles (valeurs `.env`, certificats, cles, numeros, comptes) sont volontairement omis. Avant toute reprise, verifier l'etat reel de la branche et actualiser les sections qui ont change.
