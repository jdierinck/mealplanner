<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\YieldType;


class LoadYieldType implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $types = [
            'persoon' => 'personen',
            'kopje' => 'kopjes',
            'stuk' => 'stuks',
            'portie' => 'porties',
            'pot' => 'potten',
        ];

        foreach ($types as $singular => $plural) {
            $yieldType = new YieldType();
            $yieldType->setUnitSingular($singular);
            $yieldType->setUnitPlural($plural);
            $manager->persist($yieldType);
        }

        $manager->flush();
    }
}