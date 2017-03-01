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
        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav');
       //  $menu->setCurrent($this->container->get('request')->getRequestUri());

        $menu->addChild('<span class="glyphicon glyphicon-cutlery"></span>&nbsp;Kookboek', array('route' => 'recipes'));
        $menu->addChild('<span class="glyphicon glyphicon-list-alt"></span>&nbsp;Menu\'s', array('route' => 'menus'));
        $menu->addChild('<span class="glyphicon glyphicon-shopping-cart"></span>&nbsp;Boodschappenlijst', array('route' => 'boodschappen'));

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
}