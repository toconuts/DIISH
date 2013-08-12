<?php

namespace DIISH\SComDisBundle\Form\Admin\District;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * DistrictRegistrationType,
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class DistrictRegistrationType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id');
        $builder->add('name');
        $builder->add('population');
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'district_registration';
    }

}
