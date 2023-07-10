<?php

namespace Oro\Bundle\CyberSourceBundle\Entity\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Oro\Bundle\CyberSourceBundle\Entity\CyberSourceSettings;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;

/**
 * Repository for CyberSourceSettings entity
 */
class CyberSourceSettingsRepository extends ServiceEntityRepository
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
