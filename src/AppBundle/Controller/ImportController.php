<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Recept;
use AppBundle\Entity\Ingredient;
use AppBundle\Form\ReceptType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Url;
use AppBundle\RecipeParser\RecipeParser;
use AppBundle\RecipeParser\Text;
use AppBundle\RecipeParser\NoRecipeDataException;
use Symfony\Component\Yaml\Yaml;

class ImportController extends Controller
{

	/**
	 * Import a recipe found on the web
	 *
	 * @Route("/import", name="import")
	 */
	public function importAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$user = $this->getUser();

		$data = array();
		$form = $this->createFormBuilder($data)
			->add('url', TextType::class, array(
				// 'constraints' => new Url(),
			))
			// ->add('submit', SubmitType::class, array(
			// 		'label' => 'Importeer recept'
			// 	))
			->getForm();

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$url = $form->get('url')->getData();

			try {
				$recipe = RecipeParser::parse($url);
			}
			catch (NoRecipeDataException $e) {
				$this->addFlash('error', 'No recipe data found');
				return $this->redirectToRoute('import');
			}

			$newRecipe = $this->fromSchema($recipe);

			$em->persist($newRecipe);
			$em->flush();

			$id = $newRecipe->getId();

			$this->addFlash(
            	'notice',
            	'Het recept <em>'.$newRecipe->getTitel().'</em> werd aangemaakt. '
            	.'Klik <a href="'.$this->generateUrl('editform', array('id'=>$id)).'" id="editrecipe" class="editrecipe">hier</a> om te bewerken.'
        	);

	        return $this->redirectToRoute('recipes');
		}

		$appPath = $this->container->getParameter('kernel.root_dir');
		$sites_nl = Yaml::parse(file_get_contents($appPath . '/../src/AppBundle/Resources/sites_nl.yml'));
		$sites_en = Yaml::parse(file_get_contents($appPath . '/../src/AppBundle/Resources/sites_en.yml'));
		
		return $this->render('recipes/parse.html.twig', array(
			'form' => $form->createView(), 
			'data' => $data, 
			'sites_nl' => $sites_nl, 
			'sites_en' => $sites_en
			));
	}

	/**
	 * Test Import a recipe found on the web
	 *
	 * @Route("/testimport", name="testimport")
	 */
	public function testImportAction(Request $request)
	{
		// $url = 'https://www.24kitchen.nl/recepten/poke-bowl-met-zalm';
		// $url = 'http://www.budgetkoken.be/pastagerechten/pasta-met-broccoli-en-champignons.php'; // no data
		// $url = 'https://www.colruyt.be/nl/lekker-koken/tartiflette-met-abdijkaas-en-spekjes'; // not found, blokkeert bots
		// $url = 'https://dagelijksekost.een.be/gerechten/cannelloni-met-gerookte-zalm-asperges-en-spinazie';
		// $url = 'https://www.foody.nl/recepten/salade-met-groene-asperges-burrata-en-nectarines-44973.html'; //md
		// $url = 'https://www.foodandfriends.nl/recepten/whisky-can-chicken.html'; // md, no instructions
		// $url = 'https://www.foodandfriends.nl/recepten/chili-aubergine-dip.html'; // no instructions
		// $url = 'https://www.kookjij.nl/recepten/varkensrollade-teriyaki-met-oosterse-noodles-slowcooker'; // md
		// $url = 'https://njam.tv/recepten/gehaktballen-met-tomatensaus';
		// $url = 'https://www.okokorecepten.nl/recept/chefs/gordon-ramsay/gordon-ramsay-groene-asperges-sinaasappelsaus';
		// $url = 'https://www.smulweb.nl/recepten/1057668/Couscoussalade';
		// $url = 'https://koken.vtm.be/loic-zot-van-koken/recept/gyozas-met-ponzu-saus-en-chili-olie'; // not working???
		// $url = 'https://koken.vtm.be/recept/ovenschotel-met-prei-aardappelpuree-en-kabeljauw';
		// $url = 'https://www.ah.nl/allerhande/recept/R-R444872/tortelloni-met-spinazie-uit-de-oven';
		// $url = 'https://www.lekkervanbijons.be/recepten/ovenschotel-met-vis-preipuree-kaassaus-en-gekookte-eieren';
		// $url = 'https://www.libelle-lekker.be/bekijk-recept/13947/ovenschotel-met-vis';
		// $url = 'https://www.leukerecepten.nl/recepten/ovenschotel-met-vis-en-puree/'; // no ingredients
		// $url ='https://kookidee.nl/recepten/hoofdgerecht/kabeljauw-en-groenteschotel-uit-de-oven-met-aardappel-wedges/';

		// $url = 'https://www.101cookbooks.com/rice-noodle-stir-fry-recipe/';
		// $url = 'https://12tomatoes.com/buttered-steak-and-mushroom-bites/';
		// $url = 'https://www.allrecipes.com/recipe/268843/sheet-pan-shrimp-and-sausage-bake/?internalSource=popular&referringContentType=Homepage&clickId=cardslot%204';
		// $url = 'https://www.bbcgoodfood.com/recipes/roast-chicken-peppers-feta'; // md
		// $url = 'https://www.bhg.com/recipe/noodles-and-zoodles-with-saffron-and-cream/'; // 2 arrays in json-ld
		// $url = 'https://www.bigoven.com/recipe/sun-dried-tomato-spinach-and-cheese-stuffed-chicken-breast/1683351';
		// $url = 'https://www.bonappetit.com/recipe/bas-best-chicken-parm';
		// $url = 'https://www.chowhound.com/recipes/easy-bbq-baby-back-pork-ribs-30741'; // ItemList in instructions
		// $url = 'https://www.cooks.com/rec/story/179/'; // no data
		// $url = 'http://www.eatingwell.com/recipe/280315/garlic-parmesan-melting-potatoes/'; // 2 arrays in json-ld
		// $url = 'https://elanaspantry.com/keto-huevos-rancheros-skillet-casserole/';
		// $url = 'https://www.epicurious.com/recipes/food/views/spicy-salmon-teriyaki-with-steamed-bok-choy';
		// $url = 'https://www.feastingathome.com/zhoug-recipe/';
		// $url = 'https://foodnetwork.co.uk/recipes/summer-aubergine-lasagne/'; // no data
		// $url = 'https://www.foodandwine.com/recipes/smoky-paprika-chicken-and-crispy-chickpeas-over-escarole-salad'; // 2 arrays in json-ld
		// $url = 'https://www.food.com/recipe/chickpea-salad-with-cumin-vinaigrette-220871';
		// $url = 'https://food52.com/recipes/83163-crispy-chicken-cutlets-lemon-avocado-relish'; // instructions are one array down
		// $url = 'https://www.jamieoliver.com/recipes/chicken-recipes/seared-turmeric-chicken/';
		// $url = 'https://www.kingarthurflour.com/recipes/classic-sourdough-waffles-or-pancakes-recipe';
		// $url = 'https://www.marthastewart.com/1546602/tuna-salad-hand-rolls'; // 2 arrays in json-ld
		// $url = 'https://www.myrecipes.com/recipe/malaysian-fried-rice'; // 2 arrays in json-ld
		// $url = 'https://rasamalaysia.com/three-cups-chicken-recipe/';
		// $url = 'https://rasamalaysia.com/lobster-bisque/';
		// $url = 'https://www.realsimple.com/food-recipes/browse-all-recipes/chicken-radish-recipe'; // 2 arrays in json-ld
		// $url = 'https://www.saveur.com/article/Recipes/Chicken-Tikka-Masala/'; // RDFa
		// $url = 'https://www.seriouseats.com/recipes/2020/05/chicken-and-dried-fig-tagine-with-pistachios-and-chickpeas.html';
		// $url = 'https://www.simplyrecipes.com/recipes/jicama_avocado_and_orange_salad/';
		// $url = 'https://www.skinnytaste.com/carne-guisada-latin-beef-stew/'; // HowToSections
		// $url = 'https://recipes.sparkpeople.com/recipe-detail.asp?recipe=419550'; // md
		// $url = 'https://www.tasteofhome.com/recipes/spaghetti-meatballs-and-grana-padano-cheese/';
		// $url = 'https://www.thedailymeal.com/best-recipes/grilled-chicken-peach-skewers';
		// $url = 'https://www.thekitchn.com/recipe-greek-style-tuna-salad-243282';// access denied
		// $url = 'https://zekergezond.be/detail/5e3bf998-2db2-489e-9d49-4e61f4b0126f/gemarineerde-temp%C3%A9-met-spitskool';
		$url = 'https://zekergezond.be/detail/37545421-8943-4199-8af7-d1293c0f82c0/gevulde-paprika\'s';
		// $url = 'https://zekergezond.be/detail/29100caf-543d-45cb-a521-d16d9f13a397/marokkaanse-tajine-van-kikkererwten';

		try {
			$recipe = RecipeParser::parse($url);
			dump($recipe);
		}
		catch (NoRecipeDataException $e) {
			$this->addFlash('error', 'No recipe data found');
			return $this->redirectToRoute('import');
		}

		$newRecipe = $this->fromSchema($recipe);

		return new Response(dump($newRecipe));

	}

	public function fromSchema($recipe, $format = 'array') {

		$newRecipe = new Recept();

		// Titel
		$newRecipe->setTitel(Text::formatLine($recipe['name']));

		// Yield
		if (!empty($recipe['recipeYield'])) {
			if (is_array($recipe['recipeYield'])) {
				$yield = Text::getYield($recipe['recipeYield'][0]);
			} else {
				$yield = Text::getYield($recipe['recipeYield']);
			}
			
		} else {
			$yield = 4;
		}
		$newRecipe->setYield($yield);
		// default to 'persons'
    	$type = $this->getDoctrine()->getRepository('AppBundle:YieldType')->findOneByUnitPlural('personen');
		$newRecipe->setYieldType($type);

		// Bron
		$newRecipe->setBron($recipe['url']);

		// Foto
		if (isset($recipe['image'])) {
			if (is_array($recipe['image'])) {
				if (isset($recipe['image']['@type'])) {
					$photoName = $recipe['image']['url'];
				} elseif (!empty($recipe['image'][0])) {
					// @todo: pick the image with the best resolution
					$photoName = $recipe['image'][0];
				}
			} else {
				$photoName = $recipe['image'];
			}
			// fix for missing http(s) (BBC Good Food)
			if (preg_match('/^\/\/www\./', $photoName)) $photoName = 'https:' . $photoName;
			$newRecipe->setFotonaam($photoName);
		}

		// Bereidingstijd
		if (isset($recipe['totalTime'])) {
			$a = new \DateTime();
			$b = clone $a;
			$b->add(new \DateInterval($recipe['totalTime']));
			// $int = new \DateInterval($recipe['totalTime']);
			$diff = $b->diff($a);
			$duration = $diff->format('%H:%I:%S');
			$newRecipe->setBereidingstijd($duration);
		}
		elseif (isset($recipe['prepTime']) && isset($recipe['cookTime'])) {
			// add prepTime and cookTime
			$prep = new \DateInterval($recipe['prepTime']);
			$cook = new \DateInterval($recipe['cookTime']);
			$e = new \DateTime('00:00');
			$f = clone $e;
			$e->add($prep);
			$e->add($cook);
			$duration = $f->diff($e)->format("%H:%I:%S");
			$newRecipe->setBereidingstijd($duration);
		}

		// User
		$newRecipe->setUser($this->getUser());

		// Ingredients
		$ingredients = NULL;
		if (!empty($recipe['recipeIngredient']) && empty($recipe['ingredients'])) $ingredients = $recipe['recipeIngredient'];

		if (empty($recipe['recipeIngredient']) && !empty($recipe['ingredients'])) $ingredients = $recipe['ingredients'];

		if (!empty($recipe['recipeIngredient']) && !empty($recipe['ingredients'])) {
			if (is_array($recipe['recipeIngredient']) && is_array($recipe['ingredients'])) {
				if (count($recipe['ingredients']) > count($recipe['recipeIngredient'])) $ingredients = $recipe['ingredients'];
				else {
					$ingredients = $recipe['recipeIngredient'];
				}
			}
			else {
				$ingredients = $recipe['recipeIngredient'];
			}
		}

		if (!empty($ingredients)) {
			if (is_array($ingredients)) {
				foreach ($ingredients as $ingr) {
					list($q, $u, $i) = $this->qui(Text::formatLine($ingr));
					$ingredient = new Ingredient();
					$ingredient->setHoeveelheid($q);
					$ingredient->setEenheid($u);
					$ingredient->setIngredient($i);
					$ingredient->setSection(false);
					$newRecipe->addIngredienten($ingredient);
				}
			}
			else {
				// @todo: try to convert string to array of ingredients
				// split on \n or \r\n or - or , ???
			}
		}

		// Assign department to each ingredient (except for sections)
		foreach ($newRecipe->getIngredienten() as $i) {
			if (false === $i->isSection()) {
				$finder = $this->container->get('app.dept_finder');
				$dept = $finder->findDept($i->getIngredient());
				$i->setAfdeling($dept);
			}
		}

		// Instructions
		// @todo: add support for HowToSection
		// @todo: add support for @type ItemList?
		if (!empty($recipe['recipeInstructions'])) {
			$instructions = '';
			if (is_array($recipe['recipeInstructions'])) {
				foreach ($recipe['recipeInstructions'] as $i) {
					if (is_array($i) && isset($i['text'])) {
						$instructions .= '<p>' . Text::formatLine($i['text']) . '</p>';
					} elseif (is_string($i)) {
						$instructions .= '<p>' . Text::formatLine($i) . '</p>';
					}
				}
			} elseif (is_string($recipe['recipeInstructions'])) {
				$instructions = Text::formatParagraph($recipe['recipeInstructions']);
				$instructions = '<p>' . $instructions . '</p>';
			}
			$newRecipe->setBereidingswijze($instructions);
		} elseif (!empty($recipe['description'])) {
			// chances are the instructions are in the description
			$newRecipe->setBereidingswijze(Text::formatParagraph($recipe['description']));
		}

		// Gerecht (category)
		if (!empty($recipe['recipeCategory'])) {
			if (is_array($recipe['recipeCategory'])) {
				$cat = $recipe['recipeCategory'][0]; 
			} elseif (is_string($recipe['recipeCategory'])) {
				$cat = $recipe['recipeCategory'];
			}
			$category = $this->getDoctrine()->getRepository('AppBundle:Gerecht')->findOneByName($cat);
			$newRecipe->setGerecht($category);
		}

		// Keuken (cuisine)
		if (!empty($recipe['recipeCuisine'])) {
			if (is_array($recipe['recipeCuisine'])) {
				$cuis = $recipe['recipeCuisine'][0]; 
			} elseif (is_string($recipe['recipeCuisine'])) {
				$cuis = $recipe['recipeCuisine'];
			}
			$cuisine = $this->getDoctrine()->getRepository('AppBundle:Keuken')->findOneByName($cuis);
			$newRecipe->setKeuken($cuisine);
		}

		// Rating
		if (isset($recipe['aggregateRating'])) {
			if (!empty($recipe['aggregateRating']['ratingValue'])) {
				$rating = intval($recipe['aggregateRating']['ratingValue']);
			} elseif (!empty($recipe['ratingValue'])){
				$rating = intval($recipe['ratingValue']);
			} else {
				$rating = NULL;
			}
			$newRecipe->setRating($rating);
		}

		// Keywords
		if (isset($recipe['keywords'])){
			// string (comma separated)
			// array
			// foreach (.. as $x) { $newRecipe->addTag($x); }
		}

		// author
			// string (single item)
			// array (@type = author)

		return $newRecipe;
	}

	/**
	 * Separate quantities, units and ingredients 
	 * qui stands for quantity - unit - ingredient
	 */
	public function qui($string) {
		$q = $u = NULL;

		// replace common vulgar fractions with decimals
		$string = preg_replace(['/\b¼\b/u', '/\b½\b/u', '/\b¾\b/u', '/\b⅓\b/u', '/\b⅔\b/u', '/\b⅛\b/u'], ['0.25', '0.5', '0.75', '0.3', '0.6', '0.125'], $string);
		// if a digit immediately precedes the fraction, omit 0
		$string = preg_replace(['/\b(\d+)¼\b/u', '/\b(\d+)½\b/u', '/\b(\d+)¾\b/u', '/\b(\d+)⅓\b/u', '/\b(\d+)⅔\b/u', '/\b(\d+)⅛\b/u'], ['$1.25', '$1.5', '$1.75', '$1.3', '$1.6', '$1.125'], $string);

		$i = $string;

		// preg_match('/[\x{00BC}-\x{00BE}\x{2150}-\x{215E}]/u', $string, $matches);dump($matches);
		// preg_match('/[¼½¾⅐⅑⅒⅓⅔⅕⅖⅗⅘⅙⅚⅛⅜⅝⅞]/u', $string, $matches);dump($matches);

		// Quantities
		// first look for whole or decimal quantity
		preg_match('/^(?P<dec>\d+((\.|,)\d+)?)\s\D+/', $string, $matches);
		if (!empty($matches['dec'])) {
			$q = $matches['dec'];
			$i = substr($i, strlen($matches['dec']) + 1); // remove quantity from string
		} else { // then look for fractions
			preg_match('/^(?P<frac>(\d+)?\s?(\d\/\d))\s\D+/', $string, $matches);
			if (!empty($matches['frac'])) {
				$q = Text::fractionToDecimal($matches['frac']);
				$i = substr($i, strlen($matches['frac']) + 1); // remove quantity from string
			}
		}

		// Units
		$appPath = $this->container->getParameter('kernel.root_dir');
		$units_nl = Yaml::parse(file_get_contents($appPath . '/../src/AppBundle/Resources/units_nl.yml'));
		$units_en = Yaml::parse(file_get_contents($appPath . '/../src/AppBundle/Resources/units_en.yml'));
		$units = array_merge($units_nl, $units_en);

		$words = explode(' ', $i);

		if (in_array($words[0], $units)) {
			$u = $words[0];
			$i = substr($i, strlen($u) + 1); // remove unit from string
		}

		return array($q, $u, $i);
	}

}