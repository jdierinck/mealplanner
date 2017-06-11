<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\Menu;
use AppBundle\Form\Type\DagType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\SecurityContext;

/** @DI\Service("app.form.type.menu") */
class MenuType extends AbstractType
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContext
     */
	protected $securityContext;
	
	/**
     * @DI\InjectParams({"securityContext" = @DI\Inject("security.context")})
     *
     * @param \Symfony\Component\Security\Core\SecurityContext $context
     */
    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }
	
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    	$user = $this->securityContext->getToken()->getUser();
    	 
        $builder->add('naam', TextType::class)
    //     	->add('recepten', CollectionType::class, array(
    //     	'required' => false,
    //         'entry_type' => EntityType::class,
    //         'entry_options' => array(
    //         	'class' => 'AppBundle:Recept',
    //         	'choice_label' => 'titel',
    //         	'query_builder' => function(EntityRepository $e) use($user){
    //         		return $e->createQueryBuilder('r')
    //         			->where('r.user = :user')
    //         			->setParameter('user', $user);
    //         	}
    //         	),
			 // 'allow_add' => true,
			 // 'allow_delete' => true,
    //          'prototype' => true,
    //          'attr' => array('class' => 'recipecollection'),
    //     	))
            ->add('dagen', CollectionType::class, array(
            'required' => false,
            'entry_type' => DagType::class,
             'allow_add' => true,
             'allow_delete' => true,
             'prototype' => true,
             'attr' => array('class' => 'daycollection'),
             'by_reference' => false,
            ))            
    //     	->add('dagen', Select2EntityType::class, array(
    //     		'multiple' => true,
    //     		'required' => false,
				// 'class' => 'AppBundle:Recept',
				// 'remote_route' => 'findrecept',
				// 'text_property' => 'titel',
				// 'allow_clear' => true,
				// 'placeholder' => 'Selecteer een recept',
				// 'minimum_input_length' => 0,
				// 'language' => 'nl',
				// 'width' => '100%',
				// ))
        	// ->add('bewaar', SubmitType::class)
            ;
        	       
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Menu::class,
        ));
    }

}