<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        protected UserPasswordHasherInterface $hasher
    )
    {}

    public function load(ObjectManager $manager): void
    {
        $people = [
            [
                'fullname' => 'Jane Doe', 
                'email' => 'janedoe@gmail.com', 
                'password' => 'password',
                'roles' => ['ROLE_MODERATOR'],
                'image' => 'img-01.jpg',
            ],
            [
                'fullname' => 'John Doe', 
                'email' => 'johndoe@gmail.com', 
                'password' => 'password',
                'roles' => ['ROLE_ADMIN'],
                'image' => 'img-02.jpg',
            ],
        ];

        foreach ($people as $person) {
            $user = new User();
            $user->setFullname($person['fullname']);
            $user->setEmail($person['email']);
            $user->setPassword($this->hasher->hashPassword($user, $person['password']));
            $user->setRoles($person['roles']);
            $user->setImage($person['image']);
            $manager->persist($user);
        }

        $manager->flush();
    }
}
