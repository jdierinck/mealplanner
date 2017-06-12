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
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;

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

    /**
     * Request a password reset
     *
     * @Route("requestpwd", name="requestpwd")
     */
    public function requestPwdReset(Request $request)
    {

        $username = $request->request->get('username');

        if ($username) {
            $em = $this->getDoctrine()->getManager();
            $user = $em->getRepository('AppBundle:User')->findOneByUsername($username);

            if (!$user) {
                throw $this->createNotFoundException(
                    'Geen gebruiker gevonden met naam '.$username
                );
            }
            // Generate token
            $bytes = openssl_random_pseudo_bytes(16); // Generate secure random string
            $token = substr(base64_encode($bytes), 0, 16); // Encode value and trim it
            $token = str_replace(array('+','/','='), '', $token); // Make it url safe

            $user->setConfirmationToken($token);

            $user->setpasswordRequestedAt(new \DateTime());

            $em->flush();

            // Send e-mail
            $message = \Swift_Message::newInstance()
                ->setSubject('Mealplanner: Password reset')
                ->setFrom('johan.dierinck@telenet.be')
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
                        // appBundle/views/emails/contact.html.twig
                        'user/pwdresetmail.html.twig',
                        array('token' => $token)
                        ),
                        'text/html'
                    )                
            ;
            
            $this->get('mailer')->send($message);           
            
            // add flash message
            $this->addFlash(
                'notice',
                'Er werd een e-mail verstuurd naar '.$user->getEmail().'. Klik op de link in de mail om een nieuw wachtwoord in te stellen.');
        }

        return $this->render('user/request.html.twig');
    }

    /**
     * Reset user password
     * 
     * @Route("resetpwd/{token}", name="resetpwd")
     */
    public function resetPwdAction(Request $request, $token)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->findOneByConfirmationToken($token);

        if (null === $user) {
            throw new NotFoundHttpException(sprintf('The user with "confirmation token" does not exist for value "%s"', $token));
        }

        $ttl = 3600; // request is valid for 1 hour

        if ($user->isPasswordRequestNonExpired($ttl)) {
            $form = $this->createFormBuilder($user)
                ->add('plainPassword', RepeatedType::class, array(
                    'type' => PasswordType::class,
                    'first_options'  => array('label' => 'Wachtwoord'),
                    'second_options' => array('label' => 'Herhaal wachtwoord'),
                ))
                ->getForm();

            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $password = $this->get('security.password_encoder')
                    ->encodePassword($user, $user->getPlainPassword());
                $user->setPassword($password);
                $em->flush();
                return $this->redirectToRoute('recipes');
            }

            return $this->render('user/resetpwd.html.twig', array('form' => $form->createView()));

        } else {
            return new Response('Deze aanvraag is verlopen.');
        }


    }


}