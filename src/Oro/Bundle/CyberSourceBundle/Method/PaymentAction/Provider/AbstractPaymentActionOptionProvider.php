<?php

namespace Oro\Bundle\CyberSourceBundle\Method\PaymentAction\Provider;

use CyberSource\Model\Ptsv2paymentsClientReferenceInformation;
use CyberSource\Model\Ptsv2paymentsClientReferenceInformationPartner;
use CyberSource\Model\Ptsv2paymentsidreversalsClientReferenceInformation;
use CyberSource\Model\Ptsv2paymentsidreversalsReversalInformation;
use CyberSource\Model\Ptsv2paymentsidreversalsReversalInformationAmountDetails;
use CyberSource\Model\Ptsv2paymentsMerchantDefinedInformation;
use CyberSource\Model\Ptsv2paymentsOrderInformation;
use CyberSource\Model\Ptsv2paymentsOrderInformationAmountDetails;
use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfigInterface;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\OrderBundle\Entity\OrderAddress;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Provider\AddressExtractor;

/**
 * Contains default implementation for payment action providers.
 */
abstract class AbstractPaymentActionOptionProvider implements PaymentActionOptionProviderInterface
{
    const SOLUTION_ID = 'YBE25ZRJ';
    const BILLING_ADDRESS_PROPERTY = 'billingAddress';

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /** @var AddressExtractor */
    protected $addressExtractor;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param AddressExtractor $addressExtractor
     */
    public function __construct(DoctrineHelper $doctrineHelper, AddressExtractor $addressExtractor)
    {
        $this->doctrineHelper = $doctrineHelper;
        $this->addressExtractor = $addressExtractor;
    }

    /**
     * @inheritDoc
     */
    public function getPurchaseOptions(CyberSourceConfigInterface $config, PaymentTransaction $transaction)
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getAuthorizeOptions(CyberSourceConfigInterface $config, PaymentTransaction $transaction)
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getCaptureOptions(CyberSourceConfigInterface $config, PaymentTransaction $transaction)
    {
        $clientReferenceInfo = [
            'code' => $this->getClientReferenceCode($transaction),
            'partner' => new Ptsv2paymentsClientReferenceInformationPartner(
                ['solutionId' => self::SOLUTION_ID]
            )
        ];

        $amountDetailsArray = [
            'totalAmount' => $transaction->getAmount(),
            'currency' => $transaction->getCurrency()
        ];

        $orderInfoArray = [
            'amountDetails' => new Ptsv2paymentsOrderInformationAmountDetails($amountDetailsArray)
        ];

        return [
            'clientReferenceInformation' => new Ptsv2paymentsClientReferenceInformation($clientReferenceInfo),
            'orderInformation' => new Ptsv2paymentsOrderInformation($orderInfoArray),
            'merchantDefinedInformation' => new Ptsv2paymentsMerchantDefinedInformation()
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getCancelOptions(CyberSourceConfigInterface $config, PaymentTransaction $transaction)
    {
        $clientReferenceInformation = new Ptsv2paymentsidreversalsClientReferenceInformation(
            [
                'code' => $this->getClientReferenceCode($transaction),
                'partner' => new Ptsv2paymentsClientReferenceInformationPartner(
                    ['solutionId' => self::SOLUTION_ID]
                )
            ]
        );

        $reversalInformationAmountDetails = new Ptsv2paymentsidreversalsReversalInformationAmountDetails(
            [
                'totalAmount' => $transaction->getAmount()
            ]
        );

        $reversalInformation = new Ptsv2paymentsidreversalsReversalInformation(
            [
                'amountDetails' => $reversalInformationAmountDetails
            ]
        );

        return [
            'clientReferenceInformation' => $clientReferenceInformation,
            'reversalInformation' => $reversalInformation
        ];
    }

    /**
     * @inheritDoc
     */
    public function getValidateOptions(CyberSourceConfigInterface $config, PaymentTransaction $transaction)
    {
        return [];
    }


    /**
     * @param PaymentTransaction $paymentTransaction
     *
     * @return object
     */
    protected function getPaymentEntity(PaymentTransaction $paymentTransaction)
    {
        return $this->doctrineHelper
            ->getEntity($paymentTransaction->getEntityClass(), $paymentTransaction->getEntityIdentifier());
    }

    /**
     * @param PaymentTransaction $paymentTransaction
     *
     * @return string
     */
    public function getClientReferenceCode(PaymentTransaction $paymentTransaction)
    {
        $entity = $this->getPaymentEntity($paymentTransaction);

        if ($entity instanceof Order) {
            return (string)$entity->getIdentifier();
        }
        return $paymentTransaction->getReference();
    }

    /**
     * @param PaymentTransaction $paymentTransaction
     *
     * @return OrderAddress|null
     */
    public function getBillingAddress(PaymentTransaction $paymentTransaction)
    {
        $address = $this->addressExtractor->extractAddress(
            $this->getPaymentEntity($paymentTransaction),
            self::BILLING_ADDRESS_PROPERTY
        );
        if ($address instanceof OrderAddress) {
            return $address;
        }

        return null;
    }

    /**
     * @param string $var
     *
     * @return string
     */
    protected function removeSpecialCharacters($var)
    {
        if (is_string($var)) {
            $var = str_replace(['<', '>'], '', $var);
        }

        return $var;
    }

    /**
     * @param PaymentTransaction $paymentTransaction
     *
     * @return string|null
     */
    protected function getFrontEndOwnerFirstName(PaymentTransaction $paymentTransaction)
    {
        $frontendOwner = $paymentTransaction->getFrontendOwner();
        if (null === $frontendOwner) {
            return null;
        }

        return $frontendOwner->getFirstName();
    }

    /**
     * @param PaymentTransaction $paymentTransaction
     *
     * @return string|null
     */
    protected function getFrontEndOwnerLastName(PaymentTransaction $paymentTransaction)
    {
        $frontendOwner = $paymentTransaction->getFrontendOwner();
        if (null === $frontendOwner) {
            return null;
        }

        return $frontendOwner->getLastName();
    }

    /**
     * @param PaymentTransaction $paymentTransaction
     *
     * @return string|null
     */
    protected function getFrontEndOwnerEmail(PaymentTransaction $paymentTransaction)
    {
        $frontendOwner = $paymentTransaction->getFrontendOwner();
        if (null === $frontendOwner) {
            return null;
        }

        return $frontendOwner->getEmail();
    }
}
