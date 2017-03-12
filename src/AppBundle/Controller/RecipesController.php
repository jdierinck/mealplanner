<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Recept;
use AppBundle\Form\ReceptType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Ingredient;
use AppBundle\Entity\Boodschappenlijst;

class RecipesController extends Controller
{
    /**
     * @Route("/recepten", name="recipes")
     */
    public function indexAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
    	
		$session = $request->getSession();
		
		if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
			throw $this->createAccessDeniedException();
		}
// 		$user = $this->get('security.token_storage')->getToken()->getUser();
		$user = $this->getUser();
		$boodschappenlijst = $user->getBoodschappenlijst();
		
		// Create Boodschappenlijst if none exists
		if(!$boodschappenlijst){
			$boodschappenlijst = new Boodschappenlijst();
			$boodschappenlijst->setUser($user);
			
			$em->persist($boodschappenlijst);
			$em->flush();
		}  
    
    	$allowedsorts = array('titel', 'gerecht', 'keuken', 'hoofdingredient', 'bereidingstijd', 'toegevoegdOp', 'kostprijs');
    	$sort = $request->query->get('sortBy');
    	if(null == $sort || !in_array($sort, $allowedsorts)) { $sort = 'toegevoegdOp'; }
    	
    	$gerechtid = $request->query->get('gerecht');
    	$keukenid = $request->query->get('keuken');
    	$hoofdingredientid = $request->query->get('hoofdingredient');
    	$bereidingstijd = $request->query->get('bereidingstijd');
    	$zoek = $request->query->get('zoek');
    	
    	$filters = 0;
    	
    	$repository = $this->getDoctrine()->getRepository('AppBundle:Recept');
    	
    	$qb = $repository->createQueryBuilder('r')
    		->where('r.user = :user')
    		->setParameter('user', $user)
    		->orderBy('r.'.$sort, 'ASC');
    		
    	if($gerechtid){
    		$filters += 1;
    		$qb->andWhere('r.gerecht = :gerechtid')
    			->setParameter('gerechtid', $gerechtid);
    	}
    	
    	if($keukenid){
    		$filters += 1;
    		$qb->andWhere('r.keuken = :keukenid')
    			->setParameter('keukenid', $keukenid);
    	}
    	
    	if($hoofdingredientid){
    		$filters += 1;
    		$qb->andWhere('r.hoofdingredient = :hoofdingredientid')
    			->setParameter('hoofdingredientid', $hoofdingredientid);
    	}
    	
    	if($bereidingstijd){
    		$filters += 1;
    		switch ($bereidingstijd) {
    			case "030":
				$qb->andWhere('r.bereidingstijd <= :tijd')
					->setParameter('tijd', '00:30:00');
				break;
				case "3060":
				$qb->andWhere('r.bereidingstijd > :starttijd')
					->andWhere('r.bereidingstijd <= :eindtijd')
// 					->setParameters(array('starttijd' => '00:30:00', 'eindtijd' => '00:60:00'));
					->setParameter('starttijd', '00:30:00')
					->setParameter('eindtijd', '00:60:00');
				break;
				case "60":
				$qb->andWhere('r.bereidingstijd > :tijd')
					->setParameter('tijd', '00:60:00');
				break;
    		}
    	}
    	
    	if($zoek){
    		$qb->andWhere('r.titel LIKE :zoek')
    			->setParameter('zoek', '%'.$zoek.'%');
    	}
    		
//     	$recepten = $qb->getQuery()->getResult();
//     	$total = count($recepten);
    	
    	$query = $qb->getQuery();
    	$paginator = $this->get('knp_paginator');
    	
    	$recepten = $paginator->paginate(
    		$query,
    		$request->query->getInt('page',1),
    		$request->query->getInt('limit',6)
    	);
    	$total = $recepten->getTotalItemCount();
    	
    	
    	if ($zoek || $filters > 0) {			
			$message = $total.' recepten gevonden met ';
			if ($zoek) { $message .= '<mark>'.$zoek.'</mark>'; } 
			if ($zoek && $filters > 0) { $message .= ' en '; }
			if ($filters > 0) { $message .= $filters.' filters';}
			$message .= '.';
			$this->addFlash(
					'notice',
					$message);
		}

		$repository = $this->getDoctrine()->getRepository('AppBundle:Gerecht');
// 		$gerechten = $repository->findAll();
		$query = $repository->createQueryBuilder('g')	
				->select('count(g.id) as number, g.id, g.name')
				->join('g.recepten', 'r')
				->where('r.user = :user')
				->setParameter('user', $user)	
				->groupBy('g.name')
				->getQuery();
		$gerechten = $query->getResult();
		
		$repository = $this->getDoctrine()->getRepository('AppBundle:Keuken');
// 		$keukens = $repository->findAll();
		$query = $repository->createQueryBuilder('k')				
				->select('count(k.id) as number, k.id, k.name')
				->join('k.recepten', 'r')
				->where('r.user = :user')
				->setParameter('user', $user)				
				->groupBy('k.name')
				->getQuery();
		$keukens = $query->getResult();
		
		$repository = $this->getDoctrine()->getRepository('AppBundle:Hoofdingredient');
// 		$hoofdingredienten = $repository->findAll();
		$query = $repository->createQueryBuilder('h')
				->select('count(h.id) as number, h.id, h.name')
				->join('h.recepten', 'r')
				->where('r.user = :user')
				->setParameter('user', $user)					
				->groupBy('h.name')
				->getQuery();
		$hoofdingredienten = $query->getResult();		
    	
        return $this->render('recipes/recepten.html.twig', array(
			'recepten' => $recepten, 
			'gerechten' => $gerechten,
			'keukens' => $keukens,
			'hoofdingredienten' => $hoofdingredienten,
        ));
    }
    
    /**
     * @Route("new", name="newrecept")
     */
    public function newAction(Request $request)
	{
    	$recept = new Recept();
    	
    	$form = $this->createForm(ReceptType::class, $recept, array(
    		'action' => $this->generateUrl('createrecept'),
    		'method' => 'POST',
		));
	
        return $this->render('recipes/new.html.twig', array(
            'form' => $form->createView(),
        ));	
	}
	
	/**
	 * Creates or updates a Recept entity.
	 *
	 * @Route("create", name="createrecept")
	 */
	public function createAction (Request $request) {
		
// 		Dump request to see POST values
// 		return new JsonResponse(array('request'=>dump($request->request->all())),400);
		
// 		if (!$request->isXmlHttpRequest()) {
// 			return new JsonResponse(array('message' => 'You can access this only using Ajax!'), 400);
// 		}

		// Get 'id' field value from form
		$id = $request->request->get('recept')['id'];
		
		if(!null == $id){
			$recept = $this->getDoctrine()->getRepository('AppBundle:Recept')->find($id);
		} else {
		$recept = new Recept();
		}
		
		$originalIngredients = new ArrayCollection();

		// Create an ArrayCollection of the current Ingredient objects in the database
		foreach ($recept->getIngredienten() as $ingredient) {
			$originalIngredients->add($ingredient);
		}	

    	$form = $this->createForm(ReceptType::class, $recept, array(
    		'action' => $this->generateUrl('createrecept'),
    		'method' => 'POST',
		));
    	
    	$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
			$recept = $form->getData();
			$em = $this->getDoctrine()->getManager();
			
			// set default value for gerecht property ('hoofdgerecht')
			if($recept->getGerecht() == null){
				$gerecht = $this->getDoctrine()->getRepository('AppBundle:Gerecht')->findOneByName('Hoofdgerecht');
				$recept->setGerecht($gerecht);
			}
			// set default value for personen
			if($recept->getPersonen() == null){
				$recept->setPersonen(4);
			}
			foreach ($originalIngredients as $ingredient) {
				if (false === $recept->getIngredienten()->contains($ingredient)) {
					$em->remove($ingredient);
				}
			}
			$user = $this->getUser();
			$recept->setUser($user);
			
        	$em->persist($recept);
        	$em->flush();
        			
			return new JsonResponse(array('message' => 'Success!'), 200);
			
			$this->addFlash(
            	'notice',
            	'Het recept '.$recept->getTitel().' werd aangemaakt');
		}
		
		$response = new JsonResponse(
            array(
        		'message' => 'Error',
        		'form' => $this->renderView($id==null ? 'recipes/new.html.twig' : 'recipes/edit.html.twig', array(
        			'form' => $form->createView(),
        			'recept' => $recept,
        			)
        		)
        	), 400);
 
		return $response;
		
	}

	
	/**
	 * Show a recipe
	 *
	 * @Route("recept/{id}", name="showrecept")
	 */
	public function showAction($id) {
		$recept = $this->getDoctrine()
			->getRepository('AppBundle:Recept')
			->find($id);
			
		if (!$recept) {
			throw $this->createNotFoundException(
				'No recipe found for id '.$id
			);
		}
		
		return $this->render('recipes/show.html.twig', array(
			'recept' => $recept
		));
	}
	
    /**
     * @Route("edit/{id}", name="editform")
     */
    public function editAction($id, Request $request)
	{
		$recept = $this->getDoctrine()
			->getRepository('AppBundle:Recept')
			->find($id);
		
		if (!$recept) {
			throw $this->createNotFoundException(
				'No recipe found for id '.$id
			);			
		}
			
    	$form = $this->createForm(ReceptType::class, $recept, array(
    		'action' => $this->generateUrl('createrecept'),
    		'method' => 'POST',
		));
		
		// Store id in hidden form field for later use
		$form->get('id')->setData($id);
	
        return $this->render('recipes/edit.html.twig', array(
            'form' => $form->createView(),
            'recept' => $recept,
        ));	
	}	
	
	/**
	 * Delete a recipe
	 *
	 * @Route("delete/{id}", name="deleterecept")
	 */
	public function deleteAction($id, Request $request) {
	
		$em = $this->getDoctrine()->getManager();
		$recept = $em->getRepository('AppBundle:Recept')
			->find($id);
			
		if (!$recept) {
			throw $this->createNotFoundException(
				'No recipe found for id '.$id
			);
		}
		
		$ingredients = new ArrayCollection();

		// Create an ArrayCollection of the current Ingredient objects in the database
		foreach ($recept->getIngredienten() as $ingredient) {
			$ingredients->add($ingredient);
		}		

		$defaultData = array();
		$form = $this->createFormBuilder($defaultData, array(
				'action' => $this->generateUrl('deleterecept', array('id'=>$id)),
				'method' => 'POST',
			))
			->add('submit', SubmitType::class, array('label' => 'Wis'))
			->add('cancel', SubmitType::class, array('label' => 'Annuleer'))
			->getForm();
			
		$form->handleRequest($request);		

		if ($form->isSubmitted() && $form->isValid()) {

			if ($form->get('submit')->isClicked()) {

				// delete ingredients from database
				foreach ($ingredients as $ingredient) {
						$em->remove($ingredient);
				}				
			
				// delete Recept from database
				$em->remove($recept);
				$em->flush();
			}
		
			return $this->redirectToRoute('recipes');
		}			
		
		return $this->render('recipes/delete.html.twig', array(
			'form' => $form->createView(),
			'recept' => $recept
		));
	}
	
	/**
	 * Query database for tags
	 *
	 * @Route("findtag", name="findtag")
	 */	
	public function findTagAction(Request $request){
	
		$querystring = $request->query->get('q');
		
		$repository = $this->getDoctrine()->getRepository('AppBundle:Tag');

		$query = $repository->createQueryBuilder('p')
			->where('p.name LIKE :querystring')
			->setParameter('querystring', '%'.$querystring.'%')
			->orderBy('p.name', 'ASC')
			->getQuery();		
		
		$tags = $query->getResult();
		
		$results = array();
		
		foreach ($tags as $tag){
			$results[] = array('id' => $tag->getId(), 'text' => $tag->getName());
		}
		
		return new JsonResponse($results);
	}
	
	
}