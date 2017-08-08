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
use Symfony\Component\Validator\Constraints as Assert;
use AppBundle\Controller\AccountNonExpiredController;

class ShoppingController extends Controller implements AccountNonExpiredController
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
    	foreach ($request->request->all() as $key => $value) {
    		if (substr($key,0,4) === "ingr") {
    			$values[] = $value;
    		}
    		if (substr($key,0,4) === "pers") {
    			$persons[substr($key,4)] = $value;
			}
    	}
		
		if ($values) {
			foreach ($values as $value) {
				foreach ($boodschappenlijst->getIngrBL() as $ibl) {
					if ($ibl->getId() == $value) {
						// remove original ingredient if it is not associated with a recipe
						$ingredient = $ibl->getIngredient();
						$recept = $ingredient->getRecept();
						if ($recept === null) {
							$em->remove($ingredient);
						}
						$em->remove($ibl);
					}
				}
			}
			$em->flush();
			$em->refresh($boodschappenlijst);
		}
		
		if($persons){
			foreach ($persons as $key=>$value) {
				foreach($boodschappenlijst->getReceptenBLOrdered() as $ro){
					if($ro->getId() == $key){
						$ro->setServings($value);
					}
				}
				foreach ($boodschappenlijst->getIngrbl() as $ibl) {
					if ($ibl->getReceptblordered()->getId() == $key) {
						$ibl->setServings($value);
					}
				}
			}
			$em->flush();
		}

		// Modify an ingredient's afdeling		
		if($request->isXmlHttpRequest()){
			foreach($request->request->all() as $key=>$value){
				if(substr($key,0,6)==="select"){
						$ibl = $em->getRepository('AppBundle:IngrBL')->find(substr($key,6));
						$ingredient = $ibl->getIngredient();
						$afdeling = $em->getRepository('AppBundle:Afdeling')->find($value);
						$ibl->setAfdeling($afdeling);
						// Set the new afdeling on original ingredient also
						$ingredient->setAfdeling($afdeling);
					
					$em->flush();
					$em->refresh($boodschappenlijst);
				}
			}
		}

		$qb = $em->createQueryBuilder();
	    $query = $qb->select('IDENTITY(ibl.receptblordered)')
	    		->from('AppBundle:IngrBL','ibl')
	    		->where('ibl.boodschappenlijst = :boodschappenlijst')
	    		->setParameter('boodschappenlijst', $boodschappenlijst)
	    		->groupBy('ibl.receptblordered')
	    		->getQuery();
	    $ro_ids = $query->getResult();
	    $ids = array();
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
    	$query = $qb->select('ro', 'partial r.{id,titel,personen}')
    			->from('AppBundle:ReceptBLOrdered','ro')
    			->join('ro.recept', 'r')
    			->where('ro.boodschappenlijst = :boodschappenlijst')
    			->setParameter('boodschappenlijst', $boodschappenlijst)
    			->getQuery();
    	$recepten = $query->getResult();

		$qb = $em->createQueryBuilder();
		$query = $qb->select('ao','a','ibl')
	        ->from('AppBundle:AfdelingOrdered','ao')           
	        ->where('ao.user = :user')
	        ->orderBy('ao.positie')             
	        ->setParameter('user', $user)
	        ->join('ao.afdeling', 'a')
	        ->join('a.ingrbl', 'ibl')
	        ->andWhere('ibl.boodschappenlijst = :boodschappenlijst')
	        // ->setParameters(['boodschappenlijst'=>$boodschappenlijst, 'user'=>$user])
	        ->setParameter('boodschappenlijst', $boodschappenlijst)
	        ->getQuery();
        $afdelingenordered = $query->getResult();
		
		// Get all departments, ordered
		$qb = $em->createQueryBuilder();
		$query = $qb->select('ao','a')
	        ->from('AppBundle:AfdelingOrdered','ao')           
	        ->where('ao.user = :user')
	        ->orderBy('ao.positie')             
	        ->setParameter('user', $user)
	        ->join('ao.afdeling', 'a')
	        ->getQuery();
        $alleafdelingen = $query->getResult();		

		// Get start date
		$startDate = null;
		if (count($boodschappenlijst->getRecepten()) > 0) {
			$repo = $em->getRepository('AppBundle:ReceptBLOrdered');
			$query = $repo->createQueryBuilder('ro')
					->where('ro.boodschappenlijst = :bl')
					->setParameter(':bl', $boodschappenlijst)
					->orderBy('ro.datum', 'ASC')
					->getQuery();
			$result = $query->setMaxResults(1)->getOneOrNullResult();
			$startDate = $result->getDatum()->format('d/m/Y');
		}
		
        return $this->render('shopping/shopping.html.twig', array(
			'afdelingenordered' => $afdelingenordered, 
			'boodschappenlijst' => $boodschappenlijst,
			'recepten' => $recepten,
			'alleafdelingen' => $alleafdelingen,
			'startdatum' => $startDate,
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
			// remove original ingredient if it is not associated with a recipe
			$ingredient = $ibl->getIngredient();
			$recept = $ingredient->getRecept();
			if ($recept === null) {
				$em->remove($ingredient);
			}
			// remove IngrBL
			$em->remove($ibl);
		}
		
		foreach($boodschappenlijst->getReceptenblordered() as $ro)
		{
			$em->remove($ro);
		}
		
    	$em->flush();
    	
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
    	
    	$ro = $em->getRepository('AppBundle:ReceptBLOrdered')
				->find($id);
		if (!$ro) {
			throw $this->createNotFoundException(
				'No recipe found for id '.$id
			);
		}
		
		$user = $this->getUser();
		$boodschappenlijst = $user->getBoodschappenlijst();

		foreach($boodschappenlijst->getIngrBL() as $ibl){
			if($ibl->getReceptblordered() === $ro){
				$em->remove($ibl);
			}
		}

		$em->remove($ro);
		
    	$em->flush();
    	
    	$message = $ro->getRecept()->getTitel()." werd verwijderd uit je boodschappenlijst.";
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
		foreach ($recept->getIngredienten() as $i) {
			if (false === $i->isSection()) { 
				$ingredienten[] = $i;
			}
		}

		// Find last date in BL, and if it exists increment it and set it on new object
		$repo = $em->getRepository('AppBundle:ReceptBLOrdered');
		$query = $repo->createQueryBuilder('r')
			->where('r.boodschappenlijst = :bl')
			->setParameter(':bl', $boodschappenlijst)
			->orderBy('r.datum', 'DESC')
			->getQuery();
		$lastRecept = $query->setMaxResults(1)->getOneOrNullResult();

		if ($lastRecept) {
			$date = $lastRecept->getDatum();
			$date->modify('+1 day');
		} else {
			$date = new \DateTime("now");
		}

		$ro = new ReceptBLOrdered();
		$ro->setBoodschappenlijst($boodschappenlijst);
        $ro->setRecept($recept);
        $ro->setServings(4);
        $ro->setDatum($date);
		
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
    public function addItemToShoppingListAction(Request $request)
    {
    	
    	$em = $this->getDoctrine()->getManager();

		$user = $this->getUser();
		$boodschappenlijst = $user->getBoodschappenlijst();
		    	
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
    public function generatePDFAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();

		$user = $this->getUser();
		$boodschappenlijst = $user->getBoodschappenlijst();

		$qb = $em->createQueryBuilder();
		$query = $qb->select('ao','a','ibl')
	        ->from('AppBundle:AfdelingOrdered','ao')           
	        ->where('ao.user = :user')
	        ->orderBy('ao.positie')             
	        ->setParameter('user', $user)
	        ->join('ao.afdeling', 'a')
	        ->join('a.ingrbl', 'ibl')
	        ->andWhere('ibl.boodschappenlijst = :boodschappenlijst')
	        // ->setParameters(['boodschappenlijst'=>$boodschappenlijst, 'user'=>$user])
	        ->setParameter('boodschappenlijst', $boodschappenlijst)
	        ->getQuery();
        $afdelingen = $query->getResult();
			
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
    }
    
    /**
     * @Route("csv", name="tocsv")
     */
    public function generateCSVAction(Request $request)
    {

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
     public function eventsAction(Request $request)
     {
		$em = $this->getDoctrine()->getManager();
    	
		$user = $this->getUser();
		$boodschappenlijst = $user->getBoodschappenlijst();

		$events = array();
		foreach($boodschappenlijst->getReceptenblordered() as $ro){
			$events[] = array(
					"title" => $ro->getRecept()->getTitel(), 
					"id" => $ro->getId(), 
					"start" => $ro->getDatum()->format('Y-m-d'),
				);
			}

		return new JsonResponse($events); 
 	}
 	
 	/**
     * @Route("saveevents", name="saveevents")
     */     
     public function saveEventsAction(Request $request)
     { 
     	$em = $this->getDoctrine()->getManager();

     	$repo = $em->getRepository('AppBundle:ReceptBLOrdered');
		
		$events = $request->request->get('events');
		$events = json_decode($events, true);

		foreach ($events as $event) {
			$ro = $repo->find($event["id"]);
			$newDate = $event["start"];
			$ro->setDatum(new \DateTime($newDate));
			$em->flush();
		}
		
		$message = "Planning bewaard";
		return new JsonResponse($message);
		
    }

    /**
     * @Route("startdate", name="startdate")
     */     
     public function startDateAction(Request $request)
     {
     	$startdate = $request->request->get('startdate');

     	$validator = $this->get('validator');

     	// Check if date has valid format
     	$dateConstraint = new Assert\Regex(array('pattern' => '/^(0?[1-9]|[12][0-9]|3[01])[\/\-](0?[1-9]|1[012])[\/\-]\d{4}$/'));
     	$dateConstraint->message = 'Geen geldige datum. Probeer opnieuw.';

     	$errorList = $validator->validate($startdate, $dateConstraint);

     	if (0 === count($errorList)) {
			$newStartDate = \DateTime::createFromFormat('d/m/Y', $startdate);
			$newStartDate->setTime(0,0); // Set time to 00:00:00 instead of current time for correct calculation of differences between dates

			$em = $this->getDoctrine()->getManager();
			$user = $this->getUser();
			$bl = $user->getBoodschappenlijst();

			// Get previous start date
			$repo = $em->getRepository('AppBundle:ReceptBLOrdered');
			$query = $repo->createQueryBuilder('ro')
				->where('ro.boodschappenlijst = :bl')
				->setParameter(':bl', $bl)
				->orderBy('ro.datum', 'ASC')
				->getQuery();
			$result = $query->setMaxResults(1)->getOneOrNullResult();
			$previousStartDate = $result->getDatum();

			// Calculate difference
			$diff = $previousStartDate->diff($newStartDate);
			$diff = $diff->format('%R%a days'); // Format DateInterval to string

			foreach ($bl->getReceptenblordered() as $ro) {
				$previousDate = $ro->getDatum();
				$newDate = $previousDate->modify($diff);

				// Note: $ro->setDatum($newDate); doesn't work, you have to pass a new DateTime object
				// See https://stackoverflow.com/questions/15486402/doctrine2-orm-does-not-save-changes-to-a-datetime-field/15488230#15488230
				$ro->setDatum(new \DateTime($newDate->format('Y-m-d')));

				$em->flush();
			}

			$message = "De startdatum werd aangepast.";
			return new JsonResponse($message, 200);
     	} else {
     		$message = $errorList[0]->getMessage();
     		// Do something with the error
     		return new JsonResponse($message, 400);
     	}
     	
     	
     }
}
