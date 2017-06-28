<?php

namespace AppBundle\Form\Type;

use AppBundle\Entity\AfdelingOrdered;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\CallbackTransformer;
use Doctrine\ORM\EntityManagerInterface;

class AfdelingenType extends AbstractType
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // $builder->add('name', TextType::class, array(
        //     'label' => false,
        //     )
        // );
        $builder->add('afdeling', TextType::class, array(
            'label' => false,
            'attr' => array('readonly' => 'readonly')
            )
        );

        $builder->get('afdeling')
            ->addModelTransformer(new CallbackTransformer(
                function ($afdeling) {
                    return $afdeling->getName();
                },
                function ($string) {
                    $afdeling = $this->em->getRepository('AppBundle:Afdeling')->findOneByName($string);
                    return $afdeling;
                }
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => AfdelingOrdered::class,
        ));
    }
}