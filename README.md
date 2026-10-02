# Framework MVC pédagogique (PHP ^8.2)

**Cours complet (à lire dans l’ordre) :** [docs/cours.md](docs/cours.md)

Framework minimaliste pour l’enseignement : AltoRouter, Twig, PDO / QueryBuilder (`toSql()`), **sans ORM** et **sans facades**. PHP **^8.2**.

## Prérequis

- PHP ^8.2 (`pdo_mysql`, `mbstring`, `json`) — XAMPP 8.2 convient
- Composer
- MariaDB / MySQL (phpMyAdmin)

## 1. Installation XAMPP (recommandé)

1. Copier le projet dans `C:\xampp\htdocs\framework`.
2. `composer install` puis `copy .env.example .env`
3. `.env` : `APP_BASE_PATH=/framework`, `DB_USER=root`, `DB_PASS=` vide, `DB_NAME=framework`
4. Créer la base `framework` dans phpMyAdmin.
5. Si une ancienne table `users` existe **sans** `password_hash` : supprimer la base et la recréer.
6. `C:\xampp\php\php.exe framework migrate`
7. [http://localhost/framework/](http://localhost/framework/) — inscription, puis [tâches](http://localhost/framework/todos)

## 2. Docker (optionnel)

MariaDB hôte **3307** (évite le conflit XAMPP 3306).

```bash
docker compose up -d --build
docker compose exec app php framework migrate
```

[http://localhost:8080](http://localhost:8080) — `APP_BASE_PATH` vide.

## 3. Serveur PHP intégré (optionnel)

`APP_BASE_PATH` vide, puis `php -S localhost:8000 -t public`.

## Commandes CLI

```text
php framework make:model Article --migration --controller --view
php framework make:model Article --controller --api
php framework make:controller Nom
php framework make:controller Nom --api
php framework make:dto Nom
php framework make:view articles/index
php framework make:migration create_posts_table
php framework make:middleware Auth
php framework migrate
```

Les routes **ne sont pas** générées : les ajouter dans `config/routes_web.php` et/ou `config/routes_api.php`.

## Migrations

Créer un fichier (SQL à éditer dans `database/migrations/`) :

```bash
php framework make:migration create_notes_table
```

Ou avec le modèle : `php framework make:model Note --migration`.

Appliquer les fichiers **en attente** (ceux pas encore dans la table `migrations`) :

```bash
php framework migrate
```

## Tests

`composer test` — les tests Feature (Todo) sont ignorés sans MySQL `framework_test`.

## API

`POST /api/register` et `POST /api/login` renvoient `{ user, token }`.  
Les todos : `Authorization: Bearer <token>`.
