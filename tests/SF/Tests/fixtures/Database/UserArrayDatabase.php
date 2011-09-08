<?php

namespace SF\Tests\Fixtures\Database;

use SF\Database;

class UserArrayDatabase extends Database
{
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
                'property'  => 'video',
                'type'      => 'many',
                'class'     => 'SF\Tests\Fixtures\Database\Video',
                'table'     => 'test_user_video',
                'foreign'   => 'user_id',
                'local'     => 'id',
            ),
        );
    }
}
