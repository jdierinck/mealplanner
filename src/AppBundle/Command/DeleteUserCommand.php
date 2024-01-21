<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Doctrine\Common\Cache\PhpFileCache;

class DeleteUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
    	$this
        ->setName('app:delete-user')
        ->setDescription('Delete a user and all associated content by email address')
        ->addArgument('email', InputArgument::REQUIRED, 'The email address of the user.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $repo = $em->getRepository('AppBundle:User');
        $email = $input->getArgument('email');

        $user = $repo->findOneByEmail($email);

        if (!$user) {
            $output->writeln("No user found with email {$email}");
            return;
        }

        $nr = count($user->getRecepten());
        $output->writeln("found {$nr} recepten for user {$user->getUsername()}");

        try {
            $cache = new PhpFileCache($this->getContainer()->getParameter('kernel.cache_dir') . '/mealplanner');

            $cacheKey = 'user'.$user->getId().'items_removed';
            if ($cache->contains($cacheKey)) {
                $cache->delete($cacheKey);
            }

            $cacheKey = 'user'.$user->getId().'extra_items';
            if ($cache->contains($cacheKey)) {
                $cache->delete($cacheKey);
            }

            $em->remove($user);
            $em->flush();
        }

        catch (\Exception $e) {
            $output->writeln($e->getMessage());
        }

    	$output->writeln('User successfully deleted');
    }
}