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
            ->add('dagen', CollectionType::class, array(
                'required' => false,
                'entry_type' => DagType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'attr' => array('class' => 'daycollection'),
                'by_reference' => false,
            ))
            ;
        	       
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Menu::class,
        ));
    }

}