<?php

namespace AppBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Builder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $em = $this->container->get('doctrine.orm.entity_manager');
        $user = $this->container->get('security.token_storage')->getToken()->getUser();
        $bl = $user->getBoodschappenlijst();
        $items = '';
        if ($bl) {
            $itemcount = count($bl->getIngrbl());
            $items = $itemcount > 0 ? $itemcount : '';
        }

        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav');

        $menu->addChild('<span class="glyphicon glyphicon-cutlery"></span>&nbsp;Kookboek', array('route' => 'recipes'));
        $menu->addChild('<span class="glyphicon glyphicon-list-alt"></span>&nbsp;Menu\'s', array('route' => 'menus'));
        $menu->addChild('<span class="glyphicon glyphicon-shopping-cart"></span>&nbsp;Boodschappenlijst&nbsp;<span class="badge">'.$items.'</span>', array('route' => 'boodschappen'));

        return $menu;
    }
    
    public function userMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav navbar-right');

        $menu->addChild('Mijn account')->setAttribute('dropdown', true);
        $menu['Mijn account']->addChild('<span class="glyphicon glyphicon-user"></span>&nbsp;Profiel', array('route' => 'profiel'))->setAttribute('divider_append', true);
        $menu['Mijn account']->addChild('<span class="glyphicon glyphicon-log-out"></span>&nbsp;Uitloggen', array('route' => 'logout'));

        return $menu;
    }
    
    public function homepageMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav navbar-right');
        $menu->addChild('<span class="glyphicon glyphicon-log-in"></span>&nbsp;Log in', array('route' => 'login'));
        
        return $menu;
    }

    public function footerMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'footermenu');
        $menu->addChild('<i class="fa fa-user" aria-hidden="true"></i>&nbsp;Over Mealplanner', array('route' => 'about'))
            // Note: the following attributes are not needed since we activate the modal using Javascript
            // ->setLinkAttribute('data-toggle','modal')
            // ->setLinkAttribute('data-target','#footerModal')
            ;
        $menu->addChild('<i class="fa fa-envelope" aria-hidden="true"></i>&nbsp;Contact', array('route' => 'contact'))
            // ->setLinkAttribute('data-toggle','modal')
            // ->setLinkAttribute('data-target','#footerModal')
            ;
        $menu->addChild('<i class="fa fa-life-ring" aria-hidden="true"></i>&nbsp;Support', array('route' => 'support'));

        $menu->addChild('<i class="fa fa-bullhorn" aria-hidden="true"></i>&nbsp;Wat is nieuw', array('route' => 'whatsnew'));
        
        return $menu;
    }

}