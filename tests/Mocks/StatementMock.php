<?php

namespace Indaxia\OTR\Tests\Mocks;

use Doctrine\DBAL\Driver\Statement;

/**
 * This class is a mock of the Statement interface.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class StatementMock implements \IteratorAggregate, Statement
{
    /**
     * {@inheritdoc}
     */
    public function bindValue($param, $value, $type = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function bindParam($column, &$variable, $type = null, $length = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function errorCode()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function errorInfo(){}

    /**
     * {@inheritdoc}
     */
    public function execute($params = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function rowCount()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function closeCursor()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function columnCount()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function setFetchMode($fetchStyle, $arg2 = null, $arg3 = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function fetch($fetchStyle = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function fetchAll($fetchStyle = null)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function fetchColumn($columnIndex = 0)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
    }
}
