<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use AppBundle\Entity\Ingredient;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Form\CallbackTransformer;
use AppBundle\Form\Type\AfdelingHiddenType;
use Doctrine\Common\Persistence\ObjectManager;
use AppBundle\Form\DataTransformer\AfdelingTransformer;

class IngredientType extends AbstractType
{
    /**
     * @var ObjectManager 
     */
    protected $om;

	public function __construct(ObjectManager $objectManager) {
		$this->om = $objectManager;
	}

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        	'data_class' => Ingredient::class,
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

    	
  //       $listener = function (FormEvent $event) {
  //       	$ingredient = $event->getData();
		// 	$form = $event->getForm();

		// 	$repo = $this->em->getRepository('AppBundle:Afdeling');
		// 	$afdelingen = $repo->findAll();
		
		// 	foreach ($afdelingen as $afdeling) {
		// 		// $voedingswaren = explode("\n", str_replace("\r", '', $afdeling->getVoedingswaren()));
		// 		// if(in_array($ingredient['ingredient'], $voedingswaren)){
		// 		// 	$ingredient['afdeling'] = $afdeling;
		// 		// }

  //               if ($afdeling->getName() == 'niet toegewezen') continue;

  //               $voedingswaren = explode("\r\n", $afdeling->getVoedingswaren());
  //               // dump($voedingswaren);
  //               foreach ($voedingswaren as $v) {
  //                   // if (stripos($ingredient['ingredient'], $v) !== false) {
  //                   // if (preg_match('/'.$ingredient['ingredient'].'/', $v)) {
  //                   if (preg_match('/\b'.$v.'\b/i', $ingredient['ingredient'], $matches) === 1) {   
  //                       $ingredient['afdeling'] = $afdeling;
  //                       dump($ingredient, $matches);
  //                       $event->setData($ingredient);
  //                   }   
  //               }	
		// 	}
		// };
		
		// $listener2 = function (FormEvent $event) {
  //   		$ingredient = $event->getData();

  //   		$repo = $this->em->getRepository('AppBundle:Afdeling');
		// 	$onbekend = $repo->findOneByName('niet toegewezen');
            
  //   		if(!array_key_exists('afdeling', $ingredient)) {
  //   			$ingredient['afdeling'] = $onbekend;
  //   			$event->setData($ingredient);
  //   		}
  //   	};
    	
    	$builder->add('hoeveelheid', TextType::class, array(
        			'label' => false,
        			'attr' => array('class' => 'input-sm'),
    			))
    			->add('eenheid', TextType::class, array(
        			'label' => false,
        			'attr' => array('class' => 'input-sm'),
    			))
    			->add('ingredient', TextType::class, array(
                    'label' => false,
                    'attr' => array('class' => 'input-sm'),
    			))
    			->add('afdeling', TextType::class, array(
                    'attr' => array('class' => 'hidden'),
                ));
    	
    	// $builder->addEventListener(Formevents::PRE_SUBMIT, $listener);
    	// $builder->addEventListener(Formevents::PRE_SUBMIT, $listener2);               

        // Allow comma in form field instead of dot
        $builder->get('hoeveelheid')
            ->addModelTransformer(new CallbackTransformer(
                function($number){
                    if (null === $number) {
                        return;
                    } else {
                        // Get rid of trailing zeroes and decimal point in case of integers
                        $number += 0;
                        // Replace dots with commas
                        return str_replace('.', ',', $number);
                    }
                },
                function($input){
                    if (null === $input) {
                        return;
                    } else {
                        return str_replace(',', '.', $input);
                    }
                }
            ))
        ;

        $builder->get('afdeling')
            ->addModelTransformer(new AfdelingTransformer($this->om)); 

    }

}