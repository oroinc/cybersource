<?php

namespace Oro\Bundle\CyberSourceBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class AddIntegrationSettings implements Migration
{
    public function up(Schema $schema, QueryBag $queries)
    {
        $table = $schema->getTable('oro_integration_transport');
        $table->addColumn('cbs_ignore_avs', 'boolean', ['notnull' => false, 'default' => false]);
        $table->addColumn('cbs_ignore_cvn', 'boolean', ['notnull' => false, 'default' => false]);
        $table->addColumn('cbs_auth_reversal', 'boolean', ['notnull' => false, 'default' => false]);
        $table->addColumn('cbs_display_errors', 'boolean', ['notnull' => false, 'default' => false]);
    }
}
