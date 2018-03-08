<?php

namespace Foo\Controller;

class FooController extends \Zend_Controller_Action
{

    public function init()
    {
        parent::init();
    }

    public function indexAction()
    {
xd('Controller');
        $fooModel = new Foo_Model_Foo();
        $this->view->foos = $fooModel->listar();
        $tooModel = new Foo_Model_Too();
        $this->view->toos = $tooModel->listar();
    }
}
