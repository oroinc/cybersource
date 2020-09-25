<?php

namespace Oro\Bundle\CyberSourceBundle\Method\PaymentAction\Provider\CheckoutApi;

use CyberSource\Model\Ptsv2paymentsClientReferenceInformation;
use CyberSource\Model\Ptsv2paymentsClientReferenceInformationPartner;
use CyberSource\Model\Ptsv2paymentsOrderInformation;
use CyberSource\Model\Ptsv2paymentsOrderInformationAmountDetails;
use CyberSource\Model\Ptsv2paymentsOrderInformationBillTo;
use CyberSource\Model\Ptsv2paymentsTokenInformation;
use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfigInterface;
use Oro\Bundle\CyberSourceBundle\Method\PaymentAction\Provider\AbstractPaymentActionOptionProvider;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;

/**
 * Class for preparing data options for CyberSource Checkout API calls
 */
class PaymentActionOptionProvider extends AbstractPaymentActionOptionProvider
{
    /**
     * {@inheritDoc}
     */
    public function getPurchaseOptions(CyberSourceConfigInterface $config, PaymentTransaction $transaction)
    {
        $options = $transaction->getTransactionOptions();

        if (!array_key_exists('additionalData', $options) || $options['additionalData'] === null) {
            return [];
        }
        $decodedOptions = json_decode($options['additionalData'], true);
        $transientTokenJWK = '';
        if (isset($decodedOptions['token'])) {
            $transientTokenJWK = $decodedOptions['token'];
        }

        $clientReferenceInformationArr = [
            'code' => $this->getClientReferenceCode($transaction),
            'partner' => new Ptsv2paymentsClientReferenceInformationPartner(
                ['solutionId' => self::SOLUTION_ID]
            )
        ];

        $orderInformationAmountDetailsArr = [
            'totalAmount' => $transaction->getAmount(),
            'currency' => $transaction->getCurrency()
        ];

        $orderInformationBillToArr = [];
        $address = $this->getBillingAddress($transaction);
        if ($address) {
            $firstNameWithoutSpecialCharacters = $this->removeSpecialCharacters((string) $address->getFirstName());
            $lastNameWithoutSpecialCharacters = $this->removeSpecialCharacters((string) $address->getLastName());

            $orderInformationBillToArr = [
                'firstName' => $firstNameWithoutSpecialCharacters ?
                    $firstNameWithoutSpecialCharacters :
                    $this->removeSpecialCharacters((string) $this->getFrontEndOwnerFirstName($transaction)),
                'lastName' => $lastNameWithoutSpecialCharacters ?
                    $lastNameWithoutSpecialCharacters :
                    $this->removeSpecialCharacters((string) $this->getFrontEndOwnerLastName($transaction)),
                'address1' => $this->removeSpecialCharacters((string) $address->getStreet()),
                'address2' => $this->removeSpecialCharacters((string) $address->getStreet2()),
                'locality' => $address->getCity(),
                'administrativeArea' => $address->getRegionCode(),
                'postalCode' => $address->getPostalCode(),
                'country' => $address->getCountryIso2(),
                'email' => $this->getFrontEndOwnerEmail($transaction),
                'phoneNumber' => $address->getPhone()
            ];
        }

        $orderInformationArr = [
            'amountDetails' => new Ptsv2paymentsOrderInformationAmountDetails($orderInformationAmountDetailsArr),
            'billTo' => new Ptsv2paymentsOrderInformationBillTo($orderInformationBillToArr)
        ];

        return [
            'clientReferenceInformation' => new Ptsv2paymentsClientReferenceInformation($clientReferenceInformationArr),
            'orderInformation' => new Ptsv2paymentsOrderInformation($orderInformationArr),
            'tokenInformation' => new Ptsv2paymentsTokenInformation(['transientTokenJwt' => $transientTokenJWK])
        ];
    }
}
