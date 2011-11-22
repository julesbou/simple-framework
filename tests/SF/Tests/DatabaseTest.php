<?php

namespace SF\Tests;

class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    protected static $pdo;
    protected static $user;

    public static function setUpBeforeClass()
    {
        self::$pdo = new \PDO('sqlite://:memory:');

        self::$pdo->exec('DROP TABLE IF EXISTS test_user');
        self::$pdo->exec('CREATE Table test_user (user_ID INTEGER, name TEXT, video INTEGER)');
    }

    public function testConstruct()
    {
        self::$user = new \SF\Database(self::$pdo, 'test_user', array('user_ID', 'name'));
    }

    public function testFindAllReturnEmptyArray()
    {
        $this->assertSame(array(), self::$user->findAll());
    }

    public function testInsert()
    {
        $result = self::$user->insert(array('user_ID' => 1, 'name' => 'bobby'));

        $user = self::$pdo->query('SELECT * FROM test_user')->fetch();
        $this->assertEquals('bobby', $user['name']);
        $this->assertSame('1', $user['user_ID']);
    }

    public function testUpdate()
    {
        self::$user->update(1, array('name' => 'wallace'));

        $user = self::$pdo->query('SELECT * FROM test_user')->fetch();
        $this->assertEquals('wallace', $user['name']);
    }

    public function testFind()
    {
        // find all
        $users = self::$user->findAll();
        $this->assertEquals('wallace', $users[0]['name']);
        $this->assertFalse(isset($users[0][0]), 'we want a fetch assoc');

        // find all by
        $users = self::$user->findBy(array('name' => 'wallace'));
        $this->assertEquals('wallace', $users[0]['name']);
        $this->assertFalse(isset($users[0][0]), 'we want a fetch assoc');

        // find one by
        $user = self::$user->findOneBy(array('name' => 'wallace'));
        $this->assertEquals('wallace', $user['name']);
        $this->assertFalse(isset($user[0]), 'we want a fetch assoc');

        // find with primary
        $user = self::$user->find(1);
        $this->assertEquals('wallace', $user['name']);
    }

    public function testDelete()
    {
        self::$user->delete(1);

        $this->assertSame(false, self::$pdo->query('SELECT * FROM test_user')->fetch());
    }
}
