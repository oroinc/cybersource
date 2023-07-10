<?php

namespace Oro\Bundle\CyberSourceBundle\Form\Type;

use Oro\Bundle\CyberSourceBundle\Entity\CyberSourceSettings;
use Oro\Bundle\FormBundle\Form\Type\OroEncodedPlaceholderPasswordType;
use Oro\Bundle\IntegrationBundle\Provider\TransportInterface;
use Oro\Bundle\LocaleBundle\Form\Type\LocalizedFallbackValueCollectionType;
use Oro\Bundle\SecurityBundle\Form\DataTransformer\Factory\CryptedDataTransformerFactoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Form type for CyberSource integration settings
 */
class CyberSourceSettingsType extends AbstractType
{
    const BLOCK_PREFIX = 'oro_cybersource_settings';

    /** @var TransportInterface */
    protected $transport;

    /** @var CryptedDataTransformerFactoryInterface */
    protected $cryptedDataTransformerFactory;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param TransportInterface $transport
     * @param CryptedDataTransformerFactoryInterface $cryptedDataTransformerFactory
     * @param TranslatorInterface $translator
     */
    public function __construct(
        TransportInterface $transport,
        CryptedDataTransformerFactoryInterface $cryptedDataTransformerFactory,
        TranslatorInterface $translator
    ) {
        $this->transport = $transport;
        $this->cryptedDataTransformerFactory = $cryptedDataTransformerFactory;
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $maxMessage = $this->translator->trans('oro.cybersource.settings.form.encrypted_fields_max_length.label');
        $builder
            ->add(
                'labels',
                LocalizedFallbackValueCollectionType::class,
                [
                    'label' => 'oro.cybersource.settings.labels.label',
                    'required' => true,
                    'entry_options' => ['constraints' => [new NotBlank(), new Length(['max' => 255])]],
                ]
            )
            ->add(
                'shortLabels',
                LocalizedFallbackValueCollectionType::class,
                [
                    'label' => 'oro.cybersource.settings.short_labels.label',
                    'required' => true,
                    'entry_options' => ['constraints' => [new NotBlank(), new Length(['max' => 255])]],
                ]
            )
            ->add('cbsMerchantId', TextType::class, [
                'label' => 'oro.cybersource.settings.merchant_id.label',
                'required' => true,
                'constraints' => [new NotBlank(), new Length(['max' => 255])],
            ])

            ->add('cbsMerchantDescriptor', TextType::class, [
                'label' => 'oro.cybersource.settings.merchant_descriptor.label',
                'required' => true,
                'constraints' => [new NotBlank(), new Length(['max' => 255])],
            ])

            ->add('cbsProfileId', TextType::class, [
                'label' => 'oro.cybersource.settings.profile_id.label',
                'required' => true,
                'constraints' => [new NotBlank(), new Length(['max' => 255])],
            ])
            ->add('cbsAccessKey', TextType::class, [
                'label' => 'oro.cybersource.settings.access_key.label',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 255, 'maxMessage' => $maxMessage])
                ],
            ])
            ->add('cbsApiKey', TextType::class, [
                'label' => 'oro.cybersource.settings.api_key.label',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 255, 'maxMessage' => $maxMessage])
                ],
            ])
            ->add('cbsApiSecretKey', OroEncodedPlaceholderPasswordType::class, [
                'label' => 'oro.cybersource.settings.api_secret_key.label',
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                    new Length(['max' => 255, 'maxMessage' => $maxMessage])
                ],
            ])
            ->add('cbsSecretKey', OroEncodedPlaceholderPasswordType::class, [
                'label' => 'oro.cybersource.settings.secret_key.label',
                'required' => true,
            ])
            ->add('cbsMethod', ChoiceType::class, [
                'choices' => CyberSourceSettings::METHODS,
                'choice_label' => function ($action) {
                    return $this->translator->trans(sprintf('oro.cybersource.settings.methods.%s.label', $action));
                },
                'label' => 'oro.cybersource.settings.method.label',
                'required' => true,
            ])
            ->add('cbsTestMode', CheckboxType::class, [
                'label' => 'oro.cybersource.settings.test_mode.label',
                'required' => false,
            ])
            ->add('cbsIgnoreAvs', CheckboxType::class, [
                'label' => 'oro.cybersource.settings.ignore_avs.label',
                'required' => false,
            ])
            ->add('cbsIgnoreCvn', CheckboxType::class, [
                'label' => 'oro.cybersource.settings.ignore_cvn.label',
                'required' => false,
            ])
            ->add('cbsAuthReversal', CheckboxType::class, [
                'label' => 'oro.cybersource.settings.auth_reversal.label',
                'required' => false,
            ])
            ->add('cbsDisplayErrors', CheckboxType::class, [
                'label' => 'oro.cybersource.settings.display_errors.label',
                'required' => false,
            ]);

        $this->enableEncryption($builder, 'cbsAccessKey');
        $this->enableEncryption($builder, 'cbsApiKey');
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['data_class' => $this->transport->getSettingsEntityFQCN()]);
    }

    /**
     * {@inheritDoc}
     */
    public function getBlockPrefix()
    {
        return self::BLOCK_PREFIX;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param string $fieldName
     */
    protected function enableEncryption(FormBuilderInterface $builder, $fieldName)
    {
        $builder->get($fieldName)->addModelTransformer($this->cryptedDataTransformerFactory->create());
    }
}
