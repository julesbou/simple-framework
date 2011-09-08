<?php

namespace SF\Tests\Fixtures\Database;

use SF\Database;

class UserDatabase extends Database
{
    protected function getClassName()
    {
        return 'SF\Tests\Fixtures\Database\User';
    }

    protected function getTableName()
    {
        return 'test_user';
    }

    protected function getPrimaryKeys()
    {
        return array('user_ID', 'name');
    }

    protected function getRelations()
    {
        return array(
            'videos' => array(
                'property'  => 'videos',
                'type'      => 'many',
                'class'     => 'SF\Tests\Fixtures\Database\Video',
                'table'     => 'test_video',
                'foreign'   => 'user_id',
                'local'     => 'user_ID',
            ),
        );
    }
}
