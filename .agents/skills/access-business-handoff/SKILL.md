---
name: access-business-handoff
description: Comprendre, maintenir et faire evoluer Acces Business, application Laravel SaaS multi-tenant de gestion evenementielle. A utiliser pour toute demande sur les evenements, invitations, OTP, representations, inscriptions, communications, SMS, relances, QR/check-in, Wallet, PA, organisations, landing page ou deploiement de ce depot.
---

# Reprise d'Acces Business

## Demarrage

1. Lire [`docs/system-handoff.md`](../../../docs/system-handoff.md) avant toute intervention transversale. C'est le guide detaille des fonctionnalites, routes, roles, invariants et operations.
2. Verifier le depot et `git status`; ne pas ecraser les changements locaux de l'utilisateur.
3. Confirmer la verite dans le code courant. Le guide aide a s'orienter, mais les routes, migrations et implementations actives priment.

## Carte du systeme

- Routes et permissions : `routes/web.php`, `app/Http/Middleware/CheckRole.php`, `EnsureTenantAccess.php`.
- Evenements : `EventController`, modele `Event`, vues `resources/views/events`.
- Invitation et acces : `EventAccessController`, `EventOtpController`, `EventRegistrationController`.
- SaaS/PA/landing : `PlatformAdminController`, `PlatformLandingController`, `SaasSettingsController`, modele `Organization` et `LandingPage`.
- Emails/SMS/relances : `EventCommunicationService`, `MailjetService`, `SmsService`, `SendEventBroadcast`, commandes `app/Console/Commands`, `routes/console.php`.
- Cartes/QR/Wallet : `InvitationCardService`, `WalletPassService`, routes invitation, Apple Wallet et QR.
- Donnees : `database/migrations`, modeles et `app/Support/SaasUsage.php`.

## Invariants obligatoires

- Respecter la separation `platform_admin` / utilisateurs tenant et le role effectif des routes.
- Toutes les donnees d'une organisation sont isolees via `organization_id`; toute action imbriquee doit etre autorisee par tenant et evenement, pas seulement par ID.
- Capacite, date limite, quotas et credits se valident cote serveur au moment de l'effet, pas uniquement dans Blade.
- Les transitions invite/representant, notifications et check-in doivent garder un historique coherent (`EventRegistrationActivity`).
- Les envois asynchrones doivent etre resilients aux retries, sans double debit ni effets dupliques.
- Ne jamais exposer `.env`, jetons, certificats, cle privee Apple, JSON du compte Google, tokens Mailjet ou donnees invite dans Git, docs ou logs.
- Conserver la compatibilite avec les statuts et donnees historiques ; ajouter une migration plutot que modifier une migration deja executee en production.

## Workflow de changement

1. Suivre la route jusqu'au middleware, controleur, service, modele, migration et vue concernes. Utiliser `rg` pour reperer routes, noms de champs et appels de service.
2. Identifier les effets de bord : courriel, SMS, queue, piece jointe, quota, credit, journal, QR/Wallet et stockage.
3. Implementer au plus pres des conventions Laravel du depot, avec validation serveur, autorisation, scopes tenant et gestion d'echec explicites.
4. Verifier les cas: utilisateur d'une autre organisation, role interdit, ressource inexistante, statut inattendu, expiration, doublon/retry, quota insuffisant et donnees historiques.
5. Executer les controles cibles disponibles (tests demandes ou pertinents, lint/syntaxe, routes, migrations, diff). Distinguer clairement ce qui a ete execute de ce qui reste a faire.
6. Actualiser `docs/system-handoff.md` et ce skill si le changement modifie le fonctionnement ou les procedures de reprise.

## Reperes fonctionnels

- PA : `/platform/login`, puis `/platform/*`; gere organisations, plans, forfaits de communication, referentiels globaux et contenu de la landing.
- Organisation : espace authentifie sous middleware `tenant`; les administrateurs d'organisation gerent les evenements et communications autorisees.
- Reponse invite : lien personnalise, contexte de verification, date limite et reponse; OTP demeure un parcours public alternatif selon le contexte.
- Delegation : l'invite fournit les coordonnees; le representant confirme avec son propre lien; carte et notifications suivent la confirmation.
- Diffusion : campagne enregistree, piece jointe privee tokenisee pour SMS, traitement asynchrone et suivi des compteurs/erreurs.
- Relance : commande planifiee chaque minute, heure par organisation par defaut 08:00; le cron systeme doit appeler `schedule:run`.
- Images : verifier les chemins et routes `/media/*`; ne pas supposer que `public/storage` couvre les fichiers de `public/uploads`.

## Prudence production

Ne pas deployer ni executer de commande destructive sur un serveur sans demande explicite. Avant migration, sauvegarde et controle staging ; utiliser le binaire PHP/Composer adapte a l'environnement (sur cPanel, verifier EA-PHP et ses extensions, notamment `fileinfo`). En production, queue workers, cron Laravel, stockage prive et permissions sont des dependances runtime, pas des details facultatifs.

Pour une investigation, demander des sorties expurgees (erreur exacte, statut HTTP, chemins et permissions sans secrets). Ne jamais demander de coller des cles privees ou le contenu du `.env`.
