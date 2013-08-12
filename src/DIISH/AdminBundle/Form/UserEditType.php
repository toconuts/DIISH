<?php

namespace DIISH\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * UserRegistrationType,
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class UserEditType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'text', array(
                    'read_only' => true,
        ));
        $builder->add('displayname');
        $builder->add('username', 'text', array(
                    'read_only' => true,
        ));
        $builder->add('email', 'repeated', array(
                    'type'             => 'email',
                    'invalid_message' => 'Enter the same address',
        ));
        $builder->add('isActive', 'checkbox', array(
                    'value'    => true,
                    'required' => false,
        ));
        $builder->add('isLock', 'checkbox', array(
                    'value'    => true,
                    'required' => false,
        ));
        $builder->add('groups');
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'user_edit';
    }

}
