<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Recept;
use AppBundle\Entity\Ingredient;
use AppBundle\Form\ReceptType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
// use RecipeParser\RecipeParser;
use Symfony\Component\DomCrawler\Crawler;
use Goutte\Client;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Validator\Constraints\Url;
use RecipeParser\RecipeParser;
use RecipeParser\Text;

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
				'constraints' => new Url(),
			))
			// ->add('submit', SubmitType::class, array(
			// 		'label' => 'Importeer recept'
			// 	))
			->getForm();

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$url = $form->get('url')->getData();

			try {
				$recipe = $this->parse($url);

				$newRecipe = new Recept();

				$newRecipe->setTitel($recipe->title);

				$newRecipe->setPersonen($recipe->yield);

				$newRecipe->setBron($recipe->url);

				$photoName = urldecode(substr(strrchr($recipe->photo_url,'/'), 1));
				$photo = file_get_contents($recipe->photo_url);
				$path = $this->get('kernel')->getRootDir().'/../web/images/recepten/'.$photoName;
				file_put_contents($path, $photo);
				$newRecipe->setFotonaam($photoName);
				
				$orig_time = $recipe->time['total'];
				$time = '';
				if ($orig_time > 1440) {
					$time = '24:00:00';
				} else {
					$hours = intdiv($orig_time, 60);
					$hours = $hours < 10 ? '0'.$hours : $hours;
					$minutes = $orig_time - ($hours * 60);
					$minutes = $minutes < 10 ? '0'.$minutes : $minutes;
					$time = $hours.':'.$minutes.':00';				
				}
				$newRecipe->setBereidingstijd($time);

				$newRecipe->setUser($user);

				// foreach ($recipe->ingredients[0]['list'] as $i) {
				// 	$ingredient = new Ingredient();
				// 	$ingredient->setIngredient($i);
				// 	$newRecipe->addIngredienten($ingredient);
				// }
				foreach ($recipe->ingredients as $i) {
					foreach ($i['list'] as $ingr) {
						$ingredient = new Ingredient();
						$ingredient->setIngredient($ingr);
						$newRecipe->addIngredienten($ingredient);
					}
				}

				// Assign department to each ingredient
				foreach ($newRecipe->getIngredienten() as $i) {
					$finder = $this->container->get('app.dept_finder');
					$dept = $finder->findDept($i->getIngredient());
					$i->setAfdeling($dept);
				}

				$instructions = '';
				foreach ($recipe->instructions as $i) {
					$instructions .= $i['name'];
					foreach ($i['list'] as $instr) {
						$instructions .= '<p>'.$instr.'</p>';
					}
				}
				$newRecipe->setBereidingswijze($instructions);

				if (!null == $recipe->yield && preg_match('/^\d+$/', $recipe->yield)) {
					$newRecipe->setPersonen($recipe->yield);
				} else {
					$newRecipe->setPersonen(4);
				}

				$em->persist($newRecipe);
				$em->flush();

				$id = $newRecipe->getId();

				$this->addFlash(
	            	'notice',
	            	'Het recept <em>'.$newRecipe->getTitel().'</em> werd aangemaakt. '
	            	.'Klik <a href="'.$this->generateUrl('editform', array('id'=>$id)).'" id="editrecipe">hier</a> om te bewerken'
            	);

    	        return $this->redirectToRoute('recipes');

			} catch(\RecipeParser\NoMatchingParserException $e) {
				$this->addFlash('error', 'Deze website wordt (nog) niet ondersteund.');
				return $this->redirectToRoute('import');
			} catch(\GuzzleHttp\Exception\ConnectException $e){
				$this->addFlash('error', 'Deze site kon niet gevonden worden.');
				return $this->redirectToRoute('import');
			}
		}

		// $sites_nl = $this->getSupportedSites('nl');
		// $sites_en = $this->getSupportedSites('en');
		$sites_nl = RecipeParser::getSupportedSites('nl');
		$sites_en = RecipeParser::getSupportedSites('en');
		
		return $this->render('recipes/parse.html.twig', array(
			'form' => $form->createView(), 
			'data' => $data, 
			'sites_nl' => $sites_nl, 
			'sites_en' => $sites_en
			));

	}

	// public function getSupportedSites($lang) {
	// 	// $parsers_ini_file = $this->get('kernel')->getRootDir().'/../vendor/coogle/recipe-parser/lib/RecipeParser/Parser/parsers.ini';

	// 	$sites = parse_ini_file($parsers_ini_file, true);

	// 	ksort($sites);

	// 	foreach ($sites as $key => $value) {
	// 		if ($value['lang'] === $lang) { $results[$key] = $value; }
	// 	}

	// 	return $results;

	// }
	

	public function parse($url)
	{
		$client = new Client();

		$crawler = $client->request('GET', $url);

		$html = '';
		foreach ($crawler as $domElement) {
			$html .= $domElement->ownerDocument->saveHTML($domElement);
		}

		$doc = Text::getDomDocument($html);

		$recipe = RecipeParser::parse($doc, $url);

		return $recipe;

	}

	/**
	 * Test Import a recipe found on the web with coogle\RecipeParser
	 *
	 * @Route("/testimport", name="testimport")
	 */
	public function testImportAction(Request $request)
	{
		$client = new Client();

		$url = 'http://rasamalaysia.com/butter-prawns/2';

		$crawler = $client->request('GET', $url);

		$html = '';
		foreach ($crawler as $domElement) {
			$html .= $domElement->ownerDocument->saveHTML($domElement);
		}
		dump($html);

		$doc = Text::getDomDocument($html);

		$recipe = RecipeParser::parse($doc, $url);

		return new Response(dump($recipe));

	}

	/**
	 * Test PHP code
	 *
	 * @Route("/testphp", name="testphp")
	 */
	public function testPHPAction(Request $request)
	{
		// $result = stripos('1 avocado' ,'avocado');
		// $a = 'kerstomaten';
		// $result = preg_match('/'.$a.'/', '');
		// $arr = array("key" => "value");
		// $result = reset($arr);

		$var ="javascript:(function()%7Bf='http://www.delicious.com/save?url='+encodeURIComponent(window.location.href)+'&title='+encodeURIComponent(document.title)+'&notes='+encodeURIComponent(''+(window.getSelection?window.getSelection():document.getSelection?document.getSelection():document.selection.createRange().text))+'&v=6&';a=function()%7Bif(!window.open(f+'noui=1&jump=doclose','deliciousuiv6','location=yes,links=no,scrollbars=no,toolbar=no,width=550,height=550'))location.href=f+'jump=yes'%7D;if(/Firefox/.test(navigator.userAgent))%7BsetTimeout(a,0)%7Delse%7Ba()%7D%7D)()";
		$result = rawurldecode($var);

		return new Response(dump($result));

	}



}