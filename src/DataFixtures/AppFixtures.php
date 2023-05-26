<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Produit;
use App\Entity\Utilisateur;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private UserPasswordHasherInterface $userPasswordHasher;
    private Generator $faker;
    private array $productName;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->faker = Factory::create();
        $this->faker->seed(2456);
        $this->productName = ['Guild Smoker', 'Golden Tech', 'Real Wolf', 'Step Whale', 'Appeal Wonder',
            'Driftwood Circle', 'Fat Mite', 'Buzz Beetle', 'Turbo Fly', 'Driftwood'];
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 20; $i++) {
            $produit = new Produit();
            $produit->setNom($this->faker->randomElement($this->productName));
            $produit->setPrix($this->faker->randomFloat(2));
            $produit->setDescription($this->faker->sentence($this->faker->numberBetween(4, 20)));
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
            $utilisateur->setNom($this->faker->name());
            $utilisateur->setAdresseMail("utilisateur{$i}@exemple.com");
            $utilisateur->setClient($listClient[array_rand($listClient)]);

            $manager->persist($utilisateur);
        }
        $manager->flush();
    }
}
