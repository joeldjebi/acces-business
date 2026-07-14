# Modele SaaS

Acces Business utilise un modele SaaS multi-tenant avec une seule base de donnees et un schema partage.

## Choix multi-tenant

Le choix retenu est `single database / shared schema / row-level tenancy`.

Chaque ligne metier appartient a une organisation via `organization_id`. Cette approche est adaptee a la premiere version SaaS car elle reste simple a deployer, economique a maintenir et rapide a faire evoluer. Les bases separees par client pourront etre envisagees plus tard uniquement pour des besoins enterprise forts.

## Tenant

Le tenant est une `organization`.

Une organisation represente une entreprise, agence ou equipe qui gere ses propres evenements.

## Administration plateforme

Le role `platform_admin` est separe des admins d'organisation.

- `platform_admin` supervise la plateforme via `/platform/*`
- connexion SA plateforme : `/platform/login`
- `super_admin` administre uniquement son organisation
- les routes tenant restent protegees par `tenant`
- un `platform_admin` sans organisation ne peut pas ouvrir les ecrans tenant

Le super admin plateforme peut aussi :

- creer un client depuis `/platform/organizations`
- generer un lien register client securise via `/client/register/{token}`
- fournir au client le lien login dedie `/client/login/{slug}`
- modifier les roles des utilisateurs d'une organisation : `super_admin`, `admin`, `manager`, `moderateur`

Le lien register client sert uniquement a creer le premier administrateur principal de l'organisation. Il expire et il est supprime apres activation.

Les clients peuvent aussi s'inscrire eux-memes sans passer par le SA via `/client/register`, puis se connecter via `/client/login`.

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

Les organisations dont le statut n'est pas `active` ou `trialing` sont bloquees sur les routes tenant.

Les reponses HTTP passent par un middleware de headers de securite : anti-clickjacking, MIME sniffing, referrer policy, permissions policy et HSTS lorsque la requete est en HTTPS.

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

## Etat roadmap

- Modele SaaS multi-tenant : implemente
- Organisation et isolation des donnees : implemente
- Onboarding SaaS : implemente
- Dashboard adapte SaaS : implemente
- Plans SaaS : implemente en configuration applicative
- Facturation : preparee en mode manuel avec factures internes
- Branding par organisation : implemente pour la console
- Super admin plateforme : implemente
- Infrastructure : preparee via variables d'environnement, config SaaS et documentation
- Paiement en ligne : a brancher sur un PSP lorsque le choix Stripe/PayPal/CinetPay est valide
