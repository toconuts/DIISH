<?php

namespace DIISH\SComDisBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * OutbreakItemsType,
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class OutbreakItemsType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('dayOfTheWeekString', 'text', array(
                'read_only' => true,
        ));
        $builder->add('ageGroup1F', 'integer');
        $builder->add('ageGroup1M', 'integer');
        $builder->add('ageGroup2F', 'integer');
        $builder->add('ageGroup2M', 'integer');
        $builder->add('ageGroup3F', 'integer');
        $builder->add('ageGroup3M', 'integer');
        $builder->add('ageGroup4F', 'integer');
        $builder->add('ageGroup4M', 'integer');
        $builder->add('ageGroup5F', 'integer');
        $builder->add('ageGroup5M', 'integer');
        $builder->add('ageGroup6F', 'integer');
        $builder->add('ageGroup6M', 'integer');
        $builder->add('ageGroup7F', 'integer');
        $builder->add('ageGroup7M', 'integer');
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'outbreak_items';
    }
}
