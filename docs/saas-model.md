# Modele SaaS

Acces Business utilise un modele SaaS multi-tenant avec une seule base de donnees et un schema partage.

## Choix multi-tenant

Le choix retenu est `single database / shared schema / row-level tenancy`.

Chaque ligne metier appartient a une organisation via `organization_id`. Cette approche est adaptee a la premiere version SaaS car elle reste simple a deployer, economique a maintenir et rapide a faire evoluer. Les bases separees par client pourront etre envisagees plus tard uniquement pour des besoins enterprise forts.

## Tenant

Le tenant est une `organization`.

Une organisation represente une entreprise, agence ou equipe qui gere ses propres evenements.

## Donnees rattachees a une organisation

Les donnees suivantes portent un `organization_id` :

- users
- events
- categories
- devises
- countries
- cities
- event_registrations
- event_access_links
- event_otp_verifications

Les donnees systeme comme les types de tarification et les visibilites restent globales pour le moment.

## Isolation

Les requetes applicatives doivent filtrer par `organization_id`.

Les modeles tenant-aware utilisent le trait `BelongsToOrganization`, qui fournit :

- la relation `organization()`
- le scope `forOrganization()`
- l'affectation automatique de `organization_id` lors d'une creation authentifiee

Les routes protegees utilisent aussi le middleware `tenant`. Il bloque les acces par route model binding lorsqu'un utilisateur tente d'ouvrir une ressource appartenant a une autre organisation.

Les validations des formulaires tenant-aware refusent aussi les IDs de referentiels rattaches a une autre organisation.

## Onboarding SaaS

La page d'inscription cree maintenant :

- une `organization`
- un utilisateur proprietaire de cette organisation
- un essai de 14 jours sur le plan `starter`

L'utilisateur cree devient administrateur principal de son espace et toutes les donnees creees ensuite sont rattachees a cette organisation.

## Premiere iteration

Cette phase pose la fondation SaaS :

- creation de la table `organizations`
- rattachement des utilisateurs et donnees metier
- backfill des donnees existantes vers une organisation par defaut
- filtrage du dashboard, des evenements et des referentiels par organisation
- onboarding self-service d'une nouvelle organisation
- middleware tenant sur les routes privees

Les prochaines etapes seront les plans/quotas, la facturation, puis une vraie console platform admin separee des admins d'organisation.
