<?php

namespace SimpleFramework\Tests\Fixtures\Database;

use SimpleFramework\Database;

class VideoDatabase extends Database
{
    protected function getClassName()
    {
        return 'SimpleFramework\Tests\Fixtures\Database\Video';
    }

    protected function getTableName()
    {
        return 'test_video';
    }

    protected function getPrimaryKeys()
    {
        return array('id');
    }

    protected function getRelations()
    {
        return array(
            'user' => array(
                'property'  => 'user',
                'type'      => 'one',
                'class'     => 'SimpleFramework\Tests\Fixtures\Database\User',
                'table'     => 'test_user',
                'foreign'   => 'user_ID',
                'local'     => 'user_id',
            ),
        );
    }
}
