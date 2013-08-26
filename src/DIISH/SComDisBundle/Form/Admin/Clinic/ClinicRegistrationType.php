<?php

namespace DIISH\SComDisBundle\Form\Admin\Clinic;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * ClinicRegistrationType,
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class ClinicRegistrationType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id');
        $builder->add('name');
        $builder->add('code');
        $builder->add('sentinelSite');
        $builder->add('district');
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'clinic_registration';
    }

}
