# Système d'Invitation et de Gestion d'Événements

## Configuration Mailjet

Pour utiliser le système d'envoi d'emails, vous devez configurer Mailjet dans votre fichier `.env` :

```env
MAILJET_API_KEY_PUBLIC=your_public_api_key
MAILJET_API_KEY_PRIVATE=your_private_api_key
MAILJET_FROM_EMAIL=noreply@example.com
MAILJET_FROM_NAME="Nom de votre application"
```

### Obtenir les clés API Mailjet

1. Créez un compte sur [Mailjet](https://www.mailjet.com/)
2. Allez dans votre compte → API Keys
3. Copiez votre API Key Public et API Key Private
4. Ajoutez-les dans votre fichier `.env`

## Fonctionnalités

### 1. Événements Publics

- **Inscription directe** : Les utilisateurs peuvent s'inscrire directement via un formulaire
- **Carte d'invitation automatique** : Envoi immédiat de la carte d'invitation avec QR code par email
- **Pas de vérification OTP** : Accès libre à tous

### 2. Événements Privés

- **Lien d'accès** : Les admins envoient un lien d'accès par email
- **Vérification OTP** : L'utilisateur doit entrer son email et recevoir un code OTP
- **Réponse obligatoire** : Après validation OTP, l'utilisateur doit répondre (Présent, Peut-être, Absent)
- **Carte d'invitation** : Envoyée uniquement si réponse "Présent" ou "Peut-être"

### 3. Événements sur Invitation

- **Même processus que les événements privés**
- **Contrôle total** : Seuls les utilisateurs avec un lien d'accès peuvent participer

## Flux de travail

### Pour un événement Public

1. User visite la page de l'événement
2. Remplit le formulaire d'inscription
3. Reçoit immédiatement la carte d'invitation par email

### Pour un événement Privé/Invitation

1. Admin envoie un lien d'accès à un utilisateur
2. User clique sur le lien
3. Entre son email
4. Reçoit un code OTP par email (valide 15 minutes)
5. Entre le code OTP
6. Répond à l'invitation (Présent, Peut-être, Absent)
7. Si Présent ou Peut-être : reçoit la carte d'invitation par email

## Routes disponibles

### Publiques
- `GET /events/{event}` - Voir un événement
- `POST /events/{event}/register` - S'inscrire (Public uniquement)
- `GET /events/{event}/access/{token}` - Accéder avec un lien (Privé/Invitation)
- `GET /events/verify-qr/{token}` - Vérifier un QR code

### Authentifiées
- `GET /events/{event}/respond` - Formulaire de réponse (après OTP)
- `POST /events/{event}/respond` - Soumettre la réponse
- `POST /events/{event}/request-otp` - Demander un code OTP
- `POST /events/{event}/verify-otp` - Vérifier le code OTP

### Admin uniquement
- `GET /events/{event}/send-link` - Formulaire d'envoi de liens
- `POST /events/{event}/send-link` - Envoyer des liens d'accès
- `GET /events/{event}/registrations` - Voir les inscriptions

## Structure de base de données

### Tables créées

1. **event_registrations** - Inscriptions aux événements
2. **event_otp_verifications** - Codes OTP pour vérification
3. **event_access_links** - Liens d'accès envoyés par les admins

## Services

### MailjetService

Service pour envoyer des emails via l'API Mailjet.

### InvitationCardService

Service pour générer et envoyer les cartes d'invitation avec QR codes.

## QR Code

Les QR codes sont générés via une API externe (api.qrserver.com) et contiennent :
- ID de l'événement
- Token unique de l'inscription
- Email du participant
- Nom et prénom

Le QR code peut être scanné pour vérifier l'accès à l'événement.

## Notes importantes

1. Les codes OTP expirent après 15 minutes
2. Les cartes d'invitation sont envoyées uniquement pour les réponses "Présent" ou "Peut-être"
3. Les événements publics sont accessibles à tous
4. Les événements privés nécessitent une authentification
5. Les événements sur invitation nécessitent un lien d'accès envoyé par un admin

