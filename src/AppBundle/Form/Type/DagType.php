<?php
namespace AppBundle\Form\Type;

use AppBundle\Entity\Dag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;


class DagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('recepten', Select2EntityType::class, array(
            'label' => false,
            'multiple' => true,
            'required' => false,
            'class' => 'AppBundle:Recept',
            'remote_route' => 'findrecept',
            'text_property' => 'titel',
            'allow_clear' => true,
            'placeholder' => 'Selecteer een recept',
            'minimum_input_length' => 0,
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Dag::class,
        ));
    }
}