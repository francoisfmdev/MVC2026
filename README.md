# Framework MVC pédagogique (PHP ^8.2)

**Fonctionnement interne (MVC, routage, SQL, CSRF) :** [docs/fonctionnement.md](docs/fonctionnement.md)

Framework minimaliste pour l’enseignement : routage AltoRouter, vues Twig, SQL via PDO / QueryBuilder (inspectable avec `toSql()`), **sans ORM** et **sans facades**. Cible officielle : **PHP 8.2 et versions 8.x suivantes** (8.2, 8.3, 8.4).

Le chemin principal est **XAMPP** (Apache + MariaDB). Docker et le serveur PHP intégré sont optionnels.

## Prérequis

- PHP **^8.2** (`pdo_mysql`, `mbstring`, `json`) — XAMPP 8.2 convient (ex. 8.2.12)
- Composer
- MariaDB / MySQL (phpMyAdmin sous XAMPP)

## 1. Installation XAMPP (recommandé)

1. Copier ce dossier dans `C:\xampp\htdocs\framework`.
2. Dans un terminal :

```bash
cd C:\xampp\htdocs\framework
composer install
copy .env.example .env
```

3. Éditer `.env` :

```env
APP_BASE_PATH=/framework
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=framework
DB_USER=root
DB_PASS=
```

`APP_BASE_PATH` doit correspondre à l’URL après `http://localhost` (ici `/framework`). Si vous créez un VirtualHost dont le DocumentRoot pointe sur `public/`, laissez `APP_BASE_PATH` vide.

4. Créer la base `framework` dans phpMyAdmin.
5. Exécuter les migrations (PHP de XAMPP) :

```bash
C:\xampp\php\php.exe framework migrate
```

6. Démarrer Apache + MySQL dans le panneau XAMPP, puis ouvrir [http://localhost/framework/](http://localhost/framework/).

Le `.htaccess` à la racine redirige vers `public/` (front controller). `mod_rewrite` est actif par défaut dans XAMPP.

Exemple CRUD : [http://localhost/framework/users](http://localhost/framework/users)  
API : [http://localhost/framework/api/users](http://localhost/framework/api/users)

## 2. Docker (optionnel)

Ne pas lancer Docker **et** MySQL XAMPP sur le même port 3306. Ici MariaDB est publié sur l’hôte en **3307** ; l’application dans Compose parle au service `db` sur le port 3306 interne.

```bash
docker compose up -d --build
docker compose exec app php framework migrate
```

Application : [http://localhost:8080](http://localhost:8080)

Dans ce mode, `APP_BASE_PATH` est vide (DocumentRoot = `public/`).

## 3. Serveur PHP intégré (optionnel)

```bash
copy .env.example .env
```

Mettre `APP_BASE_PATH` **vide**, puis :

```bash
php -S localhost:8000 -t public
php framework migrate
```

Ouvrir [http://localhost:8000](http://localhost:8000).

## Commandes CLI

```text
php framework help
php framework make:controller Nom
php framework make:controller Nom --api
php framework make:model Nom
php framework make:model Nom --migration
php framework make:dto Nom
php framework make:dto Nom --model=User
php framework make:view users/index
php framework make:migration create_posts_table
php framework make:middleware Auth
php framework migrate
```

Sous XAMPP, préfixer avec `C:\xampp\php\php.exe` si `php` n’est pas dans le PATH.

## Tests

```bash
copy .env.testing.example .env.testing
composer test
```

Les tests **Unit** (QueryBuilder, DTO, Env) n’ont pas besoin de MySQL.  
Les tests **Feature** (insertion / lecture `User`) sont ignorés si la base `framework_test` est inaccessible.

## Structure utile

| Dossier | Rôle |
|---|---|
| `app/Web/Controllers` + `app/Web/Views` | Pages HTML (Twig) |
| `app/Api/Controllers` | JSON |
| `app/Models` | Un modèle = une table, partagé Web / API |
| `app/DTO` | Transfert de données (hydratation explicite) |
| `core/QueryBuilder.php` | Clauses SQL nommées + `toSql()` / `getBindings()` |
| `config/routes.php` | Routes web et `/api/...` |
| `config/services.php` | Bindings du conteneur (un seul fichier) |
| `database/migrations` | `up()` / `down()` en SQL brut |

## API

Les routes `/api/*` répondent en JSON (pas de négociation `Accept`).  
Le jeton d’exemple se configure avec `API_TOKEN`. Le middleware `AuthMiddleware` attend `Authorization: Bearer …` côté API, et `user_id` en session côté web. Il n’est pas appliqué aux routes CRUD de démo (à ajouter dans `config/routes.php` si besoin).

Le CSRF est injecté automatiquement sur les `POST` / `PUT` / `PATCH` / `DELETE` **web** (fonction Twig `csrf_field()`).
