<?php

use SF\Controller as BaseController;

class controller extends BaseController
{
    public function indexAction()
    {
        return $this->render('template.php');
    }

    public function editAction($id)
    {
        $this->url('index');

        return $this->render('var_template.php', array('var' => $id));
    }
}
