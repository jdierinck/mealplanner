<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class GenerateUserTokensCommand extends ContainerAwareCommand
{
    protected function configure()
    {
    	$this
        ->setName('app:generate-user-tokens')
        ->setDescription('Generate a token for existing users')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $repo = $em->getRepository('AppBundle:User');

        $users = $repo->findAll();

        foreach($users as $user) {
            $user->setToken();
        }
        $em->flush();

    	$output->writeln('Generated tokens!');
    }
}