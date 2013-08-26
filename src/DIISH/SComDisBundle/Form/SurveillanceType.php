<?php

namespace DIISH\SComDisBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * SurveillanceType,
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class SurveillanceType extends AbstractType
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
        $builder->add('reportedBy', 'text', array(
                'read_only' => true
        ));
        $builder->add('reportedAt', 'date', array(
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'attr'   => array('class' => 'epi-date')
        ));
        $builder->add('surveillanceItems', 'collection', array(
                'type' => new SurveillanceItemsType(),
                'options' => array('data_class' => 'DIISH\SComDisBundle\Entity\SurveillanceItems'),
        ));
    }
    
    /**
     * @inheritDoc
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DIISH\SComDisBundle\Entity\Surveillance',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
        ));
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'surveillance';
    }
}
