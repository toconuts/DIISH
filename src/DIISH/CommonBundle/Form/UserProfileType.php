<?php

namespace DIISH\CommonBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * UserProfileType,
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class UserProfileType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('displayname');
        $builder->add('username', 'text', array(
            'read_only' => true,
        ));
        $builder->add('email', 'repeated', array(
                'type' => 'email',
                'invalid_message' => 'Enter the same address',
        ));
        $builder->add('changePassword', 'checkbox', array(
                'required' => false,
                //'property_path' => false,
                'mapped' => false,
        ));
        $builder->add('rawPassword', 'password', array(
                'always_empty' => false,
                //'property_path' => false,
                'mapped' => false,
        ));
        $builder->add('newRawPassword', 'repeated', array(
                'type'             => 'password',
                'invalid_message' => 'Enter the same new password',
                //'property_path' => false,
                'mapped' => false,
        ));
        $builder->add('groups');
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'user_profile';
    }

}
