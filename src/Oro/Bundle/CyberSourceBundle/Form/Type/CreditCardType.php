<?php

namespace Oro\Bundle\CyberSourceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Form type for credit card. Contains only expiration date since another fields will be rendered via flex microform js.
 */
class CreditCardType extends AbstractType
{
    public const NAME = 'oro_cybersource_credit_card';

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'expirationDate',
            CreditCardExpirationDateType::class,
            [
                'required'    => true,
                'label'       => 'oro.cybersource.credit_card.expiration_date.label',
                'mapped'      => false,
                'placeholder' => [
                    'year'  => 'oro.cybersource.credit_card.expiration_date.year',
                    'month' => 'oro.cybersource.credit_card.expiration_date.month',
                ],
                'attr'        => [
                    'data-expiration-date'          => true,
                    'data-validation-ignore-onblur' => true,
                ],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return self::NAME;
    }
}
