<?php

namespace AppBundle\Form\DataTransformer;

use AppBundle\Entity\Afdeling;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;

class AfdelingTransformer implements DataTransformerInterface
{
    private $manager;

    public function __construct(ObjectManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Transforms an object (afdeling) to a string (id).
     *
     * @param  Afdeling|null $afdeling
     * @return string
     */
    public function transform($afdeling)
    {
        // Note: this seems to be necessary to avoid error 'trying to get id on null' (but why?)
        if (null === $afdeling) {
            return '';
        }

        return $afdeling->getId();
    }

    /**
     * Transforms a string (id) to an object (afdeling).
     *
     * @param  string $id
     * @return Afdeling|null
     */
    public function reverseTransform($id)
    {
        if (!$id) {
            return;
        }
        
        $afdeling = $this->manager
            ->getRepository(Afdeling::class)
            ->find($id)
        ;

        return $afdeling;
    }
}