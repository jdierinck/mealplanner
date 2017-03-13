<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Ingredient;
use AppBundle\Entity\Boodschappenlijst;
use AppBundle\Entity\ReceptBLOrdered;
use AppBundle\Form\Type\IngredientType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\FormType;

class ShoppingController extends Controller
{
    
    /**
     * @Route("boodschappen", name="boodschappen")
     */
    public function boodschappenAction(Request $request)
    {
		if(!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
			throw $this->createAccessDeniedException();
		}
		
    	$em = $this->getDoctrine()->getManager();
			
		$user = $this->getUser();
		$boodschappenlijst = $user->getBoodschappenlijst();
		
		// Create Boodschappenlijst if none exists
		if(!$boodschappenlijst){
			$boodschappenlijst = new Boodschappenlijst();
			$boodschappenlijst->setUser($user);
			
			$em->persist($boodschappenlijst);
			$em->flush();
		}	
		
		$repository = $em->getRepository('AppBundle:Boodschappenlijst');	
		    
    	$values = array();
    	$persons = array();
    	foreach($request->request->all() as $key => $value){
    		if (substr($key,0,4)==="ingr") {
    			$values[] = $value;
    		}
    		if (substr($key,0,4)==="pers") {
    			$persons[substr($key,4)] = $value;
			}
    	}
		
		if ($values) {
			foreach($values as $value){
				foreach($boodschappenlijst->getIngrBL() as $ibl){
					if($ibl->getId() == $value) {
						$em->remove($ibl);
					}
				}
			}
		$em->flush();
		$em->refresh($boodschappenlijst);
		}
		
		if($persons){
			foreach($persons as $key=>$value){
				foreach($boodschappenlijst->getReceptenBLOrdered() as $ro){
					if($ro->getId() == $key){
						$ro->setServings($value);
					}
				}
				foreach($boodschappenlijst->getIngrbl() as $ibl){
					if($ibl->getReceptblordered()->getId() == $key){
						$ibl->setServings($value);
					}
				}
			}
			$em->flush();
		}
		
		if($request->isXmlHttpRequest()){
			foreach($request->request->all() as $key=>$value){
				if(substr($key,0,6)==="select"){
						$ibl = $em->getRepository('AppBundle:IngrBL')->find(substr($key,6));
						$ingredient = $ibl->getIngredient();
						$afdeling = $em->getRepository('AppBundle:Afdeling')->find($value);
						$ibl->setAfdeling($afdeling);
						$ingredient->setAfdeling($afdeling);
					
					$em->flush();
					$em->refresh($boodschappenlijst);
				}
			}
		}

// TO DO DOESNT WORK
// 		$recepten = $boodschappenlijst->getRecepten();
// 		foreach($recepten as $recept){
// 			if(!in_array($recept, $repository->findReceptenByBoodschappenlijst($boodschappenlijst->getId()))){
// 				foreach($recept->getReceptenblordered() as $ro){
// 					if($ro->getBoodschappenlijst()->getId() === $boodschappenlijst->getId()){
// 						$em->remove($ro);
// 					}
// 				}
// 			$em->flush();
// 			}
// 		}
// 		$em->refresh($boodschappenlijst);
//
	$qb = $em->createQueryBuilder();
    $query = $qb->select('IDENTITY(ibl.receptblordered)')
    		->from('AppBundle:IngrBL','ibl')
    		->where('ibl.boodschappenlijst = :boodschappenlijst')
    		->setParameter('boodschappenlijst', $boodschappenlijst)
    		->groupBy('ibl.receptblordered')
    		->getQuery();
    $ro_ids=$query->getResult();
    $ids=array();
    foreach($ro_ids as $key=>$value){
    	$ids[]=$value[1];
    }
    foreach($boodschappenlijst->getReceptenblordered() as $ro){
    	if(!in_array($ro->getId(), $ids)){
    		$em->remove($ro);
    	}
    }
    $em->flush();


    	$qb = $em->createQueryBuilder();
    	$query = $qb->select('ro', 'partial r.{id,titel}')
    			->from('AppBundle:ReceptBLOrdered','ro')
    			->join('ro.recept', 'r')
    			->where('ro.boodschappenlijst = :boodschappenlijst')
    			->setParameter('boodschappenlijst', $boodschappenlijst)
    			->getQuery();
    	$recepten = $query->getResult();
    	
//     	$qb = $em->createQueryBuilder();
//     	$query = $qb->select('a','ibl')
//     			->from('AppBundle:Afdeling','a')
//     			->join('a.ingrbl', 'ibl')
//     			->join('ibl.ingredient','i')
//     			->where('ibl.boodschappenlijst = :boodschappenlijst')
//     			->setParameter('boodschappenlijst', $boodschappenlijst)
//     			->orderBy('a.name')
//     			->getQuery();
//     	$afdelingen = $query->getResult();
    	
    	$afdelingen = $em->getRepository('AppBundle:Afdeling')->findIngredientenByAfdeling($boodschappenlijst);
		
		$alleafdelingen = $em->getRepository('AppBundle:Afdeling')->findAll();
		
        return $this->render('shopping/shopping.html.twig', array(
			'afdelingen' => $afdelingen, 
			'boodschappenlijst' => $boodschappenlijst,
			'recepten' => $recepten,
			'alleafdelingen' => $alleafdelingen
        ));
    }
    
    /**
     * @Route("wis", name="wislijst")
     */
    public function wisLijstAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
		
		$user = $this->getUser();
		$boodschappenlijst = $user->getBoodschappenlijst();

		foreach($boodschappenlijst->getIngrBL() as $ibl){
			$em->remove($ibl);
		}
		
		foreach($boodschappenlijst->getReceptenblordered() as $ro)
		{
			$em->remove($ro);
		}
		
		$boodschappenlijst->setEvents(null);
		
    	$em->flush();
    	
//     	return $this->render('shopping/shoppinglistdeleted.html.twig');
    	$message = "Je boodschappenlijst werd gewist.";
    	$this->addFlash(
			'notice',
			$message);
			
    	return $this->redirectToRoute('boodschappen');
    }
    
    /**
     * @Route("wis/recept/{id}", name="wisrecept", requirements={"id": "\d+"})
     */
    public function wisReceptAction(Request $request, $id)
    {
    	$em = $this->getDoctrine()->getManager();
    	
    	$recept = $em->getRepository('AppBundle:Recept')
				->find($id);
		if (!$recept) {
			throw $this->createNotFoundException(
				'No recipe found for id '.$id
			);
		}
		
		$user = $this->getUser();
		$boodschappenlijst = $user->getBoodschappenlijst();
				
		foreach($recept->getReceptenblordered() as $ro){ 
			if($ro->getBoodschappenlijst()->getId() === $boodschappenlijst->getId()){
				$em->remove($ro);
			}
		}
		
		foreach($boodschappenlijst->getIngrBL() as $ibl){
			if($ibl->getIngredient()->getRecept() === $recept){
				$em->remove($ibl);
			}
		}
		
		$events = $boodschappenlijst->getEvents();

		if($events){
			foreach($events as $key=>$event){

				if($event["title"] === $recept->getTitel()){
					unset($events[$key]);
				}
			}
			if(count($events) > 0) { 
				$events = array_values($events);
			}
			else { $events = null; }
			
			$boodschappenlijst->setEvents($events);
			$em->flush();
		}
		
    	$em->flush();
    	
    	$message = $recept->getTitel()." werd verwijderd uit je boodschappenlijst.";
    	$this->addFlash(
			'notice',
			$message);
			
    	return $this->redirectToRoute('boodschappen');
    }
    
    /**
     * @Route("add/recept/{id}", name="addrecipetoshoppinglist", requirements={"id": "\d+"})
     */
    public function addRecipetoShoppingListAction(Request $request, $id)
    {
    	$em = $this->getDoctrine()->getManager();
		
		$user = $this->getUser();
		$boodschappenlijst = $user->getBoodschappenlijst();
    	
    	$recept = $em->getRepository('AppBundle:Recept')
				->find($id);
		if (!$recept) {
			throw $this->createNotFoundException(
				'No recipe found for id '.$id
			);
		}
		
		$ingredienten = array();
		foreach($recept->getIngredienten() as $ingredient){
			$ingredienten[] = $ingredient;
		}
// 		$boodschappenlijst->setIngredienten($ingredienten);
// 		
// 		$recepten = array();
// 		$recepten[] = $recept;
// 		$boodschappenlijst->setRecepten($recepten);
		
		$ro = new ReceptBLOrdered();
		$ro->setBoodschappenlijst($boodschappenlijst);
        $ro->setRecept($recept);
        $ro->setServings(4);
		
		$ro->setIngrBL($ingredienten);
		
		$em->persist($ro);
    	$em->flush();
    	
    	$message = $recept->getTitel()." werd toegevoegd aan je boodschappenlijst.";
    	$this->addFlash(
			'notice',
			$message);

    	return $this->redirectToRoute('recipes');
    }
    
    /**
     * @Route("add/item", name="additemtoshoppinglist")
     */
    public function addItemToShoppingListAction(Request $request){
    	
    	$em = $this->getDoctrine()->getManager();

		$user = $this->getUser();
		$boodschappenlijst = $user->getBoodschappenlijst();
		    	
    	$ingredient = new Ingredient();
    	
    	$form = $this->get('form.factory')
     		->createNamedBuilder('additem', FormType::class, $ingredient)
    		->setAction($this->generateUrl('additemtoshoppinglist'))
    		->add('hoeveelheid', TextType::class)
    		->add('eenheid', TextType::class)
    		->add('ingredient', TextType::class)
    		->add('afdeling', EntityType::class, array(
    			'class' => 'AppBundle:Afdeling',
    			'choice_label' => 'name'
    		))
    		->add('submit', SubmitType::class, array(
    		'label' => 'Voeg toe'))
    		->getForm();
    	
    	$form->handleRequest($request);
    	
    	if($form->isSubmitted() && $form->isValid()){
    		$ingredient = $form->getData();
			$ingredienten = array($ingredient);
			$boodschappenlijst->setIngredienten($ingredienten);
    		$em->persist($ingredient);
    		$em->flush();

			if ($request->isXmlHttpRequest()) { 
				return new JsonResponse(array('message' => 'Success!'), 200); 
			} else {    		
    		return $this->redirectToRoute('boodschappen');
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
    
     /**
     * @Route("pdf", name="topdf")
     */
    public function generatePDFAction(Request $request){
    	$em = $this->getDoctrine()->getManager();

		$user = $this->getUser();
		$boodschappenlijst = $user->getBoodschappenlijst();

		$afdelingen = $em->getRepository('AppBundle:Afdeling')->findIngredientenByAfdeling($boodschappenlijst);
			
		$html = $this->render('shopping/shoppinglisttopdf.html.twig', array(
			'afdelingen'  => $afdelingen
		));

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

// 		return $this->render('shopping/shoppinglisttopdf.html.twig', array(
// 			'afdelingen'  => $afdelingen
// 		));

    }
    
    /**
     * @Route("csv", name="tocsv")
     */
    public function generateCSVAction(Request $request){

    	$em = $this->getDoctrine()->getManager();
    	
		$user = $this->getUser();
		$boodschappenlijst = $user->getBoodschappenlijst();

		$afdelingen = $em->getRepository('AppBundle:Afdeling')->findIngredientenByAfdeling($boodschappenlijst);
		
		$rows = array();
		foreach($afdelingen as $afdeling){
			foreach($afdeling->getIngrBL() as $ibl){
				$data = array(
					$afdeling->getName(), 
					$ibl->getIngredient()->getHoeveelheid(), 
					$ibl->getIngredient()->getEenheid(), 
					$ibl->getIngredient()->getIngredient(), 
					$ibl->getIngredient()->getRecept()->getTitel()
				);
				$rows[] = implode(",", $data);
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
     public function sendEmailAction(Request $request){
		$user = $this->getUser();
		     
     	$defaultData = array();
     	$form = $this->get('form.factory')
     		->createNamedBuilder('sendmail', FormType::class, $defaultData)
     		->setAction($this->generateUrl('sendmail'))
     		->setMethod('POST')
			->add('from', EmailType::class, array(
			'label' => 'Van:',
// 			'attr' => array('placeholder' => $user->getEmail()),
			'data' => $user->getEmail(),
			'constraints' => array(new NotBlank(), new Email()),
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
				->setFrom($data['from'])
				->setTo($data['to'])
				->setBody(
					$this->renderView(
						// appBundle/views/emails/contact.html.twig
						'shopping/email.html.twig',
						array('data' => $data)
						),
						'text/html'
					)
// 				->addPart(
// 					$this->renderView(
// 						'Emails/registration.txt.twig',
// 						array('name' => $name)
// 					),
// 					'text/plain'
// 				)					
			;
			
			$pdf = $this->forward('AppBundle:Shopping:generatePDF');
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
			return $this->redirectToRoute('boodschappen');
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
     * @Route("events", name="events")
     */     
     public function eventsAction(Request $request){
// 		[{"title":"Marokkaanse groentenstoofpotje met bulgur 168","start":"2017-03-13T10:02:56.054Z","source":{"className":[],"_fetchId":1,"_status":"resolved"},"_id":"_fc1","className":[],"end":null,"allDay":false,"_allDay":false,"_start":"2017-03-13T10:02:56.054Z","_end":null},{"title":"Rijst met scampi en chorizo 169","start":"2017-03-14T10:02:56.054Z","source":{"className":[],"_fetchId":1,"_status":"resolved"},"_id":"_fc2","className":[],"end":null,"allDay":false,"_allDay":false,"_start":"2017-03-14T10:02:56.054Z","_end":null},{"title":"Koolsla met schnitzel 170","start":"2017-03-15T10:02:56.054Z","source":{"className":[],"_fetchId":1,"_status":"resolved"},"_id":"_fc3","className":[],"end":null,"allDay":false,"_allDay":false,"_start":"2017-03-15T10:02:56.054Z","_end":null}]
// 		$events = $request->request->get('events');
// 		$events = '[{"title":"Marokkaanse groentenstoofpotje met bulgur 168","start":"2017-03-13T11:28:38.392Z","source":{"className":[],"_fetchId":1,"_status":"resolved"},"_id":"_fc1","className":[],"end":null,"allDay":false,"_allDay":false,"_start":"2017-03-13T11:28:38.392Z","_end":null},{"title":"Rijst met scampi en chorizo 169","start":"2017-03-14T11:28:38.392Z","source":{"className":[],"_fetchId":1,"_status":"resolved"},"_id":"_fc2","className":[],"end":null,"allDay":false,"_allDay":false,"_start":"2017-03-14T11:28:38.392Z","_end":null},{"title":"Koolsla met schnitzel 170","start":"2017-03-15T11:28:38.392Z","source":{"className":[],"_fetchId":1,"_status":"resolved"},"_id":"_fc3","className":[],"end":null,"allDay":false,"_allDay":false,"_start":"2017-03-15T11:28:38.392Z","_end":null}]';
// 		dump($events);
// 		$events = json_decode($events);
// 		dump($events);
		
// 		$events = [
// 			array("title"=>"event1", "start"=>"2017-03-13"),
// 			array("title"=>"event2", "start"=>"2017-03-14"),
// 		];
		
		$em = $this->getDoctrine()->getManager();
    	
		$user = $this->getUser();
		$boodschappenlijst = $user->getBoodschappenlijst();
		$events = $boodschappenlijst->getEvents();
		
		if(null === $events){
			$events = array();
			$date = date("Y-m-d");
			$i = 0;
			foreach($boodschappenlijst->getReceptenblordered() as $recept){
				$events[] = array("title" => $recept->getRecept()->getTitel(), "start" => date("Y-m-d", strtotime("+".$i." day", strtotime($date))));
				$i++;
			}
		}

		return new JsonResponse($events);
     
 	}
 	
 	/**
     * @Route("saveevents", name="saveevents")
     */     
     public function saveEventsAction(Request $request){ 
     	$em = $this->getDoctrine()->getManager();
    	
		$user = $this->getUser();
		$boodschappenlijst = $user->getBoodschappenlijst();
		
		$events = $request->request->get('events');
		$events = json_decode($events, true);
		
		$boodschappenlijst->setEvents($events);
		$em->flush();
		
		$message = "Planning bewaard";
		return new JsonResponse($message);
		
    }
}
