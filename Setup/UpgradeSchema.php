<?php
/**
 * Copyright © 2016 Trive (http://www.trive.digital/) All rights reserved.
 */

namespace Trive\Revo\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\DB\Ddl\Table;


class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
   
        if (version_compare($context->getVersion(), '1.0.1') < 0) {
        	// Get module table
            $tableName = $installer->getTable('trive_revo');
            // Check if the table already exists
            if ($installer->getConnection()->isTableExists($tableName) == true) {
                $connection = $installer->getConnection();

                $connection->addColumn(
                $tableName,
                'read_more_url',
                [
                    'type' => Table::TYPE_TEXT,
                    'comment' => 'Read more URL'
                ]);
            }
        }
		
		if (version_compare($context->getVersion(), '1.0.2') < 0) {
        	// Get module table
            $tableName = $installer->getTable('trive_revo');
            // Check if the table already exists
            if ($installer->getConnection()->isTableExists($tableName) == true) {
                $connection = $installer->getConnection();

                $connection->addColumn(
                $tableName,
                'background_image',
                [
                    'type' => Table::TYPE_TEXT,
                    'comment' => 'Background image'
                ]);
            }
        }
        
        if (version_compare($context->getVersion(), '1.0.4') < 0) {
            // Get module table
            $tableName = $installer->getTable('trive_revo');
            // Check if the table already exists
            if ($installer->getConnection()->isTableExists($tableName) == true) {
                $connection = $installer->getConnection();

                $connection->addColumn(
                $tableName,
                'background_image_mobile',
                [
                    'type' => Table::TYPE_TEXT,
                    'comment' => 'Background image mobile'
                ]);

                $connection->addColumn(
                $tableName,
                'title_background_image',
                [
                    'type' => Table::TYPE_TEXT,
                    'comment' => 'Title background image'
                ]);

                $connection->addColumn(
                $tableName,
                'title_background_image_mobile',
                [
                    'type' => Table::TYPE_TEXT,
                    'comment' => 'Title background image mobile'
                ]);
            }
        }

        $installer->endSetup();
    }
}
