Green Goodies
==========

Green goodies est un site ecommerce pour la vente de produit eco-responsable, le projet est composé du site et d'une API

## Versions Utilisées

Ce projet a été developper avec Symfony 7.3 sous php 8.4,
toutes versions antérieures ne garantissent pas le fonctionnement de l'application

Pour les besoins du projet un container docker est utilisé pour la base donnée ainsi que pour le serveur mail.
Il est paramétré dans `compose.yaml` et dans `compose.override.yaml`

Vous y trouverez une base Postgres SQL et un serveur mail de test.

## Installation

1 - Commencer par cloner le projet depuis le [gitHub](https://github.com/AD2210/Green-Googies) dans votre ide

2 - Executer la commande pour initialiser le projet symfony

```bash
composer install
```

3 - Copier le fichier _.env_ et renommer le en _.env.local_

4 - Démarrer docker et votre système de base de donnée

```bash
docker compose up -d --build
```

5 - Migrer le schéma de la base avec la commande

```bash
symfony console doctrine:schema:update --force
```

6 - Charger les Fixtures avec la commande

```bash
symfony console doctrine:fixtures:load 
```

7 - Initialiser les clés JWT

```bash
symfony console lexik:jwt:generate-keypair
```

## Usage

1 - Démarer votre serveur symfony

> [!NOTE]
> Si vous avez la version 5.12.0 de symfony CLI le `-d` ne fonctionne plus sur Windows

```bash
symfony serve -d
```

2.a - Acceder au site

`https://127.0.0.1:8000`

2.b - Acceder à la documentation de l'API et l'interface Swagger UI

 `https://127.0.0.1:8000/api/doc`

2.c - Acceder à la boite mail de test

`http://localhost:8025`

3 - Pour vous logger :

user : `exemple@greengoodies.com`

mot de passe : `password123`

## License

Ce projet est réalisé dans le cadre d'une formation OpenClassroom
