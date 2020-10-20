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
use AppBundle\Form\Type\UserEditTagsType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\AfdelingOrdered;
use AppBundle\Form\Type\UserEditAfdelingenType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserController extends Controller
{
    /**
     * @Route("/registreer", name="registreer")
     */
    public function registerAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

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
                'makkelijk',
                'snel',
                'vegetarisch',
                'kinderfavoriet',
                'pikant',
                'lente/zomer',
                'herfst/winter',
                'omega 3',
                'feestgerecht',
            );
            $i = 0;
            foreach ($defaultTags as $tagName) {
                $tag = new Tag();
                $tag->setName($tagName);
                $tag->setUser($user);
                $tag->setPosition($i);
                $user->addTag($tag);
                $i++;
            }

            // Set default order of Afdelingen
            // First get all afdelingen ordered alphabetically
            $afdelingen = $em->getRepository('AppBundle:Afdeling')->findBy(array(),array('name' => 'ASC'));
            foreach ($afdelingen as $afdeling) {
                $ao = new AfdelingOrdered();
                $afdeling->addAfdelingenordered($ao);
                $user->addAfdelingenordered($ao);
            }
            // Note: no need to call $em->persist($ao) because of cascade={"persist"} on the associations

            $user->setAccount('FREE');

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

        $form = $this->createForm(UserEditTagsType::class, $user);

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
            $user = $em->getRepository('AppBundle:User')->loadUserByUserName($username); // query by username or e-mail address

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
                ->setFrom($this->getParameter('mailer_from'))
                ->setTo($user->getEmail())
                ->setBody(
                    $this->renderView(
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

    /**
     * Edit afdelingen
     *
     * @Route("/afdelingen/edit", name="editafdelingen")
     */ 
    public function editAfdelingenAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        $form = $this->createForm(UserEditAfdelingenType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $form->getData();

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('shoppingList');
        }

        return $this->render('user/editafdelingen.html.twig', array(
            'form' => $form->createView(),
        ));
    }


    /**
     * Restore default order of Afdelingen for a User
     * 
     * @Route("setafdelingen", name="setafdelingen")
     */
    public function setAfdelingenAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();

        // Remove existing order if any
        $previous_afdelingen = $user->getAfdelingenordered();
        if (count($previous_afdelingen) > 0) {
            foreach ($previous_afdelingen as $pa) {
                $user->removeAfdelingenordered($pa);
            }

            $em->flush();
        }

        // Set default order of Afdelingen
        // First get all afdelingen ordered alphabetically
        $afdelingen = $em->getRepository('AppBundle:Afdeling')->findBy(array(),array('name' => 'ASC'));
        foreach ($afdelingen as $afdeling) {
            $ao = new AfdelingOrdered();
            $afdeling->addAfdelingenordered($ao);
            $user->addAfdelingenordered($ao);
        }

        $em->flush();

        return new Response('De afdelingen werden opnieuw ingesteld!');
    }
}