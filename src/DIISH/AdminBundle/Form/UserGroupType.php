<?php

namespace DIISH\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * UserGroupType,
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class UserGroupType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('groups');
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'user_group';
    }
}
