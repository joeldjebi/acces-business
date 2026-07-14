# Infrastructure SaaS

## Environnements

Prevoir au minimum :

- `local` pour le developpement
- `staging` pour tester migrations, emails, OTP et parcours paiement
- `production` pour les clients

## Variables importantes

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://...`
- `SESSION_ENCRYPT=true`
- `SESSION_SECURE_COOKIE=true`
- `SESSION_HTTP_ONLY=true`
- `SESSION_SAME_SITE=lax`
- `PLATFORM_ADMIN_EMAIL=...`
- `PLATFORM_ADMIN_PASSWORD=...`
- `BILLING_PROVIDER=manual` pour la phase actuelle
- `BILLING_CURRENCY=XOF`

## Securite

La plateforme utilise :

- separation `platform_admin` / `super_admin`
- middleware tenant sur les routes organisation
- blocage des organisations `suspended` et `cancelled`
- throttling login/register
- headers HTTP de securite
- stockage des logos dans le disk public Laravel

## Donnees

Le modele actuel est `single database / shared schema`.

Chaque table metier doit conserver `organization_id` et toute nouvelle fonctionnalite doit ajouter :

- une migration avec `organization_id`
- le trait `BelongsToOrganization` si le modele est tenant-aware
- des validations `Rule::exists(...)->where('organization_id', ...)`
- une verification via route model binding ou middleware

## Deploiement

Avant production :

- executer les migrations sur staging
- verifier `php artisan route:list --except-vendor`
- verifier `php artisan test`
- activer HTTPS
- executer `php artisan storage:link`
- configurer un cron pour `php artisan schedule:run`
- configurer les workers de queue si les emails sont envoyes en async

## Facturation

La facturation est prete en mode manuel avec `billing_invoices`.

La prochaine etape consiste a ajouter un provider paiement :

- Stripe pour carte internationale
- PayPal si necessaire
- CinetPay/PayDunya pour Mobile Money Afrique de l'Ouest

Le provider devra mettre a jour `billing_invoices.status`, `organizations.status` et `organizations.subscription_ends_at` via webhooks signes.
