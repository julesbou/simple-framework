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

    public function testRenderFirstFoundFirstTaken()
    {
        $templating = new Templating(array('ns' => __DIR__.'/Fixtures/templates3', __DIR__.'/Fixtures/templates', __DIR__.'/Fixtures/templates2'));
        $this->assertEquals("template content\n", $templating->render('template.php'));
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
content
<h2>Bye</h2>

EOF;

        $this->assertEquals($complexTemplate, $templating->render('template_with_layout.php', array('var' => '    complex template')));
    }

    public function testRenderWithMultiLayout()
    {
        $templating = new Templating(array('' => __DIR__.'/Fixtures/templates'));
        $complexTemplate = <<<EOF
<h1>Hello</h1>
content
multi layout content
<h2>Bye</h2>

EOF;

        $this->assertEquals($complexTemplate, $templating->render('template_with_template_with_layout.php'));
    }

    public function testRenderWithParent()
    {
        $templating = new Templating(array(__DIR__.'/Fixtures/templates2', __DIR__.'/Fixtures/templates'));
        $complexTemplate = <<<EOF
child template content
template content
EOF;

        $this->assertEquals($complexTemplate, $templating->render('template.php'));
    }

    public function testRenderHelper()
    {
        $templating = new Templating(array('' => __DIR__.'/Fixtures/templates'));
        $templating['helper'] = 'foo';
        $this->assertEquals("foo", $templating->render('global_template.php'));
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
