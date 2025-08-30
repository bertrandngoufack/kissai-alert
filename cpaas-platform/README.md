# kissai-alert

Plate-forme CPaaS multicanal basée sur CodeIgniter 4 + MariaDB, avec API OTP, Email, et Swagger UI.

## Démarrage rapide

1. Prérequis: PHP 8.1+, MariaDB, Composer
2. Copier `env` vers `.env` puis définir la base de données et l’URL
3. Installer dépendances: `composer install`
4. Créer la base et lancer migrations: `php spark migrate`
5. Lancer serveur dev: `php spark serve`

## API

- POST `/api/rest/otp/generate` – Génère un OTP
- POST `/api/rest/otp/check` – Vérifie un OTP
- POST `/api/rest/email/send` – Envoi d’email via SMTP du client

Toutes les routes API nécessitent un header `Authorization: Basic <API_KEY>`.

Swagger UI: ouvrir `/swagger` ou charger le JSON `/swagger.json`.
