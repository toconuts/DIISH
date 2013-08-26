<?php

namespace DIISH\SComDisBundle\Form\Admin\Phase;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * PhaseRegistrationType,
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class PhaseRegistrationType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id');
        $builder->add('threshold');
        $builder->add('color');
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'phase_registration';
    }

}
