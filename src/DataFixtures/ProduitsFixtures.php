<?php

namespace App\DataFixtures;

use App\Entity\Produit;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class ProduitsFixtures extends Fixture
{

    private Generator $faker;

    public function __construct()
    {
        $this->faker = Factory::create();
        $this->faker->seed(3256);
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 20; $i++) {
            $produit = new Produit();
            $produit->setNom($this->faker->word());
            $produit->setPrix($this->faker->randomFloat(2));
            $produit->setDescription($this->faker->sentence($this->faker->numberBetween(4, 20)));
            $manager->persist($produit);
        }

        $manager->flush();
    }
}
