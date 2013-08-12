<?php

namespace DIISH\SComDisBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * SurveillanceGISCriteriaType
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class SurveillanceGISCriteriaType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('target_year', 'text');
        $builder->add('year_choices');
        $builder->add('syndromes');
        $builder->add('useNoRecords', 'checkbox', array(
                  'required' => false,
        ));
        $builder->add('useIslandwideSD', 'checkbox', array(
                  'required' => false,
        ));
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'SurveillanceGIS';
    }
}
