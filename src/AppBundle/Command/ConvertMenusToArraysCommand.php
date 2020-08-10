<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class ConvertMenusToArraysCommand extends ContainerAwareCommand
{
    protected function configure()
    {
    	$this
        ->setName('app:convert-menus')
        ->setDescription('Converts existing menus to arrays.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $repo = $em->getRepository('AppBundle:Menu');

        $menus = $repo->findAll();

        foreach($menus as $menu) {
            $data = [
            	'days' => [],
            ];

            foreach ($menu->getDagen() as $i => $dag) {
                $recipeIds = [];
                foreach ($dag->getRecepten() as $recept) {
                    $recipeIds[] = $recept->getId();
                }
                $data['days'][$i]['slots'][3] = $recipeIds;
            }

            $menu->setMenuData($data);
            // $em->persist($menu);
        }
        $em->flush();

    	$output->writeln('All menus converted to arrays!');
    }
}