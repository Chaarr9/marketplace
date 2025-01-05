<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Advert;
use App\Entity\Category;
use App\Enums\CategoryStatus;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class AdvertFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        protected EntityManagerInterface $entityManager
    )
    {}

    public function load(ObjectManager $manager): void
    {
        $category = new Category();
        $category->setName('Electronics');
        $category->setStatus(CategoryStatus::ACTIVE);
        $manager->persist($category);

        $ads = [
            ['title' => 'HP EliteBook x360', 'description' => 'HP EliteBook x360 is a convertible laptop with a 13.3-inch display.', 'price' => '450000', 'currency' => '£', 'country' => 'Nigeria', 'city' => 'Lagos'],
            ['title' => 'Snapdragon Gen 7', 'description' => 'Snapdragon Gen 7 is a high-end mobile processor.', 'price' => '150000', 'currency' => '£', 'country' => 'Nigeria', 'city' => 'Abuja'],
            ['title' => 'QASA Rechargeable Fan', 'description' => 'QASA Rechargeable Fan is a 16-inch fan with a remote control.', 'price' => '25000', 'currency' => '£', 'country' => 'Nigeria', 'city' => 'Port Harcourt'],
        ];

        $user = $this->getUser();

        foreach ($ads as $ad) {
            $advert = new Advert();
            $advert->setTitle($ad['title']);
            $advert->setDescription($ad['description']);
            $advert->setPrice($ad['price']);
            $advert->setCountry($ad['country']);
            $advert->setCity($ad['city']);
            $advert->setCurrency($ad['currency']);
            $advert->addCategory($category);
            $advert->setUser($user);
            $manager->persist($advert);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    protected function getUser(): ?User
    {
        return $this->entityManager->getRepository(User::class)->first();
    }
}
