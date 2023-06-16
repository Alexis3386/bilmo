<?php

namespace App\DataFixtures;

use App\Entity\Client;
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

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
        $this->faker = Factory::create();
        $this->faker->seed(2456);
    }

    public function load(ObjectManager $manager): void
    {

        for ($i = 0; $i < 5; $i++) {
            $client = new Client();
            $client->setRoles(["ROLE_ADMIN"]);
            $client->setAdresseMail("client{$i}@exemple.com");
            $client->setPassword($this->userPasswordHasher->hashPassword($client, 'password'));
            $manager->persist($client);

            for ($j = 0; $j < $this->faker->numberBetween(0, 6); $j++) {
                $utilisateur = new Utilisateur();
                $utilisateur->setNom($this->faker->name());
                $utilisateur->setAdresseMail($this->faker->email());
                $utilisateur->setClient($client);
                $manager->persist($utilisateur);
            }
        }

        $manager->flush();
    }
}
