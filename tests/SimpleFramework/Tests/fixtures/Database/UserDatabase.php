<?php

namespace SimpleFramework\Tests\Fixtures\Database;

use SimpleFramework\Database;

class UserDatabase extends Database
{
    protected function getClassName()
    {
        return 'SimpleFramework\Tests\Fixtures\Database\User';
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
                'class'     => 'SimpleFramework\Tests\Fixtures\Database\Video',
                'table'     => 'test_video',
                'foreign'   => 'user_id',
                'local'     => 'user_ID',
            ),
        );
    }
}
