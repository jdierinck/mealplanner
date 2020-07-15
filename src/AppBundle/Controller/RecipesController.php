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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Ingredient;
use AppBundle\Entity\Boodschappenlijst;
use RecipeParser\RecipeParser;

class RecipesController extends Controller
{
    /**
     * @Route("/recepten", name="recipes")
     */
    public function indexAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
    	
		// $session = $request->getSession();
		
		if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
			throw $this->createAccessDeniedException();
		}

		// Get user (long version)
		// $user = $this->get('security.token_storage')->getToken()->getUser();
		$user = $this->getUser();
		$boodschappenlijst = $user->getBoodschappenlijst();
	
		// Create Boodschappenlijst if none exists
		if (!$boodschappenlijst) {
			$boodschappenlijst = new Boodschappenlijst();
			$boodschappenlijst->setUser($user);
			
			$em->persist($boodschappenlijst);
			$em->flush();
		}

    	$allowedsorts = array(
    		'titel', 
    		'gerecht', 
    		'keuken', 
    		'hoofdingredient', 
    		'bereidingstijd', 
    		'toegevoegdOp', 
    		'kostprijs',
    		);

    	$sort = $request->query->get('sortBy');
    	if (null == $sort || !in_array($sort, $allowedsorts)) { 
    		$sort = 'toegevoegdOp'; 
    	}
    	
    	$gerecht = $request->query->get('gerecht');
    	$keuken = $request->query->get('keuken');
    	$hoofdingredient = $request->query->get('hoofdingredient');
    	$bereidingstijd = $request->query->get('bereidingstijd');
    	$zoek = $request->query->get('zoek');
    	// $metIngr = $request->query->get('met');
    	$zonderIngr = $request->query->get('zonder');
    	$tags = $request->query->get('tag');
    	
    	$repository = $this->getDoctrine()->getRepository('AppBundle:Recept');

    	$qb = $repository->createQueryBuilder('r', 'r.id') // index by id
    		->addSelect('i')
    		->leftJoin('r.ingredienten', 'i')
    		->where('r.user = :user')
    		->setParameter('user', $user)
    		->orderBy('r.'.$sort, 'DESC');
    		
    	if ($gerecht) {
    		$qb->andWhere('r.gerecht IN (:gerecht)')
    			->setParameter('gerecht', $gerecht);
    	}
    	
    	if ($keuken) {
    		$qb->andWhere('r.keuken IN (:keuken)')
    			->setParameter('keuken', $keuken);
    	}
    	
    	if ($hoofdingredient) {
    		$qb->andWhere('r.hoofdingredient IN (:hoofdingredient)')
    			->setParameter('hoofdingredient', $hoofdingredient);
    	}

    	if ($tags) {
    		$qb->join('r.tags', 't')
    			->addSelect('t')
    			->andWhere('t.id IN (:tags)')
    			->setParameter('tags', $tags);
    	}
    	
    	if ($bereidingstijd) {

    		$subquery = [];

    		if (in_array('030', $bereidingstijd)) {
    			$subquery[] = 'r.bereidingstijd != \'\' AND r.bereidingstijd <= :halfhour';
    			$qb->setParameter('halfhour', '00:30:00');
    		}
    		if (in_array('3060', $bereidingstijd)) {
    			$subquery[] = '(r.bereidingstijd > :starttijd AND r.bereidingstijd <= :eindtijd)';
				$qb->setParameter('starttijd', '00:30:00')
					->setParameter('eindtijd', '00:60:00');   			
    		}
    		if (in_array('60', $bereidingstijd)) {
    			$subquery[] = 'r.bereidingstijd > :hour';
				$qb->setParameter('hour', '00:60:00');
    		}

    		$subquery = implode(' OR ', $subquery);

    		$qb->andWhere($subquery);

    	}

    	if ($zonderIngr) {
    		$qb2 = $this->getDoctrine()->getManager()->createQueryBuilder();
    		$ids = $qb2->select('r')
    			->from('AppBundle:Recept', 'r', 'r.id')
    			->where('r.user = :user')
    			->setParameter('user', $user)
    			->join('r.ingredienten','i')
    			->andWhere('i.ingredient LIKE :zonder')
    			->setParameter('zonder', '%' . $zonderIngr . '%')
    			->getQuery()
    			->getResult();
  			
    		$ids = array_keys($ids);

    		if ($ids) {
    			$qb
    			->andWhere($qb->expr()->notIn('r.id', $ids));
    		}
    	}
    	
    	if ($zoek) {
    		$qb->andWhere('r.titel LIKE :zoek OR i.ingredient LIKE :zoek')
    			->setParameter('zoek', '%'.$zoek.'%');
    	}
    	
    	// Get unpaginated array of recipes	
    	$results = $qb->getQuery()->getResult();
    	
    	// Paginate search results
    	$query = $qb->getQuery();
    	$paginator = $this->get('knp_paginator');
    	
    	$recepten = $paginator->paginate(
    		$query,
    		$request->query->getInt('page',1),
    		$request->query->getInt('limit',12)
    	);
    	$total = $recepten->getTotalItemCount();

		$repository = $this->getDoctrine()->getRepository('AppBundle:Gerecht');
		$gerechten = $repository->findAllByUser($user);

		$repository = $this->getDoctrine()->getRepository('AppBundle:Keuken');
		$keukens = $repository->findAllByUser($user);
		
		$repository = $this->getDoctrine()->getRepository('AppBundle:Hoofdingredient');
		$hoofdingredienten = $repository->findAllByUser($user);

		$repository = $this->getDoctrine()->getRepository('AppBundle:Tag');
		$tags = $repository->findAllByUser($user);

		$intervals = [
			'030' => '< 30 min',
			'3060' => '30 min - 1 u',
			'60' => '> 1 u',
		];

        return $this->render('recipes/recepten.html.twig', array(
			'recepten' => $recepten,
			'gerechten' => $gerechten,
			'keukens' => $keukens,
			'hoofdingredienten' => $hoofdingredienten,
			'total' => $total,
			'intervals' => $intervals,
			'tags' => $tags,
        ));
    }
    
    /**
     * @Route("new", name="newrecept")
     */
    public function newAction(Request $request)
	{
    	$recept = new Recept();
    	// set default value for yield and yieldtype
    	$recept->setYield(4);
    	$type = $this->getDoctrine()->getRepository('AppBundle:YieldType')->findOneByUnitPlural('personen');
    	$recept->setYieldType($type);
    	
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
	public function createAction (Request $request)
	{
		// Get 'id' field value from form
		$id = $request->request->get('recept')['id'];
		
		if (!null == $id) {
			$recept = $this->getDoctrine()->getRepository('AppBundle:Recept')->find($id);
		} else {
			$recept = new Recept();
		}
		
		// Create an ArrayCollection of the current Ingredient objects in the database
		$originalIngredients = new ArrayCollection();
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
			if ($recept->getGerecht() == null) {
				$gerecht = $this->getDoctrine()->getRepository('AppBundle:Gerecht')->findOneByName('Hoofdgerecht');
				$recept->setGerecht($gerecht);
			}

			foreach ($originalIngredients as $ingredient) {
				if (false === $recept->getIngredienten()->contains($ingredient)) {
					$em->remove($ingredient);
				}
			}

			$user = $this->getUser();
			$recept->setUser($user);

			// Assign department to each ingredient if ingredient is new and not a section
			foreach ($recept->getIngredienten() as $i) {
				if (null === $i->getAfdeling() && false === $i->isSection()) {
					$finder = $this->container->get('app.dept_finder');
					$dept = $finder->findDept($i->getIngredient());
					$i->setAfdeling($dept);
				}
			}

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
        		'form' => $this->renderView($id == null ? 'recipes/new.html.twig' : 'recipes/edit.html.twig', array(
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
	public function showAction($id, Request $request) 
	{
		$recept = $this->getDoctrine()
			->getRepository('AppBundle:Recept')
			->find($id);
			
		if (!$recept) {
			throw $this->createNotFoundException(
				'No recipe found for id '.$id
			);
		}

		$p = $request->query->get('p');

		$servings = (!null == $p) ? $p : $recept->getYield();

		return $this->render('recipes/show.html.twig', array(
			'recept' => $recept,
			'servings' => $servings,
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
	public function deleteAction($id, Request $request) 
	{
	
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
				'action' => $this->generateUrl('deleterecept', array('id' => $id, 'page' => $request->query->get('page'))),
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

				$this->addFlash(
	            	'notice',
	            	'Het recept <em>' . $recept->getTitel() . '</em> werd verwijderd.'
	            );
			}		
		
			return $this->redirectToRoute('recipes', $request->query->all());
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
	public function findTagAction(Request $request)
	{
		$user = $this->getUser();

		$querystring = $request->query->get('q');
		
		$repository = $this->getDoctrine()->getRepository('AppBundle:Tag');

		$query = $repository->createQueryBuilder('p')
			->where('p.name LIKE :querystring')
			->andWhere('p.user = :user')
			->setParameter('querystring', '%'.$querystring.'%')
			->setParameter('user', $user)
			->orderBy('p.name', 'ASC')
			->getQuery();		
		
		$tags = $query->getResult();
		
		$results = array();
		
		foreach ($tags as $tag){
			$results[] = array('id' => $tag->getId(), 'text' => $tag->getName());
		}
		
		return new JsonResponse($results);
	}

	/**
	 * Query database for keuken
	 *
	 * @Route("findkeuken", name="findkeuken")
	 */	
	public function findKeukenAction(Request $request)
	{

		$querystring = $request->query->get('q');
		
		$repository = $this->getDoctrine()->getRepository('AppBundle:Keuken');

		$query = $repository->createQueryBuilder('p')
			->where('p.name LIKE :querystring')
			->setParameter('querystring', '%'.$querystring.'%')
			->orderBy('p. regio', 'ASC')
			->addOrderBy('p. name', 'ASC')
			// ->groupBy('p.regio')
			->getQuery();		
		
		$keukens = $query->getResult();

		$results = array();
		$regios = array();
		
		foreach ($keukens as $keuken){
			$regios[] = $keuken->getRegio();
		}
		$regios = array_values(array_unique($regios));
		
		foreach ($regios as $regio) {
			foreach ($keukens as $keuken) {
				if ($keuken->getRegio() === $regio) {
					$results[$regio][] = array('id' => $keuken->getId(), 'text'=> $keuken->getName());
				}
			}
		}

		$finalresult = array();

		foreach($results as $key=>$values){
			$finalresult[] = array('text' => $key, 'children' => $values);
		}

		return new JsonResponse($finalresult);
	}
	
}