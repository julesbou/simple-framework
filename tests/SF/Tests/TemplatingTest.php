<?php

namespace SF\Tests;

use SF\Templating;

class TemplatingMock extends Templating
{
    protected function doRender($__t__, $__v__)
    {
        return 'doRender';
    }
}

class TemplatingTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $templating = new Templating(array('' => __DIR__.'/Fixtures/templates'));
        $this->assertEquals("template content\n", $templating->render('template.php'));
    }

    public function testDoRenderCanBeOverride()
    {
        $mock = new TemplatingMock(array('' => __DIR__.'/Fixtures/templates'));
        $this->assertEquals('doRender', $mock->render('template.php'));
    }

    public function testRenderWithNamespace()
    {
        $templating = new Templating(array('namespace' => __DIR__.'/Fixtures/templates'));
        $this->assertEquals("template content\n", $templating->render('namespace:template.php'));
    }

    public function testRenderWithVars()
    {
        $templating = new Templating(array('' => __DIR__.'/Fixtures/templates'));
        $this->assertEquals('var template', $templating->render('var_template.php', array('var' => 'var template')));
    }

    public function testRenderWithLayout()
    {
        $templating = new Templating(array('' => __DIR__.'/Fixtures/templates'));
        $complexTemplate = <<<EOF
<h1>Hello</h1>
    complex template<h2>Bye</h2>

EOF;

        $this->assertEquals($complexTemplate, $templating->render('var_template.php', array('var' => '    complex template'), 'layout.php'));
    }

    public function testGlobalVars()
    {
        $templating = new Templating(array('' => __DIR__.'/Fixtures/templates'));
        $templating->setGlobalVars(array('var' => 'global var'));
        $this->assertEquals("global var", $templating->render('global_template.php'));

        $this->assertEquals("global var", $templating->render('global_template.php', array('var' => 'local var')), 'global var cannot be overriden');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testTemplateDirectoryNotValid()
    {
        new Templating(array('/invalid/dir'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidTemplate()
    {
        $templating = new Templating(array('' => __DIR__.'/Fixtures/templates'));
        $templating->render('invalid_template.php');
    }

    /**
     * @expectedException OutOfBoundsException
     */
    public function testInvalidNamespace()
    {
        $templating = new Templating();
        $templating->render('invalid_namespace:template.php');
    }
}
