<?php

namespace DIISH\SComDisBundle\Form\Admin\SentinelSite;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * SentinelSiteEditType,
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class SentinelSiteEditType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name');
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'sentinelSite_edit';
    }

}
