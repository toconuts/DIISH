<?php

namespace DIISH\SComDisBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * SurveillanceTrendCriteriaType
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class SurveillanceTrendCriteriaType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('year_choices');
        $builder->add('syndromes');
        $builder->add('useSeriesSyndromes', 'checkbox', array(
                  'required' => false,
        ));
        $builder->add('sentinelSites');
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'SurveillanceTrend';
    }
}
