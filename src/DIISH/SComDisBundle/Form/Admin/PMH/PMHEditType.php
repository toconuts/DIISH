<?php

namespace DIISH\SComDisBundle\Form\Admin\PMH;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * PMHEditType,
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class PMHEditType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'text', array(
                'read_only' => true,
        ));
        $builder->add('clinic');
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'pmh_edit';
    }

}
