<?php

namespace App\DataFixtures;

use App\Entity\Advice;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class AdviceFixtures extends Fixture implements FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        $advices = [
            [
                'content' => 'Protect your plants from frost by covering them with a winter fleece.',
                'months' => [1, 2, 12]
            ],
            [
                'content' => 'Prune your roses before spring to encourage beautiful blooms.',
                'months' => [2, 3]
            ],
            [
                'content' => 'Sow your tomatoes indoors to transplant them in spring.',
                'months' => [2, 3]
            ],
            [
                'content' => 'Prepare your summer vegetable seedlings: zucchini, eggplants, peppers.',
                'months' => [3, 4]
            ],
            [
                'content' => 'Plant your potatoes when the lilacs bloom.',
                'months' => [4]
            ],
            [
                'content' => 'Water your plants regularly, preferably in the morning or evening.',
                'months' => [5, 6, 7, 8]
            ],
            [
                'content' => 'Mulch the soil to retain moisture and limit weeds.',
                'months' => [5, 6, 7, 8, 9]
            ],
            [
                'content' => 'Harvest your green beans regularly to stimulate production.',
                'months' => [6, 7, 8, 9]
            ],
            [
                'content' => 'Plant your strawberries for a harvest next year.',
                'months' => [9, 10]
            ],
            [
                'content' => 'Collect fallen leaves to make compost.',
                'months' => [10, 11]
            ],
            [
                'content' => 'Plant spring bulbs: tulips, daffodils, crocuses.',
                'months' => [10, 11]
            ],
            [
                'content' => 'Clean and store your garden tools before winter.',
                'months' => [11, 12]
            ],
            [
                'content' => 'Consider installing birdhouses and feeders for birds.',
                'months' => [11, 12, 1, 2]
            ],
            [
                'content' => 'Plan your vegetable garden for next year during the winter period.',
                'months' => [12, 1, 2]
            ],
            [
                'content' => 'Have your soil analyzed to know its amendment needs.',
                'months' => [1, 2, 3, 10, 11, 12]
            ]
        ];

        foreach ($advices as $adviceData) {
            $advice = new Advice();
            $advice->setContent($adviceData['content']);
            $advice->setMonths($adviceData['months']);
            $manager->persist($advice);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['advices', 'all'];
    }
}
