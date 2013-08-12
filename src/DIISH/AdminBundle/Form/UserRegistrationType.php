<?php

namespace DIISH\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * UserRegistrationType,
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class UserRegistrationType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('displayname');
        $builder->add('username');
        $builder->add('email', 'repeated', array(
                    'type'             => 'email',
                    'invalid_message' => 'Enter the same address',
        ));
        $builder->add('rawPassword', 'password', array(
                    'always_empty' => false,
        ));
        $builder->add('isActive', 'checkbox', array(
                    'value'    => true,
                    'required' => false,
        ));
        $builder->add('isLock', 'checkbox', array(
                    'value'    => true,
                    'required' => false,
        ));
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'user_registration';
    }

}
