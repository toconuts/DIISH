<?php

namespace DIISH\SComDisBundle\Form\Admin\Syndrome4Surveillance;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Syndrome4SurveillanceRegistrationType,
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class Syndrome4SurveillanceRegistrationType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id');
        $builder->add('name');
        $builder->add('displayId');
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'syndrome4surveillance_registration';
    }

}
