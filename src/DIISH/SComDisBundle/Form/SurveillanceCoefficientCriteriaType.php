<?php

namespace DIISH\SComDisBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * OutDailyTallyReportType
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class SurveillanceCoefficientCriteriaType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('target_year', 'text');
        $builder->add('weekend', 'date', array(
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'attr'   => array('class' => 'epi-weekend')
        ));
        $builder->add('weekOfYear', 'text', array(
                'attr'   => array('class' => 'epi-weekOfYear')
        ));
        $builder->add('year', 'text', array(
                'attr'   => array('class' => 'epi-year')
        ));
        $builder->add('year_choices');
        $builder->add('syndromes');
        $builder->add('useNoRecords', 'checkbox', array(
                  'required' => false,
        ));
        $builder->add('useLandwideSD', 'checkbox', array(
                  'required' => false,
        ));
        $builder->add('showIslandwide', 'checkbox', array(
                  'required' => false,
        ));
        $builder->add('showOnlyIslandwide', 'checkbox', array(
                  'required' => false,
        ));
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'SurveillanceCoefficient';
    }
}
