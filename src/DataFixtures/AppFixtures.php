<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        //création d'un user
        $user = new User();
        $user->setEmail('exemple@greengoodies.com');
        $user->setPassword($this->hasher->hashPassword($user,'password123'));
        $user->setRoles(['ROLE_USER']);
        $user->setFirstName('User');
        $user->setLastName('Googies');
        $manager->persist($user);

        //Création de plusieurs produits
        for ($i = 0; $i < 5; $i++) {
            $product = new Product();
            $product->setName('Product ' . $i);
            $product->setPrice($i + 1.5 * 5);
            $product->setPicture('https://picsum.photos/480?random=' . $i . '.webp');
            $product->setShortDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit.');
            $product->setLongDescription("Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse eleifend eros at mattis ultrices. Aenean sem libero, tristique sit amet libero nec, lobortis porttitor elit. Sed mollis mi vel nibh varius, non commodo leo faucibus.

            Praesent efficitur mollis facilisis. Proin at porttitor justo, at interdum purus. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. In volutpat mauris et semper iaculis. Nunc at augue at arcu dapibus tristique. Ut in pulvinar metus. Sed aliquam eu velit tempor hendrerit.

            Aliquam ut nisl odio. Nulla at sagittis ipsum. Vestibulum vel lobortis tortor, eget vehicula odio. Nulla lorem felis, rhoncus vitae rutrum non, consequat nec quam. Suspendisse semper erat quis diam feugiat dignissim.

            Cras semper neque id orci rutrum, vel faucibus arcu bibendum. Aenean ac feugiat orci. Curabitur suscipit dui rutrum sem ornare pharetra. Mauris quis magna at nisl euismod molestie nec at felis. Maecenas felis felis, sodales id pulvinar in, malesuada at nibh.");
            $manager->persist($product);
    }
        $manager->flush();
    }
}
