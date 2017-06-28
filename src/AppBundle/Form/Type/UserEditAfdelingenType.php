<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Form\Type\AfdelingenType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class UserEditAfdelingenType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('afdelingenordered', CollectionType::class, array(
                'label' => 'Stel volgorde afdelingen in',
                'required' => false,
                'entry_type' => AfdelingenType::class,
                'entry_options' => array(
                   'label' => 'Naam',
                    ),
                'allow_add' => false,
                'allow_delete' => false,
                // 'by_reference' => false,
                'prototype' => true,
                'attr' => array('class' => 'afdelingcollection'),
                ))
            // ->add('submit', SubmitType::class, array('label' => 'Bewaar'))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => User::class,
            'validation_groups' => array('edit')
        ));
    }
}