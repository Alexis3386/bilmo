<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Produit;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 20; $i++) {
            $produit = new Produit();
            $produit->setNom('Produit' . ($i + 1));
            $produit->setPrix(10.00);
            $produit->setDescription('Découvrez notre dernier téléphone mobile : un design élégant, des performances exceptionnelles et une connectivité rapide pour vous garder toujours connecté.' . ($i + 1));
            $manager->persist($produit);
        }

        $listClient = [];

        for ($i = 0; $i < 5; $i++) {
            $client = new Client();
            $client->setAdresseMail("client{$i}@exemple.com");
            $client->setPassword($this->userPasswordHasher->hashPassword($client, 'password'));
            $manager->persist($client);

            $listClient[] = $client;
        }

        for ($i = 0; $i < 10; $i++) {
            $utilisateur = new Utilisateur();
            $utilisateur->setNom('nom' . ($i + 1));
            $utilisateur->setAdresseMail("utilisateur{$i}@exemple.com");
            $utilisateur->setClient($listClient[array_rand($listClient)]);

            $manager->persist($utilisateur);
        }
        $manager->flush();
    }
}
