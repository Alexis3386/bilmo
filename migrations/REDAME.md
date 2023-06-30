# BileMo #


Projet OpenClassrooms : API pour l'entreprise BileMo

## Informations du projet ##
Projet de la formation ***Développeur d'application - PHP / Symfony***.

**Créez un web service exposant une API** - [Lien de la formation](https://openclassrooms.com/fr/paths/59-developpeur-dapplication-php-symfony)

## Badges du projet ##



## Descriptif du besoin ##

Il s'agit de fournir à toutes les plateformes qui le souhaitent l’accès au catalogue de mobile via une API (Application Programming Interface).

Après une réunion dense avec le client, il a été identifié un certain nombre d’informations. Il doit être possible de :

- consulter la liste des produits BileMo.
- consulter les détails d’un produit BileMo.
- consulter la liste des utilisateurs inscrits liés à un client sur le site web.
- consulter le détail d’un utilisateur inscrit lié à un client.
- ajouter un nouvel utilisateur lié à un client.
- supprimer un utilisateur ajouté par un client.

Seuls les clients référencés peuvent accéder aux API. Les clients de l’API doivent être authentifiés via OAuth ou JWT.

## Installation ##

1. Clonez le repo :

``` 
git clone htt ps://github.com/damienvalade/OC-P7-BILEMO.git 
```

2. Modifier le .env avec vos informations.

3. Installez les dependances :

``` 
composer install
```

4. Mettre en place la BDD :

``` 
php bin/console doctrine:database:create 
php bin/console make:migrate 
php bin/console doctrine:migrations:migrate 
```

5. Mettre en place les fixtures pour utiliser l'api :

``` 
php bin/console doctrine:fixtures:load
```

6. Creer un token :

``` 
requete : https://adresse.com/api/login_check
Body : {
         "username": "client email a trouver dans la bd",
         "password": "password"
       }
```

7. Faire des requêtes grace au token :

``` 
exemple : https://adresse.com/api/client/users
bearer : 
    mettre le token obtenue
```
