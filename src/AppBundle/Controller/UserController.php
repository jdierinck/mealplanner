<?php 

namespace AppBundle\Controller;

use AppBundle\Form\UserType;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Entity\Tag;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use AppBundle\Form\Type\TagType;
use AppBundle\Form\Type\UserEditType;
use Doctrine\Common\Collections\ArrayCollection;

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

            // Set default tags
            $defaultTags = array(
                'goedkoop',
                'glutenvrij',
                'lactosevrij',
                'makkelijk',
                'snel',
                'vegetarisch',
                'kinderfavoriet',
            );
            foreach ($defaultTags as $tagName) {
                $tag = new Tag();
                $tag->setName($tagName);
                $tag->setUser($user);
                $user->addTag($tag);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // Create a new token with the user entity and pass it into the security context
            // so the user is automatically authenticated and doesn't need to log in after registration
            $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
            $this->get('security.token_storage')->setToken($token);
            $this->get('session')->set('_security_main', serialize($token));

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

    /**
     * Edit tags
     *
     * @Route("/tags/edit", name="edittags")
     */ 
    public function editTagsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        $originalTags = new ArrayCollection();

        // Create an ArrayCollection of the current Tag objects in the database
        foreach ($user->getTags() as $tag) {
            $originalTags->add($tag);
        }

        $form = $this->createForm(UserEditType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $form->getData();

            foreach ($originalTags as $tag) {
                if (false === $user->getTags()->contains($tag)) {
                    $em->remove($tag);
                }
            }

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('recipes');
        }

        return $this->render('user/edittags.html.twig', array(
            'form' => $form->createView(),
        ));
    }    
}