<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Doctrine\Common\Cache\PhpFileCache;
use AppBundle\Entity\Ingredient;
use AppBundle\Form\Type\IngredientType;


class ShoppingListController extends Controller
{  
    /**
     * @Route("shopping-list", name="shoppingList")
     */
    public function shoppingListAction(Request $request, $toPDF = FALSE, $toCSV = FALSE)
    {
		if(!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
			throw $this->createAccessDeniedException();
		}
		
    	$em = $this->getDoctrine()->getManager();
			
		$user = $this->getUser();
		$mealplan = $user->getMealplan();

		// removed items
		$cacheDir = $this->container->getParameter('kernel.cache_dir');
		$cache = new PhpFileCache($cacheDir . '/mealplanner');
		$itemsRemoved = $cache->fetch('user'.$user->getId().'items_removed');

		// extra items
		$cacheKey = 'user'.$user->getId().'extra_items';
		$extra_items = !empty($cache->fetch($cacheKey)) ? $cache->fetch($cacheKey): [];
		foreach ($extra_items as $key => $item) {
			$extra_items[$key] = unserialize($item);
		}

		$results = $events = [];
		foreach ($mealplan->getEvents() as $event) {
			if ($event->getDate() >= new \DateTime('today')) {
				$events[] = $event;
				$servings = $event->getServings(); // eg [565 => 4, 89 => 2, 909 => 3]
				foreach ($event->getRecepten() as $recipe) {
					foreach ($recipe->getIngredienten() as $ingredient) {
						if (isset($itemsRemoved[$event->getId()]) && in_array($ingredient->getId(), $itemsRemoved[$event->getId()])) {
							continue;
						}
						// set new servings property on each ingredient
						$ingr = clone $ingredient;
						$ingr->servings = $servings[$ingr->getRecept()->getId()];
						$ingr->event = $event->getId();
						$results[] = $ingr;
					}
				}
			}
		}
		$results = array_merge($results, $extra_items);
		// sort ingredients alpabetically
		usort($results, array($this, 'cmp_ingr'));

		// fill array keyed by department
		$items = [];
		foreach ($results as $result) {
			if (!$result->isSection()) {
				$items[$result->getAfdeling()->getName()][] = $result;
			}
		}

		// Get all departments, ordered
		$qb = $em->createQueryBuilder();
		$query = $qb->select('ao','a')
	        ->from('AppBundle:AfdelingOrdered','ao')
	        ->join('ao.afdeling', 'a')
	        ->where('ao.user = :user')
	        ->orderBy('ao.positie')             
	        ->setParameter('user', $user)
	        ->getQuery();
        $departments = $query->getResult();

		// sort items according to afdelingordered
        $orderby = [];
        foreach ($departments as $department) {
        	$orderby[] = $department->getAfdeling()->getName();
        }
        $items = array_merge(array_flip($orderby), $items); // keys in 2nd array overwrite those of 1st

        if ($toCSV === TRUE) {
        	return $items;
        }

        // find items to be merged
        $duplicates = [];
        foreach ($items as $dept => &$ingredients) {
        	if (is_array($ingredients)){
        	foreach ($ingredients as $key => &$i) {
        		if (isset($ingredients[$key+1])) {
        		if (($i->getEenheid() == $ingredients[$key+1]->getEenheid()) && ($i->getIngredient() == $ingredients[$key+1]->getIngredient())){
        			// $i->mergeWith[] = $ingredients[$key+1]->getId();
        			$id = preg_replace('/[^\w]/i', '', $i->getEenheid() . $i->getIngredient());
        			$duplicates[$dept][$id][] = $i->getId();
        			$duplicates[$dept][$id][] = $ingredients[$key+1]->getId();
        			// $duplicates[$dept][$id] = array_unique($duplicates[$dept][$id]);
        			$i->mergeid = $id;
        			$ingredients[$key+1]->mergeid = $id;
        		}
        		}
        	}
        	}
        }

		// Clean up cached removed items if necessary
		if (!empty($itemsRemoved)) {
			$eventIds = [];
			foreach ($events as $event) {
				$eventIds[] = $event->getId();
			}
			foreach ($itemsRemoved as $key => $removed) {
				// if event is no longer on shopping list delete all its removed items
				if (!in_array($key, $eventIds)) {
					unset($itemsRemoved[$key]);
				}
			}
			$cache->save('user'.$user->getId().'items_removed', $itemsRemoved);
		}

		// sort events by date
		usort($events, array($this, 'cmp_events'));

        return $this->render('shopping/shoppinglist.html.twig', array(
        	'items' => $items,
        	'toPDF' => $toPDF,
        	'events' => $events,
        ));
 
    }

    static function cmp_ingr($a, $b)
    {
        $al = strtolower($a->getIngredient());
        $bl = strtolower($b->getIngredient());
        if ($al == $bl) {
        	return $a->getHoeveelheid() > $b->getHoeveelheid() ? +1 : -1;
            // return 0;
        }
        return ($al > $bl) ? +1 : -1;
    }

    static function cmp_events($a, $b)
    {
        $al = $a->getDate(); $ats = $a->getTimeSlot();
        $bl = $b->getDate(); $bts = $b->getTimeSlot();
        if ($al == $bl) {
        	return ($ats > $bts) ? +1 : -1; // sort by timeslot
            // return 0;
        }
        return ($al > $bl) ? +1 : -1;
    }

    /**
     * @Route("setservings/{eventId}", name="setServings")
     */
    public function setServings ($eventId, Request $request) 
    {
    	$yield = intval($request->request->get('yield'));
    	$recipeId = $request->request->get('recipeId');
    	$em = $this->getDoctrine()->getManager();
		$event = $em->getRepository('AppBundle:Event')->find($eventId);

		if (!$event) {
			throw $this->createNotFoundException(
				'No event found for id ' . $eventId
			);
		}

		$event->addServings($recipeId, $yield);
		$em->flush();

		$html = $this->shoppingListAction($request, TRUE);

		return $html;
    }

    /**
     * @Route("remove/ingr/{ingr}", name="removeIngr")
     */
    public function removeIngr ($ingr, Request $request)
    {
    	$event = intval($request->request->get('event'));
		$user = $this->getUser();

		$cache = new PhpFileCache($this->container->getParameter('kernel.cache_dir') . '/mealplanner');
		$cacheKey = 'user'.$user->getId().'items_removed';
		$removed = !empty($cache->fetch($cacheKey)) ? $cache->fetch($cacheKey) : [];

		$removed[$event][] = $ingr;
		$cache->save($cacheKey, $removed);

		return new JsonResponse('Item removed', 200);
    }

    /**
     * @Route("shopping-list/reset", name="resetShoppingList")
     */
    public function resetShoppingList (Request $request)
    {
		$user = $this->getUser();
		$cache = new PhpFileCache($this->container->getParameter('kernel.cache_dir') . '/mealplanner');
		$cacheKey = 'user'.$user->getId().'items_removed';
		$cache->delete($cacheKey);
		$cacheKey = 'user'.$user->getId().'extra_items';
		$cache->delete($cacheKey);

		$this->addFlash('notice', 'De boodschappenlijst werd hersteld.');

		return new JsonResponse('Shopping list reset', 200);
    }

    /**
     * @Route("dept/set/{id}", name="setDepartment")
     */
    public function setDepartment ($id, Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
    	$ingredient = $em->getRepository('AppBundle:Ingredient')->find($id);
    	$dept = $em->getRepository('AppBundle:Afdeling')->findOneByName($request->request->get('dept'));
    	if (!$ingredient || !$dept) {
    		return new JsonResponse(array('message' => 'Something went wrong'), 404);
    	}
    	$ingredient->setAfdeling($dept);
    	$em->flush();

		return new JsonResponse('Department was set', 200);
    }


    /**
     * @Route("pdf", name="topdf")
     */
    public function generatePDFAction(Request $request)
    {
		$html = $this->shoppingListAction($request, TRUE);

		return new Response(
			$this->get('knp_snappy.pdf')->getOutputFromHtml($html, array(
				'enable-javascript' => true,
				'javascript-delay' => 1000,
				'no-stop-slow-scripts' => true,
				'encoding' => 'utf-8',
				)
			),
			200,
			array(
				'Content-Type'          => 'application/pdf',
				'Content-Disposition'   => 'attachment; filename="Mealplanner.pdf"'
			)
		);
    }

    
    /**
     * @Route("csv", name="tocsv")
     */
    public function generateCSVAction(Request $request)
    {
		$items = $this->shoppingListAction($request, FALSE, TRUE);

		$rows = array();
		foreach ($items as $department => $ingredients) {
			if (is_array($ingredients)) {
				foreach ($ingredients as $i) {
					$data = array(
						$department,
						$i->getHoeveelheid(),
						$i->getEenheid(), 
						$i->getIngredient(), 
						$i->getRecept()->getTitel()
					);
					$rows[] = implode(",", $data);
				}
			}
		}

		$content = implode("\n", $rows);
		
    	$response = new Response($content);
    	$response->headers->set('Content-Type', 'text/csv');
    	$response->headers->set('Content-Disposition', 'attachment; filename="export.csv"');
    	
    	return $response;
    }
    
    /**
     * @Route("sendmail", name="sendmail")
     */
    public function sendEmailAction(Request $request)
    {
		$user = $this->getUser();
		     
     	$defaultData = array();
     	$form = $this->get('form.factory')
     		->createNamedBuilder('sendmail', FormType::class, $defaultData)
     		->setAction($this->generateUrl('sendmail'))
     		->setMethod('POST')
			->add('from', EmailType::class, array(
				'label' => 'Van:',
				'data' => $this->getParameter('mailer_from'),
				'attr' => array('disabled' => 'disabled'),
				))
			->add('to', EmailType::class, array(
				'label' => 'Aan:',
				'constraints' => array(new NotBlank(), new Email()),
				))
			->add('subject', TextType::class, array(
				'label' => 'Onderwerp',
				'attr' => array('placeholder' => 'Boodschappenlijst'),
				'empty_data' => 'Boodschappenlijst',
				))
			->add('message', TextareaType::class, array(
				'label' => 'Bericht',
				'attr' => array('rows' => 5, 'cols' => 50, 'placeholder' => 'Hier is mijn geweldige boodschappenlijst!'),
				))
			->add('send', SubmitType::class, array('label' => 'Verzend'))
			->getForm();
			
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();

			$message = \Swift_Message::newInstance()
				->setSubject($data['subject'])
				->setFrom($this->getParameter('mailer_from'))
				->setTo($data['to'])
				->setBody(
					$this->renderView(
						'shopping/email.html.twig',
						array('data' => $data)
					),
					'text/html'
				)
			;
			
			$pdf = $this->forward('AppBundle:ShoppingList:generatePDF');
			$attachment = \Swift_Attachment::newInstance($pdf, 'Boodschappenlijst.pdf', 'application/pdf');
			$message->attach($attachment);
			
			$this->get('mailer')->send($message);			
			
			// add flash message
        	$this->addFlash(
            	'notice',
            	'Er werd een e-mail verstuurd naar '.$data['to']);
        			
			if ($request->isXmlHttpRequest()) { 
				return new JsonResponse(array('message' => 'Success!'), 200);
			} else {			
			return $this->redirectToRoute('shoppingList');
			}
		}
		elseif ($form->isSubmitted() && !$form->isValid()) {
			$response = new JsonResponse(
				array(
					'message' => 'Error',
					'form' => $this->renderView('shopping/sendmailform.html.twig', array(
						'form' => $form->createView()
						)
					)
				), 400);
 
			return $response;
		}
		
		return $this->render('shopping/sendmailform.html.twig', array('form' => $form->createView()));
    }

    /**
     * @Route("add/item", name="additemtoshoppinglist")
     */
    public function addItemToShoppingListAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();

		$user = $this->getUser();
		    	
    	$ingredient = new Ingredient();

    	$form = $this->get('form.factory')
     		->createNamedBuilder('additem', IngredientType::class, $ingredient)
    		->setAction($this->generateUrl('additemtoshoppinglist'))
    		->add('submit', SubmitType::class, array(
    			'label' => 'Voeg toe'
    		))
    		->getForm();    		
    	
    	$form->handleRequest($request);
    	
    	if($form->isSubmitted() && $form->isValid()){
    		$ingredient = $form->getData();

    		// Assign department
			$finder = $this->container->get('app.dept_finder');
			$dept = $finder->findDept($ingredient->getIngredient());
			$ingredient->setAfdeling($dept);

			// Persist in cache
			$cacheDir = $this->container->getParameter('kernel.cache_dir');
			$cache = new PhpFileCache($cacheDir . '/mealplanner');
			$cacheKey = 'user'.$user->getId().'extra_items';
			if (!$cache->contains($cacheKey)) {
				$ingredients = [];
			} else {
				$ingredients = $cache->fetch($cacheKey);
			}
			$ingredients[] = serialize($ingredient);

			$cache->save($cacheKey, $ingredients);

			if ($request->isXmlHttpRequest()) { 
				return new JsonResponse(array('message' => 'Success!'), 200); 
			} else {    		
    			return $this->redirectToRoute('shoppingList');
    		}
    	}
		elseif ($form->isSubmitted() && !$form->isValid()) {
			$response = new JsonResponse(
				array(
					'message' => 'Error',
					'form' => $this->renderView('shopping/additem.html.twig', array(
						'form' => $form->createView()
						)
					)
				), 400);
 
			return $response;
		}    	
    	
    	return $this->render('shopping/additem.html.twig', array('form' => $form->createView()));
    }
}