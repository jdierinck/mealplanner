<?php

namespace AppBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Entity\Gerecht;
use AppBundle\Entity\Keuken;
use AppBundle\Entity\Hoofdingredient;
use AppBundle\Entity\Tag;
use AppBundle\Entity\Afdeling;

class LoadReceptData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
	$names = [
	'Hoofdgerecht', 
	'Voorgerecht', 
	'Bijgerecht', 
	'Hapje', 
	'Ontbijt', 
	'Dessert', 
	'Lunch',
	'Soep',
	'Salade',
	];
	
	foreach ($names as $name) {
    	$gerecht = new Gerecht();
    	$gerecht->setName($name);
    	$manager->persist($gerecht);
	}		

	$names = [
	'Belgisch', 
	'Frans', 
	'Italiaans', 
	'Grieks',
	'Scandinavisch', 
	'Midden-oosten',
	'Mexicaans', 
	'Indisch', 
	'Vietnamees',
	'Japans',
	'Chinees',
	'Indonesisch',
	'Thais',
	'Maleisisch',
	'Ander',
	];
	
	foreach ($names as $name) {
    	$keuken = new Keuken();
    	$keuken->setName($name);
    	$manager->persist($keuken);
	}
	
	$names = [
	'Rundsvlees', 
	'Kip en gevogelte', 
	'Varkensvlees', 
	'Kaas',
	'Groenten (vegetarisch)', 
	'Eieren', 
	'Vis en zeevruchten', 
	'Lamsvlees',
	'Fruit',
	'Rijst',
	'Kalkoen',
	'Pasta',
	];
	
	foreach ($names as $name) {
    	$hooofdingredient = new Hoofdingredient();
    	$hooofdingredient->setName($name);
    	$manager->persist($hooofdingredient);
	}	
	
	$names = [
	'Goedkoop', 
	'Glutenvrij', 
	'Lactosevrij', 
	'Makkelijk',
	'Snel', 
	'Vegetarisch',
	'Paleo',
	'Diepvries',
	'Restjes',
	];
	
	foreach ($names as $name) {
    	$tag = new Tag();
    	$tag->setName($name);
    	$manager->persist($tag);
	}
	
	$names = [
	'vlees & gevogelte',
	'vis',
	'diepvries',
	'groenten',
	'charcuterie',
	'zuivel',
	'conserven',
	'dranken',
	'kruiden',
	'noten',
	'pasta & rijst',
	'brood',
	'varia',
	'oosterse specialiteiten',
	'niet toegewezen'
	];
	
	foreach ($names as $name) {
    	$afdeling = new Afdeling();
    	$afdeling->setName($name);
    	$manager->persist($afdeling);
	}
	
		

        $manager->flush();
    }
}