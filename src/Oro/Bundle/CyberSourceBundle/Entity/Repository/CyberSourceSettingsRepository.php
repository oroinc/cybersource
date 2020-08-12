<?php

namespace Oro\Bundle\CyberSourceBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\CyberSourceBundle\Entity\CyberSourceSettings;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

/**
 * Repository for CyberSourceSettings entity
 */
class CyberSourceSettingsRepository extends EntityRepository
{
    /** @var AclHelper */
    protected $aclHelper;

    /**
     * @param AclHelper $aclHelper
     */
    public function setAclHelper(AclHelper $aclHelper)
    {
        $this->aclHelper = $aclHelper;
    }

    /**
     * @return CyberSourceSettings[]
     */
    public function findEnabledSettings()
    {
        $qb = $this->createQueryBuilder('settings');

        $qb
            ->innerJoin('settings.channel', 'channel')
            ->andWhere($qb->expr()->eq('channel.enabled', ':channelEnabled'))
            ->setParameter('channelEnabled', true);

        return $this->aclHelper->apply($qb)->getResult();
    }
}
