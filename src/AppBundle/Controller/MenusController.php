<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Menu;
use AppBundle\Entity\Recept;
use AppBundle\Form\Type\MenuType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Entity\Boodschappenlijst;
use AppBundle\Entity\ReceptBLOrdered;
use AppBundle\Controller\AccountNonExpiredController;

class MenusController extends Controller implements AccountNonExpiredController
{
    
    /**
     * @Route("menus", name="menus")
     */
    public function menusAction(Request $request)
    {
    	$em = $this->getDoctrine()->getManager();
    	
		$session = $request->getSession();
		
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
		}
		    
    	$menu = new Menu();
		
		$form = $this->createForm(MenuType::class, $menu);
		
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$menu = $form->getData();
			$menu->setUser($user);

			$em->persist($menu);
			$em->flush();

			return $this->redirectToRoute('menus');
		}
    	
    	$qb = $em->createQueryBuilder();
    	$query = $qb->select('m')
    			->from('AppBundle:Menu','m')
    			->where('m.user = :user')
    			->setParameter('user', $user)
    			->getQuery();
    	$menus = $query->getResult();
    	
        return $this->render('menus/menus.html.twig', array('menus' => $menus, 'form' => $form->createView()));
    }
    
	/**
	 * @Route("menu/new", name="newmenu")
	 */
	public function newMenuAction(Request $request){
	
		if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
			throw $this->createAccessDeniedException();
		}
		$user = $this->getUser();
		
		$menu = new Menu();
		
		$form = $this->createForm(MenuType::class, $menu);
		
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			$menu = $form->getData();
			$menu->setUser($user);

			$em = $this->getDoctrine()->getManager();
			$em->persist($menu);
			$em->flush();

			return $this->redirectToRoute('menus');
		}

		return $this->render('menus/newmenu.html.twig', array(
			'form' => $form->createView(),
		));		
	
	}
	
	/**
	 * @Route("menu/edit/{id}", name="editmenu")
	 */
	public function editMenuAction(Request $request, $id){
		
		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('AppBundle:Menu');
    	$menu = $repository->find($id);

    	if (!$menu) {
			throw $this->createNotFoundException(
				'No menu found for id '.$id
			);			
		}

		// Create an ArrayCollection of the current Dag objects in the database
		$previousDagen = new ArrayCollection();
		foreach ($menu->getDagen() as $dag){
			$previousDagen[] = $dag;
		}
		
		$form = $this->createForm(MenuType::class, $menu, array(
			'action' => $this->generateUrl('editmenu', array('id'=>$menu->getId())),
			'method' => 'POST',
		));

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			 // remove the relationship between the Dag and the Menu
			foreach($previousDagen as $dag){
				if (false === $menu->getDagen()->contains($dag)) {
					$menu->removeDagen($dag);
					// delete Dag entirely
					// to just remove the relationship: $dag->setMenu(null);
					$em->remove($dag);
					// $em->flush();
				}
			}

			$menu = $form->getData();
			
			$em->persist($menu);

			$em->flush();
			
			return $this->redirectToRoute('menus');
		}
		
		return $this->render('menus/editmenu.html.twig', array(
				'form' => $form->createView(),
		));	
	
	}

	/**
	 * Delete a menu
	 *
	 * @Route("menu/delete/{id}", name="deletemenu")
	 */
	public function deleteMenuAction($id, Request $request) {
	
		$em = $this->getDoctrine()->getManager();
		$menu = $em->getRepository('AppBundle:Menu')
			->find($id);
			
		if (!$menu) {
			throw $this->createNotFoundException(
				'No menu found for id '.$id
			);
		}		

		$defaultData = array();
		$form = $this->createFormBuilder($defaultData, array(
				'action' => $this->generateUrl('deletemenu', array('id'=>$id)),
				'method' => 'POST',
			))
			->add('submit', SubmitType::class, array('label' => 'Wis', 'attr' => array('class' => 'btn btn-default', 'style' => 'float:left;')))
			->add('cancel', SubmitType::class, array('label' => 'Annuleer', 'attr' => array('class' => 'btn btn-default')))
			->getForm();
			
		$form->handleRequest($request);		

		if ($form->isSubmitted() && $form->isValid()) {

			if ($form->get('submit')->isClicked()) {				
				// delete Menu from database
				$em->remove($menu);
				$em->flush();
			}
		
			return $this->redirectToRoute('menus');
		}			
		
		return $this->render('menus/deletemenu.html.twig', array(
			'form' => $form->createView(),
			'menu' => $menu,
		));
	}	

	/**
	 * Query database for recepten
	 *
	 * @Route("findrecept", name="findrecept")
	 */	
	public function findReceptAction(Request $request){

		$user = $this->getUser();
	
		$querystring = $request->query->get('q');
		
		$repository = $this->getDoctrine()->getRepository('AppBundle:Recept');

		$query = $repository->createQueryBuilder('p')
			->where('p.titel LIKE :querystring')
			->setParameter('querystring', '%'.$querystring.'%')
			->andWhere('p.user = :user')
            ->setParameter('user', $user)
			->orderBy('p.titel', 'ASC')
			->getQuery();		
		
		$recepten = $query->getResult();
		
		$results = array();
		
		foreach ($recepten as $recept){
			$results[] = array('id' => $recept->getId(), 'text' => $recept->getTitel());
		}
		
		return new JsonResponse($results);
	}
	
	/**
	 * @Route("showlist/{id}", name="showlist")
	 */
	public function showShoppingListAction(Request $request, $id){
	
		$em = $this->getDoctrine()->getManager();
		
		$afdelingen = $em->getRepository('AppBundle:Menu')
			->findIngredientenByMenu($id);
		
		return $this->render('menus/showlist.html.twig', array('afdelingen' => $afdelingen));		
	}
	
	/**
	 * @Route("add/menu/{id}", name="addmenutoshoppinglist")
	 */
	public function addToShoppingListAction(Request $request, $id){
		
		$user = $this->getUser();
		
		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('AppBundle:Menu');
		$menu = $repository->find($id);

		$boodschappenlijst = $user->getBoodschappenlijst();
		
		$date = new \DateTime("now");
		$date->format("Y-m-d");

		foreach($menu->getDagen() as $dag){

			foreach ($dag->getRecepten() as $recept) {

				$ingredienten = array();
				
				foreach ($recept->getIngredienten() as $i) {
					if (false === $i->isSection()) {
						$ingredienten[] = $i;
					}
				}
			
				$ro = new ReceptBLOrdered();
				$ro->setBoodschappenlijst($boodschappenlijst);
				$ro->setRecept($recept);
				$ro->setServings(4);
				$ro->setDatum($date);
			
				$ro->setIngrBL($ingredienten);
			
				$em->persist($ro);
				$em->flush();
			}

			$date = $date->modify("+1 day");
		}
		// Note: do not flush at the end or else the last date will be set on each object
		// $em->flush();
		
		return $this->render('menus/addedtoshoppinglist.html.twig', array('menu_naam' => $menu->getNaam()));
	}
  
}
