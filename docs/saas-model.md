# Modele SaaS

Acces Business utilise un modele SaaS multi-tenant avec une seule base de donnees.

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

## Premiere iteration

Cette phase pose la fondation SaaS :

- creation de la table `organizations`
- rattachement des utilisateurs et donnees metier
- backfill des donnees existantes vers une organisation par defaut
- filtrage du dashboard, des evenements et des referentiels par organisation

Les prochaines etapes seront l'onboarding complet, les plans/quotas, puis la facturation.
