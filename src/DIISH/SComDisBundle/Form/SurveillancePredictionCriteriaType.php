<?php

namespace DIISH\SComDisBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * SurveillancePredictionCriteriaType
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class SurveillancePredictionCriteriaType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('target_year', 'text');
        $builder->add('year_choices');
        $builder->add('syndromes');
        $builder->add('useSeriesSyndromes', 'checkbox', array(
                  'required' => false,
        ));
        $builder->add('sentinelSites');
        $builder->add('useNoRecords', 'checkbox', array(
                  'required' => false,
        ));
        $builder->add('confidence_coefficient');
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'SurveillancePrediction';
    }
}
