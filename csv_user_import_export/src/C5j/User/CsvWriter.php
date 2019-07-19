<?php

/**
 * Class CsvWriter.
 *
 * @author: Biplob Hossain <biplob@concrete5.co.jp>
 *
 * @license MIT
 * Date: 2019-07-19
 */
namespace C5j\User;

use Concrete\Core\User\UserInfo;
use Concrete\Core\User\UserList;
use League\Csv\Writer;

class CsvWriter
{
    public $writer;
    /** @var Columns */
    protected $columns;

    public function __construct()
    {
        $this->writer = Writer::createFromPath('php://output', 'w');
        $this->columns = new Columns();
    }

    public function insertHeaders()
    {
        $this->writer->insertOne(iterator_to_array($this->columns->getHeaders()));
    }

    public function insertRecords(UserList $list)
    {
        $this->writer->insertAll($this->getRecords($list));
    }

    private function getRecords(UserList $list)
    {
        $statement = $list->deliverQueryObject()->execute();

        foreach ($statement as $result) {
            $user = $list->getResult($result);
            yield iterator_to_array($this->getRecord($user));
        }
    }

    private function getRecord(UserInfo $user)
    {
        yield $user->getUserID();
        yield $user->getUserName();
        yield $user->getUserEmail();
        yield $user->getUserTimezone();
        yield $user->getUserDefaultLanguage();
        yield is_object($user->getUserDateAdded()) ? $user->getUserDateAdded()->format('Y-m-d H:i:s') : '';
        yield $user->getLastOnline();
        yield $user->getLastLogin();
        yield $user->getLastIPAddress();
        yield $user->getPreviousLogin();
        yield $user->isActive() ? 'active' : 'inactive';
        yield $user->isValidated();
        yield $user->getNumLogins();

        $attributes = $this->columns->getAttributeKeys();
        foreach ($attributes as $attribute) {
            $value = $user->getAttributeValueObject($attribute);
            yield $value ? $value->getValue('display') : '';
        }
    }
}
