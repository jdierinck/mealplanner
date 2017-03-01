<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Ingredient;
use AppBundle\Entity\Boodschappenlijst;
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
    	$values = array();
//     	$selects = array();
    	foreach($request->request->all() as $key => $value){
    		if (substr($key,0,4)==="ingr") {
    			$values[] = $value;
    		}
//     		if (substr($key,0,6)==="select") {
//     			$selects[substr($key,6)] = $value;
// 			}
    	}
    	
    	$em = $this->getDoctrine()->getManager();
    	
// 		$session = $request->getSession();
		
		if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
			throw $this->createAccessDeniedException();
		}		
		$user = $this->getUser();
		$boodschappenlijst = $user->getBoodschappenlijst();
		
		// Create Boodschappenlijst if none exists
		if(!$boodschappenlijst){
			$boodschappenlijst = new Boodschappenlijst();
			$boodschappenlijst->setUser($user);
			
			$em->persist($boodschappenlijst);
			$em->flush();
// 			$session->set('boodschappenlijst_id', $boodschappenlijst->getId());
		}

// 		$boodschappenlijstid = $session->get('boodschappenlijst_id');
		$repository = $em->getRepository('AppBundle:Boodschappenlijst');
// 		$boodschappenlijst = $repository->find($boodschappenlijstid);
		
		if ($values) {
			foreach($values as $value){
				foreach($boodschappenlijst->getIngredienten() as $ingredient){
					if($ingredient->getId() == $value) {
						$ingredient->setBoodschappenlijst(null);
					}
				}
			}
		$em->flush();
		$em->refresh($boodschappenlijst);
		}
		
		if($request->isXmlHttpRequest()){
			$selects = $request->request->all();
			if($selects){
				foreach($selects as $key=>$value){
					$ingredient = $em->getRepository('AppBundle:Ingredient')->find(substr($key,6));
					$afdeling = $em->getRepository('AppBundle:Afdeling')->find($value);
					$ingredient->setAfdeling($afdeling);
				}
				$em->flush();
				$em->refresh($boodschappenlijst);
			}
		}
		
		$afdelingen = $repository
			->findIngredientenByBoodschappenlijst($boodschappenlijst);
		
// 		$recepten = $repository->findReceptenByBoodschappenlijst($boodschappenlijstid);

		$recepten = $boodschappenlijst->getRecepten();
		foreach($recepten as $recept){
			if(!in_array($recept, $repository->findReceptenByBoodschappenlijst($boodschappenlijst->getId()))){
				foreach($recept->getReceptenblordered() as $ro){
					if($ro->getBoodschappenlijst()->getId() === $boodschappenlijst->getId()){
						$em->remove($ro);
					}
				}
			$em->flush();
			}
		}
		$em->refresh($boodschappenlijst);
		$recepten = $boodschappenlijst->getRecepten();
		
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
    	
//     	$session = $request->getSession();
// 
// 		$boodschappenlijst = $em->getRepository('AppBundle:Boodschappenlijst')
// 				->find($session->get('boodschappenlijst_id'));
		
		$user = $this->getUser();
		$boodschappenlijst = $user->getBoodschappenlijst();

		foreach($boodschappenlijst->getIngredienten() as $ingredient){
				// Cleanup: remove ingredients not attached to a recipe
				if($ingredient->getRecept() == null){
					$em->remove($ingredient);
				} else {
				$ingredient->setBoodschappenlijst(null);
				}
		}
		
		foreach($boodschappenlijst->getReceptenblordered() as $ro)
		{
			$em->remove($ro);
		}
		
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
		
// 		$session = $request->getSession();
// 
// 		$boodschappenlijst = $em->getRepository('AppBundle:Boodschappenlijst')
// 				->find($session->get('boodschappenlijst_id'));
		
		$user = $this->getUser();
		$boodschappenlijst = $user->getBoodschappenlijst();
				
		foreach($recept->getReceptenblordered() as $ro){ 
			if($ro->getBoodschappenlijst()->getId() === $boodschappenlijst->getId()){
				$em->remove($ro);
			}
		}
		
		foreach($recept->getIngredienten() as $ingredient){
			$ingredient->setBoodschappenlijst(null);
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
    	
//     	$session = $request->getSession();
// 
// 		$boodschappenlijst = $em->getRepository('AppBundle:Boodschappenlijst')
// 				->find($session->get('boodschappenlijst_id'));
		
		$user = $this->getUser();
		$boodschappenlijst = $user->getBoodschappenlijst();
    	
    	$recept = $em->getRepository('AppBundle:Recept')
				->find($id);
		if (!$recept) {
			throw $this->createNotFoundException(
				'No recipe found for id '.$id
			);
		}

		foreach($recept->getIngredienten() as $ingredient){
			$ingredient->setBoodschappenlijst($boodschappenlijst);
		}
		
		$recepten = array();
		$recepten[] = $recept;
		$boodschappenlijst->setRecepten($recepten);
		
    	$em->flush();
    	
    	$message = $recept->getTitel()." werd toegevoegd aan je boodschappenlijst.";
    	$this->addFlash(
			'notice',
			$message);

    	return $this->redirectToRoute('homepage');
    }
    
    /**
     * @Route("add/item", name="additemtoshoppinglist")
     */
    public function addItemToShoppingListAction(Request $request){
    	
    	$em = $this->getDoctrine()->getManager();
    	
//     	$session = $request->getSession();
//     	
//     	$boodschappenlijstid = $session->get('boodschappenlijst_id');
//     	$boodschappenlijst = $em->getRepository('AppBundle:Boodschappenlijst')
//     		->find($boodschappenlijstid);

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
    		$ingredient->setBoodschappenlijst($boodschappenlijst);
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
//     	$session = $request->getSession();
    	$em = $this->getDoctrine()->getManager();
    	
//     	$boodschappenlijstid = $session->get('boodschappenlijst_id');
// 		$repository = $em->getRepository('AppBundle:Boodschappenlijst');

		$user = $this->getUser();
		$boodschappenlijst = $user->getBoodschappenlijst();

		$afdelingen = $em->getRepository('AppBundle:Boodschappenlijst')
			->findIngredientenByBoodschappenlijst($boodschappenlijst->getId());
			
		$html = $this->renderView('shopping/shoppinglisttopdf.html.twig', array(
			'afdelingen'  => $afdelingen
		));

		return new Response(
			$this->get('knp_snappy.pdf')->getOutputFromHtml($html),
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
//     	$session = $request->getSession();
    	$em = $this->getDoctrine()->getManager();
    	
//     	$boodschappenlijstid = $session->get('boodschappenlijst_id');
// 		$repository = $em->getRepository('AppBundle:Boodschappenlijst');
		$user = $this->getUser();
		$boodschappenlijst = $user->getBoodschappenlijst();

		$afdelingen = $em->getRepository('AppBundle:Boodschappenlijst')
			->findIngredientenByBoodschappenlijst($boodschappenlijst->getId());
		
		$rows = array();
		foreach($afdelingen as $afdeling){
			foreach($afdeling->getIngredienten() as $ingredient){
				$data = array(
					$afdeling->getName(), 
					$ingredient->getHoeveelheid(), 
					$ingredient->getEenheid(), 
					$ingredient->getIngredient(), 
					$ingredient->getRecept()->getTitel()
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
     * @Route("edit/ingr", name="editingredients")
     */     
     public function editIngredientsAction(Request $request){

     
 	}   
    
}
