<?php

namespace DIISH\SComDisBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * BBSNewType,
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class BBSNewType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('message');
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'bbs_new';
    }

}
