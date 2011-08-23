<?php

namespace SimpleFramework\Tests;

require __DIR__.'/Fixtures/Database/User.php';
require __DIR__.'/Fixtures/Database/UserArrayDatabase.php';
require __DIR__.'/Fixtures/Database/UserDatabase.php';
require __DIR__.'/Fixtures/Database/Video.php';
require __DIR__.'/Fixtures/Database/VideoDatabase.php';

class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    protected static $pdo;
    protected static $userArray;
    protected static $user;
    protected static $video;

    public static function setUpBeforeClass()
    {
        self::$pdo          = new \PDO('sqlite://:memory:');
        self::$user         = new Fixtures\Database\UserDatabase(self::$pdo);
        self::$video        = new Fixtures\Database\VideoDatabase(self::$pdo);
        self::$userArray    = new Fixtures\Database\UserArrayDatabase(self::$pdo);

        self::$pdo->exec('DROP TABLE IF EXISTS test_user');
        self::$pdo->exec('DROP TABLE IF EXISTS test_video');
        self::$pdo->exec('CREATE Table test_user (user_ID INTEGER, name TEXT, video INTEGER)');
        self::$pdo->exec('CREATE Table test_video (id INTEGER, video TEXT, user_id INTEGER)');
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
        $users = self::$userArray->find()->all();
        $this->assertEquals('wallace', $users[0]['name']);
        $this->assertFalse(isset($users[0][0]), 'we want a fetch assoc');

        $users = self::$user->find()->all();
        $this->assertEquals('wallace', $users[0]->name);

        // find all by
        $users = self::$userArray->find(array('name' => 'wallace'))->all();
        $this->assertEquals('wallace', $users[0]['name']);
        $this->assertFalse(isset($users[0][0]), 'we want a fetch assoc');

        $users = self::$user->find(array('name' => 'wallace'))->all();
        $this->assertEquals('wallace', $users[0]->name);

        // find one by
        $user = self::$userArray->find(array('name' => 'wallace'))->one();
        $this->assertEquals('wallace', $user['name']);
        $this->assertFalse(isset($user[0]), 'we want a fetch assoc');

        $user = self::$user->find(array('name' => 'wallace'))->one();
        $this->assertEquals('wallace', $user->name);

        // find with primary
        $user = self::$userArray->find(1)->one();
        $this->assertEquals('wallace', $user['name']);

        $user = self::$user->find(1)->one();
        $this->assertEquals('wallace', $user->name);

        $user = self::$user->find('wallace')->one();
        $this->assertEquals('wallace', $user->name);
    }

    public function testDelete()
    {
        self::$user->delete(1);
        $this->assertSame(false, self::$pdo->query('SELECT * FROM test_user')->fetch());
    }

    public function testJoinOneWithOneToOne()
    {
        self::$pdo->query("INSERT INTO test_user (user_ID, name) VALUES (1, 'Einstein')");
        self::$pdo->query("INSERT INTO test_video (id, video, user_id) VALUES (1, 'vids', 1)");

        $video = self::$video->joinOne(array('id' => 1), array('user'));
        $this->assertEquals('vids', $video->video);
        $this->assertEquals('Einstein', $video->user->name);
        $this->assertEquals(4, count(get_object_vars($video)));

        // test null result
        $user = self::$video->joinOne(array('id' => 999), array('user'));
        $this->assertSame(null, $user);
    }

    public function testJoinOneWithOneToMany()
    {
        self::$pdo->query("INSERT INTO test_video (id, video, user_id) VALUES (2, 'vidss', 1)");

        $user = self::$user->joinOne(array('user_ID' => 1), array('videos'));

        $this->assertEquals('Einstein', $user->name);
        $this->assertEquals('vids', $user->videos[0]->video);
        $this->assertEquals('vidss', $user->videos[1]->video);
        $this->assertEquals(3, count(get_object_vars($user)));
    }

    public function testJoinManyWithOneToOne()
    {
        self::$pdo->query("INSERT INTO test_user (user_ID, name) VALUES (2, 'Jules')");

        $videos = self::$video->joinMany(array(), array('user'));

        $this->assertEquals('Einstein', $videos[0]->user->name);
        $this->assertEquals('vids', $videos[0]->video);
        $this->assertEquals(4, count(get_object_vars($videos[0])));
    }

    public function testJoinManyWithOneToMany()
    {
        self::$pdo->query("INSERT INTO test_user_video (id, video) VALUES (2, 'vidss')");

        $users = self::$user->joinMany(array('user_ID' => 1), array('videos'));

        $this->assertEquals('Einstein', $users[0]->name);
        $this->assertEquals('vids', $users[0]->videos[0]->video);
        $this->assertEquals(3, count(get_object_vars($users[0])));
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testJoinWithUndefinedRelation()
    {
        self::$user->joinOne(array(), array('unknow_rel'));
    }
}
