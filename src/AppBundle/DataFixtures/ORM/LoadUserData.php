<?php

// src/AppBundle/DataFixtures/ORM/LoadUserData.php
/**
 * This class is used to add some users to the database.
 * While it is primitive, it's here to show that we can fill up the database if we wish.
 */

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\User;

class LoadUserData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        foreach (range(1, 10) as $number) {
            $userAdmin = new User();
            $userAdmin->setUsername("user_{$number}");
            $manager->persist($userAdmin);
            $manager->flush();
        }
    }
}
