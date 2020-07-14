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
	// 'Hoofdgerecht', 
	// 'Voorgerecht', 
	// 'Bijgerecht', 
	// 'Hapje', 
	// 'Ontbijt', 
	// 'Dessert', 
	// 'Lunch',
	// 'Soep',
	// 'Salade',
		'Snack',
		'Drank',
		'Saus',
	];
	
	foreach ($names as $name) {
    	$gerecht = new Gerecht();
    	$gerecht->setName($name);
    	$manager->persist($gerecht);
	}		

	// $names = [
	// 'Belgisch', 
	// 'Frans',
	// 'Duits',
	// 'Oostenrijks',
	// 'Zwitsers',
	// 'Italiaans',
	// 'Spaans',
	// 'Grieks',
	// 'Turks',
	// 'Marokkaans',
	// 'Scandinavisch',
	// 'Brits', 
	// 'Midden-oosten',
	// 'Mexicaans', 
	// 'Indisch', 
	// 'Vietnamees',
	// 'Japans',
	// 'Koreaans',
	// 'Chinees',
	// 'Indonesisch',
	// 'Thais',
	// 'Maleisisch',
	// 'Ander',
	// ];
		$names = [
			['regio' => 'Zuid-Amerikaans', 'land' => 'Peruviaans'],
			['regio' => 'Zuid-Amerikaans', 'land' => 'Chileens'],
			['regio' => 'Zuid-Amerikaans', 'land' => 'Braziliaans'],
			['regio' => 'Zuid-Amerikaans', 'land' => 'Argentijns'],
		];
	
		foreach ($names as $name) {
			$keuken = new Keuken();
			$keuken->setRegio($name['regio']);
			$keuken->setName($name['land']);
    		$manager->persist($keuken);
		}
	// foreach ($names as $name) {
 //    	$keuken = new Keuken();
 //    	$keuken->setName($name);
 //    	$manager->persist($keuken);
	// }
	
	// $names = [
	// 'Rundsvlees', 
	// 'Kip en gevogelte', 
	// 'Varkensvlees', 
	// 'Kaas',
	// 'Groenten (vegetarisch)', 
	// 'Eieren', 
	// 'Vis en zeevruchten', 
	// 'Lamsvlees',
	// 'Fruit',
	// 'Rijst',
	// 'Kalkoen',
	// 'Pasta',
	// ];
	
	// foreach ($names as $name) {
 //    	$hooofdingredient = new Hoofdingredient();
 //    	$hooofdingredient->setName($name);
 //    	$manager->persist($hooofdingredient);
	// }	
	
	// $names = [
	// 'Goedkoop', 
	// 'Glutenvrij', 
	// 'Lactosevrij', 
	// 'Makkelijk',
	// 'Snel', 
	// 'Vegetarisch',
	// 'Paleo',
	// 'Diepvries',
	// 'Restjes',
	// ];
	
	// foreach ($names as $name) {
 //    	$tag = new Tag();
 //    	$tag->setName($name);
 //    	$manager->persist($tag);
	// }
	
	// $names = [
	// 'vlees & gevogelte',
	// 'vis',
	// 'diepvries',
	// 'groenten',
	// 'charcuterie',
	// 'zuivel',
	// 'conserven',
	// 'dranken',
	// 'kruiden',
	// 'noten',
	// 'pasta & rijst',
	// 'brood',
	// 'varia',
	// 'oosterse specialiteiten',
	// 'niet toegewezen'
	// ];
	
	// foreach ($names as $name) {
 //    	$afdeling = new Afdeling();
 //    	$afdeling->setName($name);
 //    	$manager->persist($afdeling);
	// }

        $manager->flush();
    }
}