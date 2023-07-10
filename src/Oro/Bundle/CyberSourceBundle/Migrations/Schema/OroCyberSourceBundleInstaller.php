<?php

namespace Oro\Bundle\CyberSourceBundle\Migrations\Schema;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Installation;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroCyberSourceBundleInstaller implements Installation
{
    /**
     * {@inheritDoc}
     */
    public function getMigrationVersion()
    {
        return 'v1_1';
    }

    /**
     * {@inheritDoc}
     */
    public function up(Schema $schema, QueryBag $queryBag)
    {
        $this->updateOroIntegrationTransportTable($schema);

        /** Tables generation **/
        $this->createOroCyberSourceShortLabelTable($schema);
        $this->createOroCyberSourceTransLabelTable($schema);

        /** Foreign keys generation **/
        $this->addOroCyberSourceShortLabelForeignKeys($schema);
        $this->addOroCyberSourceTransLabelForeignKeys($schema);
    }

    /**
     * @param Schema $schema
     */
    public function updateOroIntegrationTransportTable(Schema $schema)
    {
        $table = $schema->getTable('oro_integration_transport');
        $table->addColumn('cbs_test_mode', 'boolean', ['notnull' => false, 'default' => false]);
        $table->addColumn('cbs_merchant_id', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('cbs_merchant_descriptor', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('cbs_profile_id', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('cbs_access_key', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('cbs_secret_key', 'text', ['notnull' => false]);
        $table->addColumn('cbs_api_key', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('cbs_api_secret_key', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('cbs_method', 'string', ['notnull' => false, 'length' => 255]);
        $table->addColumn('cbs_ignore_avs', 'boolean', ['notnull' => false, 'default' => false]);
        $table->addColumn('cbs_ignore_cvn', 'boolean', ['notnull' => false, 'default' => false]);
        $table->addColumn('cbs_auth_reversal', 'boolean', ['notnull' => false, 'default' => false]);
        $table->addColumn('cbs_display_errors', 'boolean', ['notnull' => false, 'default' => false]);
    }

    /**
     * Create oro_cbs_short_label table
     *
     * @param Schema $schema
     */
    protected function createOroCyberSourceShortLabelTable(Schema $schema)
    {
        $table = $schema->createTable('oro_cbs_short_label');
        $table->addColumn('transport_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->setPrimaryKey(['transport_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id']);
        $table->addIndex(['transport_id'], null, []);
    }

    /**
     * Create oro_cbs_trans_label table
     *
     * @param Schema $schema
     */
    protected function createOroCyberSourceTransLabelTable(Schema $schema)
    {
        $table = $schema->createTable('oro_cbs_trans_label');
        $table->addColumn('transport_id', 'integer', []);
        $table->addColumn('localized_value_id', 'integer', []);
        $table->setPrimaryKey(['transport_id', 'localized_value_id']);
        $table->addUniqueIndex(['localized_value_id']);
    }

    /**
     * Add oro_cbs_short_label foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroCyberSourceShortLabelForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_cbs_short_label');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_transport'),
            ['transport_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }

    /**
     * Add oro_cbs_trans_label foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroCyberSourceTransLabelForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('oro_cbs_trans_label');
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_integration_transport'),
            ['transport_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('oro_fallback_localization_val'),
            ['localized_value_id'],
            ['id'],
            ['onDelete' => 'CASCADE', 'onUpdate' => null]
        );
    }
}
