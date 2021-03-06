<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Yaml\Yaml;

class DefaultController extends Controller
{

    /**
     * @Route("/", name="home")
     */
    public function indexAction(Request $request)
    {
		$appPath = $this->container->getParameter('kernel.root_dir');
		$sites_nl = Yaml::parse(file_get_contents($appPath . '/../src/AppBundle/Resources/sites_nl.yml'));
		$sites_en = Yaml::parse(file_get_contents($appPath . '/../src/AppBundle/Resources/sites_en.yml'));

    	return $this->render('default/index.html.twig', array('sites_nl' => $sites_nl, 'sites_en' => $sites_en));
	}

    /**
     * @Route("contact", name="contact")
     */
     public function contactAction(Request $request)
     {
		$user = $this->getUser();
		$from = $user ? $user->getEmail() : '';
		     
     	$defaultData = array();
     	$form = $this->get('form.factory')
     		->createNamedBuilder('contactform', FormType::class, $defaultData)
     		->setAction($this->generateUrl('contact'))
     		->setMethod('POST')
			->add('from', EmailType::class, array(
			'label' => 'Van:',
			'data' => $from,
			'constraints' => array(new NotBlank(), new Email()),
			))
			->add('subject', TextType::class, array(
			'label' => 'Onderwerp',
			'constraints' => new NotBlank(),
			))
			->add('message', TextareaType::class, array(
			'label' => 'Bericht',
			'attr' => array('rows' => 5, 'cols' => 50),
			'constraints' => new NotBlank(),
			))
			->add('send', SubmitType::class, array('label' => 'Verzend'))
			->getForm();
			
		$form->handleRequest($request);
		
		if ($form->isSubmitted() && $form->isValid()) {
			$data = $form->getData();

			$message = \Swift_Message::newInstance()
				->setSubject($data['subject'])
				->setFrom($data['from'])
				->setTo('johan.dierinck@gmail.com')
				->setBody($data['message'])				
			;
			
			$this->get('mailer')->send($message);
        			
			return new JsonResponse(array('message' => 'Bedankt voor je bericht!'), 200); 

		}
		elseif ($form->isSubmitted() && !$form->isValid()) {
			$response = new JsonResponse(
				array(
					'message' => 'Er is een fout opgetreden',
					'form' => $this->renderView('default/contactform.html.twig', array(
						'form' => $form->createView()
						)
					)
				), 400);
 
			return $response;
		}
		
		return $this->render('default/contactform.html.twig', array('form' => $form->createView()));			     		
     }

    /**
     * @Route("about", name="about")
     */
     public function aboutAction(Request $request)
     {
     	return $this->render('default/about.html.twig');
     }

    /**
     * @Route("support", name="support")
     */
     public function supportAction(Request $request)
     {
		$appPath = $this->container->getParameter('kernel.root_dir');
		$sites_nl = Yaml::parse(file_get_contents($appPath . '/../src/AppBundle/Resources/sites_nl.yml'));
		$sites_en = Yaml::parse(file_get_contents($appPath . '/../src/AppBundle/Resources/sites_en.yml'));

     	return $this->render('default/support.html.twig', array('sites_nl' => $sites_nl, 'sites_en' => $sites_en));
     }

    /**
     * @Route("whatsnew", name="whatsnew")
     */
     public function whatsnewAction(Request $request)
     {

     	return $this->render('default/whatsnew.html.twig');
     }
}