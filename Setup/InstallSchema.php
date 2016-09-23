<?php
/**
 * Copyright (c) 2016. Radial inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Radial\FraudInsight\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'radial_fraud_insight'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('radial_fraud_insight')
        )->addColumn(
            'id',
            Table::TYPE_SMALLINT,
            null,
            ['identity' => true, 'nullable' => false, 'primary' => true],
            'Fraud Insight ID'
        )->addColumn(
            'order_increment_id',
            Table::TYPE_TEXT,
            32,
            ['nullable' => false],
            'Order Increment ID'
        )->addColumn(
            'http_headers',
            Table::TYPE_TEXT,
            '64k',
            ['nullable' => true],
            'HTTP Header Information'
        )->addColumn(
            'response_code',
            Table::TYPE_TEXT,
            32,
            [],
            'Response Code'
        )->addColumn(
            'response_code_description',
            Table::TYPE_TEXT,
            255,
            [],
            'Response Description Code'
        )->addColumn(
            'is_request_sent',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Flag for UCP Request'
        )->addColumn(
            'is_feedback_sent',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Flag for Feedback Request'
        )->addColumn(
            'feedback_attempt_count',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Feedback Request Failure Count'
        )->addColumn(
            'action_taken_acknowledgement',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Action Taken Acknowledgement'
        )->addColumn(
            'charge_back_acknowledgement',
            Table::TYPE_SMALLINT,
            null,
            ['nullable' => false, 'default' => '0'],
            'Charge Back Acknowledgement'
        )->addIndex(
            $setup->getIdxName(
                $installer->getTable('radial_fraud_insight'),
                ['order_increment_id'],
                AdapterInterface::INDEX_TYPE_UNIQUE
            ),
            ['order_increment_id'],
            ['type' => AdapterInterface::INDEX_TYPE_UNIQUE]
        )->setComment(
            'Fraud Insight Table'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}