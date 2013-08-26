<?php

namespace DIISH\SComDisBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * SearchSurveillanceType
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class SearchSurveillanceType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
        $builder->add('sentinelSite');
        $builder->add('clinic');
    }
    
    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'SearchSurveillance';
    }
}
