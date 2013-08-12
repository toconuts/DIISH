<?php

namespace DIISH\SComDisBundle\Form\Admin\PMH;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * PMHRegistrationType,
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class PMHRegistrationType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('clinic');
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'pmh_registration';
    }

}
