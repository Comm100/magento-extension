<?php

namespace Comm100\LiveChat\Setup;

use Comm100\LiveChat\Helper\Constants;
use DateTime;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var LoggerInterface
     */
    protected $_logger;

    public function __construct(\Psr\Log\LoggerInterface $logger)
    {
        $this->_logger = $logger;
    }

    /**
     * Installs DB schema for a module.
     *
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;

        $installer->startSetup();
        $connection = $installer->getConnection();

        // Create the master table.
        $masterTable = $setup
            ->getConnection()
            ->newTable($setup->getTable(Constants::PARENT_TABLE_NAME))
            ->addColumn(
                'MagentoBaseURL',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['identity' => false, 'nullable' => true, 'primary' => false],
                'no comments'
            )
			->addColumn(
                'MagentoAPIBaseURL',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['identity' => false, 'nullable' => true, 'primary' => false],
                'no comments'
            )
            ->addColumn(
                'MagentoAccountEmail',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['identity' => false, 'nullable' => true, 'primary' => false],
                'no comments'
            )
            ->addColumn(
                'Comm100AgentEmail',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['identity' => false, 'nullable' => true, 'primary' => false],
                'no comments'
            )
            ->addColumn(
                'Comm100AccessToken',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                2000,
                ['identity' => false, 'nullable' => true, 'primary' => false],
                'no comments'
            )
            ->addColumn(
                'WebhookConnectionStatus',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['identity' => false, 'nullable' => true, 'primary' => false,'default'=>false],
                'no comments'
            )
            ->addColumn(
                'AccessToken',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['identity' => false, 'nullable' => true, 'primary' => false],
                'no comments'
            )
            ->addColumn(
                'ConsumerKey',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['identity' => false, 'nullable' => true, 'primary' => false],
                'no comments'
            )
            ->addColumn(
                'ConsumerSecret',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['identity' => false, 'nullable' => true, 'primary' => false],
                'no comments'
            )
            ->addColumn(
                'OAuthVerifier',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['identity' => false, 'nullable' => true, 'primary' => false],
                'no comments'
            )
            ->addColumn(
                'MagentoAppBaseURL',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['identity' => false, 'nullable' => true, 'primary' => false],
                'no comments'
            )
            ->addColumn(
                'MagentoVersion',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['identity' => false, 'nullable' => true, 'primary' => false],
                'no comments'
            )
            ->addColumn(
                'Comm100SiteID',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                [
                    'identity' => false,
                    'nullable' => true,
                    'primary' => false,
                    'unique' => true,
                ],
                'no comments'
            )
            ->addColumn(
                'CreatedOn',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [
                    'identity' => false,
                    'nullable' => false,
                    'primary' => false,
                    'default' => new DateTime(),
                ],
                'no comments'
            )
            ->addColumn(
                'UpdatedOn',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['identity' => false, 'nullable' => true, 'primary' => false],
                'no comments'
            );

        $connection->createTable($masterTable);

        // Create the child table.
        $childTable = $setup
            ->getConnection()
            ->newTable($setup->getTable(Constants::CHILD_TABLE_NAME))
            ->addColumn(
                'Comm100SiteID',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => false, 'nullable' => true, 'primary' => false],
                'no comments'
            )
            ->addColumn(
                'Comm100CampaignID',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['identity' => false, 'nullable' => true, 'primary' => false],
                'no comments'
            )
            ->addColumn(
                'MagentoStoreId',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                11,
                ['identity' => false, 'nullable' => false, 'primary' => false],
                'no comments'
            )
            ->addColumn(
                'CreatedOn',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                6,
                [
                    'identity' => false,
                    'nullable' => true,
                    'primary' => false,
                    'default' => new DateTime(),
                ],
                'no comments'
            )
            ->addColumn(
                'UpdatedOn',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                6,
                ['identity' => false, 'nullable' => true, 'primary' => false],
                'no comments'
            );

        $connection->createTable($childTable);

        $installer->endSetup();
    }
}
