<?php

namespace DIISH\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * UserRegistrationType,
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class GroupRegistrationType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name');
        $builder->add('role');
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'group_registration';
    }

}
