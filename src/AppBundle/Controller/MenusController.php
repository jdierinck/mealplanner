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

class MenusController extends Controller
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
// 			$session->set('boodschappenlijst_id', $boodschappenlijst->getId());
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
    
//     	$repository = $this->getDoctrine()->getRepository('AppBundle:Menu');
//     	$menus = $repository->findAll();
    	
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
		
		$previousRecepten = new ArrayCollection();
		foreach($menu->getReceptenordered() as $ro){
			
			$previousRecepten[] = $ro;
		}
		
		$form = $this->createForm(MenuType::class, $menu, array(
			'action' => $this->generateUrl('editmenu', array('id'=>$menu->getId())),
			'method' => 'POST',
		));

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {

			foreach($previousRecepten as $p){
				$menu->removeReceptenordered($p);
				$em->remove($p);
				$em->flush();
			}

			$menu = $form->getData();
			
			$em->persist($menu);

			$em->flush();
			
			if ($request->isXmlHttpRequest()) { 
				return new JsonResponse(array('message' => 'Success!'), 200); 
			} else {
			return $this->redirectToRoute('menus');
			}
		}
		
		if ($request->isXmlHttpRequest()) {
			$response = new JsonResponse(
				array(
					'message' => 'Error',
					'form' => $this->renderView('menus/newmenu.html.twig', array(
						'form' => $form->createView(),
						'menu' => $menu,
						)
					)
				), 400);
 
			return $response;
		} else {
			return $this->render('menus/newmenu.html.twig', array(
				'form' => $form->createView(),
			));	
		}	
	
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
			->add('submit', SubmitType::class, array('label' => 'Wis'))
			->add('cancel', SubmitType::class, array('label' => 'Annuleer'))
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
	
		$querystring = $request->query->get('q');
		
		$repository = $this->getDoctrine()->getRepository('AppBundle:Recept');

		$query = $repository->createQueryBuilder('p')
			->where('p.titel LIKE :querystring')
			->setParameter('querystring', '%'.$querystring.'%')
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
		
// 		$qb = $em->createQueryBuilder();
// 		$query = $qb->select('a','i')
// 			->from('AppBundle:Afdeling','a')
// 			->join('a.ingredienten', 'i')
// 			->join('i.recept', 'r')
// 			->join('r.menus', 'm')
// 			->where('m.id = :menuid')
// // 			->setParameter('menuid', $menu->getId())
// 			->setParameter('menuid', $id)			
// 			->orderBy('a.name')
// 			->getQuery();
// 		$afdelingen = $query->getResult();
// 		dump($afdelingen);
		
		$afdelingen = $em->getRepository('AppBundle:Menu')
			->findIngredientenByMenu($id);
		
		return $this->render('menus/showlist.html.twig', array('afdelingen' => $afdelingen));		
	}
	
	/**
	 * @Route("add/menu/{id}", name="addmenutoshoppinglist")
	 */
	public function addToShoppingListAction(Request $request, $id){
		
		$user = $this->getUser();
		
// 		$session = $this->get('session');
		
		$em = $this->getDoctrine()->getManager();
		$repository = $em->getRepository('AppBundle:Menu');
		$menu = $repository->find($id);

// 		$boodschappenlijst = $em->getRepository('AppBundle:Boodschappenlijst')
// 				->find($session->get('boodschappenlijst_id'));

		$boodschappenlijst = $user->getBoodschappenlijst();
		
		$recepten = new ArrayCollection();
		foreach($menu->getRecepten() as $recept){
			foreach($recept->getIngredienten() as $ingredient){
// 				$boodschappenlijst->addIngredienten($ingredient);
				$ingredient->setBoodschappenlijst($boodschappenlijst);
				$em->persist($ingredient);
			}
			$recepten[] = $recept;
		}
		$boodschappenlijst->setRecepten($recepten);
		
		$em->flush();
		
		return $this->render('menus/addedtoshoppinglist.html.twig', array('menu_naam' => $menu->getNaam()));
	}
  
}
