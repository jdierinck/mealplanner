<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use AppBundle\Entity\Ingredient;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Yaml\Yaml;

class IngredientType extends AbstractType
{
    /**
     * @var EntityManager 
     */
    protected $em;

	public function __construct(EntityManager $entityManager) {
		$this->em = $entityManager;
	}

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        	'data_class' => Ingredient::class,
        ));
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

    	
        $listener = function (FormEvent $event) {
        	$ingredient = $event->getData();
			$form = $event->getForm();
            	
			$repo = $this->em->getRepository('AppBundle:Afdeling');
			$afdelingen = $repo->findAll();
			$onbekend = $repo->findOneByName('niet toegewezen');
		
			foreach($afdelingen as $afdeling){
				$voedingswaren = explode("\n",str_replace("\r", '', $afdeling->getVoedingswaren()));
				if(in_array($ingredient['ingredient'],$voedingswaren)){
					$ingredient['afdeling'] = $afdeling;
				}
				$event->setData($ingredient);
			}

		};
		
		$listener2 = function (FormEvent $event) {
    		$ingredient = $event->getData();
    		$repo = $this->em->getRepository('AppBundle:Afdeling');
			$onbekend = $repo->findOneByName('niet toegewezen');
    		if(!array_key_exists('afdeling', $ingredient)) {
    			$ingredient['afdeling'] = $onbekend;
    			$event->setData($ingredient);
    		}
    	};
    	
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
    			'label' => false,
    			'attr' => array('class' => 'input-sm'),
    			));
    	
    	$builder->addEventListener(Formevents::PRE_SUBMIT, $listener);
    	$builder->addEventListener(Formevents::PRE_SUBMIT, $listener2);

    }

}