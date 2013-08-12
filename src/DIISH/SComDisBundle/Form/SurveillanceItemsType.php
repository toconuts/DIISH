<?php

namespace DIISH\SComDisBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * SurveillanceItemsType,
 *
 * @author Natsuki Hara <toconuts@gmail.com>
 */
class SurveillanceItemsType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('syndrome', 'entity', array(
            'class' => 'DIISH\SComDisBundle\Entity\Syndrome4Surveillance',
            //'read_only' => true,
            'disabled' => true,
        ));
        $builder->add('sunday', 'integer');
        $builder->add('monday', 'integer');
        $builder->add('tuesday', 'integer');
        $builder->add('wednesday', 'integer');
        $builder->add('thursday', 'integer');
        $builder->add('friday', 'integer');
        $builder->add('saturday', 'integer');
        $builder->add('referrals', 'text', array(
                'required' => false,
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'DIISH\SComDisBundle\Entity\SurveillanceItems',
        ));
    }
    
    /**
     * @inheritDoc
     */
    public function getName()
    {
        return 'surveillance_items';
    }
}
