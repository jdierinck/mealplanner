<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Form\Type\TagType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class UserEditTagsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('tags', CollectionType::class, array(
                'label' => 'Beheer tags',
                'required' => false,
                'entry_type' => TagType::class,
                'entry_options' => array(
                   'label' => 'Naam',
                    ),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
                'attr' => array('class' => 'tagcollection'),
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

    public function getBlockPrefix()
    {
        return 'UserEditTagsType';
    }
}