<?php

namespace DIISH\SComDisBundle\Form\Admin\Syndrome4Surveillance;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Syndrome4SurveillanceEditType,
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class Syndrome4SurveillanceEditType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name');
        $builder->add('displayId');
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'syndrome4surveillance_edit';
    }

}
