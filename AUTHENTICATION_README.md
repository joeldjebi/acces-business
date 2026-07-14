# Système d'Authentification avec Rôles

Ce système d'authentification permet de gérer différents niveaux d'accès avec Bootstrap pour une interface moderne et professionnelle.

## Fonctionnalités

- **Super Admin Auto-créé** : Le premier utilisateur qui s'inscrit devient automatiquement le super administrateur propriétaire (SaaS)
- **Inscription unique** : Seul le premier utilisateur peut s'inscrire, les autres doivent être créés par le super admin
- **Gestion des utilisateurs** : Le super admin peut créer des utilisateurs avec les rôles suivants :
  - Admin
  - Manager
  - Modérateur
- **Interface moderne** : Design professionnel avec Bootstrap 5
- **Sécurité** : Middleware de protection des routes par rôle

## Installation

1. **Exécuter les migrations** :
```bash
php artisan migrate
```

2. **Accéder à l'inscription** :
   - Allez sur `/register` ou `/` (redirige automatiquement vers l'inscription si aucun utilisateur n'existe)
   - Remplissez le formulaire d'inscription
   - Vous serez automatiquement créé en tant que **Super Administrateur**
   - Vous serez connecté automatiquement après l'inscription

⚠️ **Important** : Le premier utilisateur devient automatiquement super admin et peut ensuite créer d'autres utilisateurs !

## Structure des Rôles

### Super Admin
- Accès complet à toutes les fonctionnalités
- Peut créer, modifier et supprimer des utilisateurs
- Seul rôle qui ne peut pas être supprimé

### Admin
- Accès administrateur (à définir selon vos besoins)

### Manager
- Accès manager (à définir selon vos besoins)

### Modérateur
- Accès modérateur (à définir selon vos besoins)

## Routes Disponibles

### Routes Publiques
- `GET /` - Redirige vers `/register` si aucun utilisateur, sinon vers `/login`
- `GET /register` - Formulaire d'inscription (disponible uniquement si aucun utilisateur n'existe)
- `POST /register` - Traitement de l'inscription (crée automatiquement un super admin si c'est le premier utilisateur)
- `GET /login` - Formulaire de connexion
- `POST /login` - Traitement de la connexion
- `POST /logout` - Déconnexion

### Routes Protégées
- `GET /dashboard` - Tableau de bord (tous les utilisateurs authentifiés)

### Routes Super Admin
- `GET /users` - Liste des utilisateurs
- `GET /users/create` - Formulaire de création
- `POST /users` - Création d'un utilisateur
- `GET /users/{user}/edit` - Formulaire d'édition
- `PUT /users/{user}` - Mise à jour d'un utilisateur
- `DELETE /users/{user}` - Suppression d'un utilisateur

## Utilisation

### Première Installation (Création du Super Admin)
1. Accédez à `/` ou `/register`
2. Remplissez le formulaire d'inscription avec vos informations
3. Vous serez automatiquement créé en tant que **Super Administrateur**
4. Vous serez connecté automatiquement et redirigé vers le dashboard

### Connexion (après la création du premier utilisateur)
1. Accédez à `/login`
2. Entrez vos identifiants
3. Vous serez redirigé vers le dashboard

⚠️ **Note** : Après la création du premier utilisateur (super admin), l'inscription publique est désactivée. Seul le super admin peut créer de nouveaux utilisateurs.

### Création d'un utilisateur (Super Admin uniquement)
1. Connectez-vous en tant que super admin
2. Accédez à "Gestion Utilisateurs" dans le menu
3. Cliquez sur "Nouvel Utilisateur"
4. Remplissez le formulaire et sélectionnez le rôle
5. Cliquez sur "Créer l'utilisateur"

### Modification d'un utilisateur
1. Dans la liste des utilisateurs, cliquez sur l'icône d'édition
2. Modifiez les informations souhaitées
3. Cliquez sur "Enregistrer les modifications"

## Protection des Routes

Pour protéger une route avec un rôle spécifique, utilisez le middleware `role` :

```php
Route::middleware('role:admin')->group(function () {
    // Routes accessibles uniquement aux admins
});
```

Le super admin a automatiquement accès à toutes les routes protégées.

## Code Simple et Maintenable

Le code est organisé de manière claire :
- **Modèles** : `app/Models/User.php` - Gestion des rôles
- **Contrôleurs** : 
  - `app/Http/Controllers/AuthController.php` - Authentification
  - `app/Http/Controllers/UserController.php` - Gestion des utilisateurs
- **Middleware** : `app/Http/Middleware/CheckRole.php` - Vérification des rôles
- **Vues** : `resources/views/` - Interface utilisateur avec Bootstrap

## Personnalisation

### Ajouter un nouveau rôle
1. Modifiez la migration pour ajouter le rôle dans l'enum
2. Ajoutez une méthode `isNouveauRole()` dans le modèle User
3. Mettez à jour les formulaires de création/édition

### Modifier le design
Les styles sont définis dans `resources/views/layouts/app.blade.php` dans la section `<style>`.

## Sécurité

- Les mots de passe sont hashés avec bcrypt
- Protection CSRF sur tous les formulaires
- Validation des données d'entrée
- Protection des routes par middleware
- Le super admin ne peut pas être supprimé

