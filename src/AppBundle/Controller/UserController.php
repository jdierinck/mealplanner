<?php 

namespace AppBundle\Controller;

use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    /**
     * @Route("/registreer", name="registreer")
     */
    public function registerAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('recipes');
        }

        return $this->render(
            'user/register.html.twig',
            array('form' => $form->createView())
        );
    }
    
    /**
     * @Route("/profiel", name="profiel")
     */
    public function profileAction(Request $request)
    {	
    	if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
        	throw $this->createAccessDeniedException();
    	}
    	$user = $this->getUser();
    	
    	return $this->render('user/profiel.html.twig', array('user' => $user));
    }
}