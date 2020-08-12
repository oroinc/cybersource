<?php

namespace Oro\Bundle\CyberSourceBundle;

use Oro\Bundle\CyberSourceBundle\DependencyInjection\OroCyberSourceExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * The CyberSource bundle class.
 */
class OroCyberSourceBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getContainerExtension()
    {
        if (!$this->extension) {
            $this->extension = new OroCyberSourceExtension();
        }

        return $this->extension;
    }
}
