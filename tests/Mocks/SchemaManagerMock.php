<?php

namespace Indaxia\OTR\Tests\Mocks;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;

/**
 * Mock class for AbstractSchemaManager.
 */
class SchemaManagerMock extends AbstractSchemaManager
{
    /**
     * @param \Doctrine\DBAL\Connection $conn
     */
    public function __construct(Connection $conn)
    {
        parent::__construct($conn);
    }

    /**
     * {@inheritdoc}
     */
    protected function _getPortableTableColumnDefinition($tableColumn)
    {
    }
}
