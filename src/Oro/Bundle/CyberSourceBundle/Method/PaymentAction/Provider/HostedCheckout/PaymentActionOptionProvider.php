<?php

namespace Oro\Bundle\CyberSourceBundle\Method\PaymentAction\Provider\HostedCheckout;

use Oro\Bundle\CyberSourceBundle\Method\Config\CyberSourceConfigInterface;
use Oro\Bundle\CyberSourceBundle\Method\PaymentAction\Provider\AbstractPaymentActionOptionProvider;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\OrderBundle\Entity\OrderLineItem;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class for preparing data options for CyberSource Hosted Checkout method
 */
class PaymentActionOptionProvider extends AbstractPaymentActionOptionProvider
{
    const HASH_ALGORITHM = 'sha256';
    const AMOUNT = 'amount';
    const CURRENCY = 'currency';
    const LINE_ITEM_SKU = 'item_%d_sku';
    const LINE_ITEM_CODE = 'item_%d_code';
    const LINE_ITEM_NAME = 'item_%d_name';
    const LINE_ITEM_QTY = 'item_%d_quantity';
    const LINE_ITEM_PRICE = 'item_%d_unit_price';
    const LINE_ITEM_COUNT = 'line_item_count';

    /** @var RouterInterface */
    protected $router;

    /** @var RequestStack */
    protected $requestStack;

    /**
     * {@inheritDoc}
     */
    public function getPurchaseOptions(CyberSourceConfigInterface $config, PaymentTransaction $transaction)
    {
        $lineItemsOptions = $this->getLineItemsOptions($transaction);
        $lineItemsOptionsKeys = array_keys($lineItemsOptions);

        $options = [
            'profile_id' => $config->getProfileId(),
            'access_key' => $config->getAccessKey(),
            'locale' => 'en-us',
            'merchant_descriptor' => $config->getMerchantDescriptor(),
            'signed_date_time' => gmdate("Y-m-d\TH:i:s\Z"),
            'signed_field_names' => $this->getSignedFieldNames(),
            'unsigned_field_names' => $this->getUnsignedFieldNames(). ',' . implode(',', $lineItemsOptionsKeys),
            'override_custom_cancel_page' => $this->getOverrideCustomCancelPage($transaction),
            'override_custom_receipt_page' => $this->getOverrideCustomReceiptPage($transaction),
            'customer_ip_address' => $this->getCustomerIpAddress(),
            'transaction_uuid' => uniqid(),
            'amount' => $transaction->getAmount(),
            'currency' => $transaction->getCurrency(),
            'transaction_type' => 'authorization',
            'reference_number' => $this->getClientReferenceCode($transaction),
            'merchant_ref_number' => $this->getClientReferenceCode($transaction),
            'auth_trans_ref_no' => $this->getClientReferenceCode($transaction),
            'ignore_avs' => 'true',
        ];

        $options = array_merge(
            $options,
            $this->getBillingOptions($transaction),
            $lineItemsOptions
        );

        $options['signature'] = $this->generateSignature($options, $config->getSecretKey());

        return $options;
    }

    /**
     * @param PaymentTransaction $transaction
     *
     * @return array
     */
    protected function getBillingOptions(PaymentTransaction $transaction)
    {
        $options = [];
        $address = $this->getBillingAddress($transaction);
        if ($address) {
            $firstNameWithoutSpecialCharacters = $this->removeSpecialCharacters((string) $address->getFirstName());
            $lastNameWithoutSpecialCharacters = $this->removeSpecialCharacters((string) $address->getLastName());

            $options = [
                'bill_to_forename' => $firstNameWithoutSpecialCharacters ?
                    $firstNameWithoutSpecialCharacters :
                    $this->removeSpecialCharacters((string) $this->getFrontEndOwnerFirstName($transaction)),
                'bill_to_surname' => $lastNameWithoutSpecialCharacters ?
                    $lastNameWithoutSpecialCharacters :
                    $this->removeSpecialCharacters((string) $this->getFrontEndOwnerLastName($transaction)),
                'bill_to_email' => (string) $this->getFrontEndOwnerEmail($transaction),
                'bill_to_phone' => (string) $address->getPhone(),
                'bill_to_address_line1' => $this->removeSpecialCharacters((string) $address->getStreet()),
                'bill_to_address_line2' => $this->removeSpecialCharacters((string) $address->getStreet2()),
                'bill_to_address_city' => (string) $address->getCity(),
                'bill_to_address_state' => (string) $address->getRegionCode(),
                'bill_to_address_postal_code' => (string) $address->getPostalCode(),
                'bill_to_address_country' => (string) $address->getCountryIso2(),
            ];
        }

        return $options;
    }

    /**
     * @param PaymentTransaction $transaction
     *
     * @return array
     */
    protected function getLineItemsOptions(PaymentTransaction $transaction)
    {
        $options = [];
        $lineItems = $this->getLineItems($transaction);

        if ($lineItems) {
            foreach ($lineItems as $lineItem) {
                $options[] = [
                    self::LINE_ITEM_SKU => $lineItem->getProductSku(),
                    self::LINE_ITEM_CODE => $lineItem->getProductName(),
                    self::LINE_ITEM_NAME => $lineItem->getProductName(),
                    self::LINE_ITEM_PRICE => (float)$lineItem->getPrice()->getValue(),
                    self::LINE_ITEM_QTY => (int)$lineItem->getQuantity(),
                ];
            }
        }

        return $this->prepareLineItemsOptions($options);
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function prepareLineItemsOptions($options)
    {
        $result = [];
        $number = 0;

        $fields = [self::LINE_ITEM_NAME, self::LINE_ITEM_CODE, self::LINE_ITEM_SKU];
        $numberFields = [self::LINE_ITEM_PRICE, self::LINE_ITEM_QTY, self::LINE_ITEM_COUNT];
        foreach ($options as $option) {
            foreach ($fields as $field) {
                $result[sprintf($field, $number)] = $this->getLineItemValue($option, $field);
            }

            foreach ($numberFields as $field) {
                $result[sprintf($field, $number)] = $this->getLineItemValue($option, $field, 0);
            }

            $number++;
        }

        $result[self::LINE_ITEM_COUNT] = $number;

        return $result;
    }

    /**
     * @param array $array
     * @param string $key
     * @param string $default
     *
     * @return string
     */
    protected function getLineItemValue($array, $key, $default = '')
    {
        return isset($array[$key]) ? $this->removeBadChar($array[$key]) : $default;
    }

    /**
     * @param mixed $var
     *
     * @return mixed
     */
    protected function removeBadChar($var)
    {
        if (is_string($var)) {
            $var = str_replace(['<', '>'], '', $var);
        }

        return $var;
    }

    /**
     * @param array $params
     * @param string $secretKey
     *
     * @return string
     */
    protected function generateSignature(array $params, string $secretKey)
    {
        $signedFieldNames = explode(",", $params["signed_field_names"]);
        $dataToSign = [];
        foreach ($signedFieldNames as $field) {
            $dataToSign[] = $field . "=" . $params[$field];
        }

        $data = implode(",", $dataToSign);

        return base64_encode(hash_hmac(self::HASH_ALGORITHM, $data, $secretKey, true));
    }

    /**
     * @return string
     */
    protected function getSignedFieldNames()
    {
        return 'profile_id,access_key,transaction_uuid,signed_field_names,unsigned_field_names,signed_date_time,'
            . 'locale,transaction_type,reference_number,auth_trans_ref_no,amount,currency,merchant_descriptor,'
            . 'override_custom_cancel_page,override_custom_receipt_page,ignore_avs';
    }

    /**
     * @return string
     */
    protected function getUnsignedFieldNames()
    {
        return 'signature,bill_to_forename,bill_to_surname,bill_to_email,bill_to_phone,bill_to_address_line1,'
            . 'bill_to_address_line2,bill_to_address_city,bill_to_address_state,bill_to_address_country,'
            . 'bill_to_address_postal_code,customer_ip_address';
    }

    /**
     * @param PaymentTransaction $transaction
     *
     * @return string
     */
    protected function getOverrideCustomCancelPage(PaymentTransaction $transaction)
    {
        return $this->router->generate(
            'oro_payment_callback_error',
            [
                'accessIdentifier' => $transaction->getAccessIdentifier(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    /**
     * @param PaymentTransaction $transaction
     *
     * @return string
     */
    protected function getOverrideCustomReceiptPage(PaymentTransaction $transaction)
    {
        return $this->router->generate(
            'oro_payment_callback_return',
            [
                'accessIdentifier' => $transaction->getAccessIdentifier(),
            ],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    /**
     * @return string
     */
    protected function getCustomerIpAddress()
    {
        $masterRequest = $this->requestStack->getMasterRequest();

        if (!$masterRequest) {
            return '';
        }

        return (string)$masterRequest->getClientIp();
    }

    /**
     * @param PaymentTransaction $transaction
     *
     * @return null|OrderLineItem[]
     */
    protected function getLineItems(PaymentTransaction $transaction)
    {
        $entity = $this->getPaymentEntity($transaction);
        $lineItems = null;

        if ($entity instanceof Order) {
            $lineItems = $entity->getLineItems()->toArray();
        }

        return $lineItems;
    }

    /**
     * @param RouterInterface $router
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param RequestStack $requestStack
     */
    public function setRequestStack(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }
}
