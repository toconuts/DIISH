<?php

namespace DIISH\SComDisBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * OutbreakType,
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class OutbreakType extends AbstractType
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
        $builder->add('syndrome');
        $builder->add('reportedBy');
        $builder->add('reportedAt', 'date', array(
                'widget' => 'single_text',
                'format' => 'dd/MM/yyyy',
                'attr'   => array('class' => 'epi-date')
        ));
        $builder->add('outbreakItems', 'collection', array(
                'type' => new OutbreakItemsType(),
                'options' => array('data_class' => 'DIISH\SComDisBundle\Entity\OutbreakItems'),
        ));
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DIISH\SComDisBundle\Entity\Outbreak',
            'csrf_protection' => true,
            'csrf_field_name' => '_token',
        ));
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'outbreak';
    }
}
