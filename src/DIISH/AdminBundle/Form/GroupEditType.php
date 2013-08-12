<?php

namespace DIISH\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * GroupEditType,
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class GroupEditType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'text', array(
                    'read_only' => true,
        ));
        $builder->add('name');
        $builder->add('role');
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'group_edit';
    }
}
