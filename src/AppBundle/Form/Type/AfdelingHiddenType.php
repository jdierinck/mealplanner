<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Entity\Afdeling;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Entity hidden custom type class definition
 */
class AfdelingHiddenType extends HiddenType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // attach the specified model transformer for this entity list field
        // this will convert data between object and string formats
        $builder->addModelTransformer(new CallbackTransformer(
                function ($afdeling) {
                    return $afdeling->getId();
                },
                function ($string) {
                    $afdeling = $this->em->getRepository('AppBundle:Afdeling')->find($string);
                    return $afdeling;
                }
            ))
        ; 
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Afdeling::class,
        ));
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return 'hidden';
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'afdelinghidden';
    }
}