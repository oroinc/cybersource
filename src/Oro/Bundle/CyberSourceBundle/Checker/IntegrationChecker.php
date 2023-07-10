<?php

namespace Oro\Bundle\CyberSourceBundle\Checker;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Generator\IntegrationIdentifierGeneratorInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentMethodConfig;

/**
 * Checks if delete is allowed for the given integration.
 */
class IntegrationChecker
{
    /** @var IntegrationIdentifierGeneratorInterface */
    protected $identifierGenerator;

    /** @var DoctrineHelper */
    protected $doctrineHelper;

    /**
     * @param IntegrationIdentifierGeneratorInterface $identifierGenerator
     * @param DoctrineHelper $doctrineHelper
     */
    public function __construct(
        IntegrationIdentifierGeneratorInterface $identifierGenerator,
        DoctrineHelper $doctrineHelper
    ) {
        $this->identifierGenerator = $identifierGenerator;
        $this->doctrineHelper = $doctrineHelper;
    }

    /**
     * Allow delete only if integration is not used as payment method.
     *
     * @param Channel $data
     *
     * @return bool
     */
    public function isDeleteAllowed(Channel $data): bool
    {
        $integrationIdentifier = $this->identifierGenerator->generateIdentifier($data);

        return !(bool)$this->doctrineHelper
            ->getEntityRepositoryForClass(PaymentMethodConfig::class)
            ->count(['type' => $integrationIdentifier]);
    }
}
