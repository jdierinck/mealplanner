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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

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
    	$builder->add('hoeveelheid', NumberType::class, array(
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
          ->add('section', CheckboxType::class, array(
              'label' => false,
              'attr' => array('class' => 'hidden')
            ))
          ->add('afdeling', AfdelingHiddenType::class)
          ;

        $builder->get('afdeling')
            ->addModelTransformer(new AfdelingTransformer($this->om));
        
        //Modify options of field: hide fields hoeveelheid en eenheid when ingredient is section
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $ingredient = $event->getData();

            if (!$ingredient == null) {
              if ($ingredient->isSection()) {
                // Get configuration & options of specific field
                $config = $form->get('hoeveelheid')->getConfig();
                $options = $config->getOptions();

                $form->add(
                    // Replace original field... 
                    'hoeveelheid',
                    $config->getType()->getName(),
                    // while keeping the original options... 
                    array_replace(
                        $options, 
                        [
                            // replacing specific ones
                            'attr' => array('class' => 'input-sm hidden'),
                        ]
                    )
                );
                // Get configuration & options of specific field
                $config = $form->get('eenheid')->getConfig();
                $options = $config->getOptions();

                $form->add(
                    // Replace original field... 
                    'eenheid',
                    $config->getType()->getName(),
                    // while keeping the original options... 
                    array_replace(
                        $options, 
                        [
                            // replacing specific ones
                            'attr' => array('class' => 'input-sm hidden'),
                        ]
                    )
                );                
              }
            }
        });     

    }

}