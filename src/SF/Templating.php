<?php

namespace SF;

/*
 * This file is part of the SimpleFrameworke
 *
 * (c) Jules Boussekeyt <jules.boussekeyt@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

class Templating implements \ArrayAccess
{
    /**
     * @var array An array of directories, keys are section names
     */
    protected $directories;

    protected $helpers = array();

    protected $parents = array();

    protected $current;

    public function __construct($directories = array())
    {
        foreach ($directories as $templatesDir) {
            if (!is_dir($templatesDir)) {
                throw new \InvalidArgumentException("templates dir '{$templatesDir}' is not a directory");
            }
        }

        $this->directories = $directories;
    }

    /**
     * Render a template
     *
     * Include a template, declare parameters in it and return template's content
     *
     * @param template string The template filename (eg: my_page.php)
     * @param vars array Variables that get passed to template
     * @param layout string Template of the layout
     */
    public function render($template, $vars = array(), $layout = null)
    {
        $this->current  = $template;
        $this->parents[$template] = null;

        $templatePath   = $this->findTemplate($template);
        $vars           = array('view' => $this) + $vars;
        $content        = $this->doRender($templatePath, $vars);

        if ($this->parents[$template]) {
            $layoutPath = $this->findTemplate($this->parents[$template]);
            $layoutVars = array('content' => $content, 'view' => $this);
            $content = $this->render($this->parents[$template], $layoutVars + $vars);
        }

        return $content;
    }

    /**
     * Render a file and include variables in it
     *
     * @param string $__t__ Template name
     * @param array  $__v__ Template variables
     */
    protected function doRender($__t__, $__v__)
    {
        ob_start();
        extract($__v__);
        include $__t__;

        return ob_get_clean();
    }

    /**
     * Find a template
     *
     * @pram string $template Template name
     */
    private function findTemplate($template)
    {
        $namespace = '';

        if (false !== strpos($template, ':')) {
            list($namespace, $template) = explode(':', $template);
        }

        if ($namespace == '' && !isset($this->directories[$namespace])) {
            foreach ($this->directories as $ns => $dir) {
                if (file_exists($dir.DIRECTORY_SEPARATOR.$template)) {
                    $namespace = $ns;
                }
            }
        }

        if (!isset($this->directories[$namespace])) {
            throw new \OutOfBoundsException("No template where found in '$namespace'");
        }

        if (!file_exists($templatePath = $this->directories[$namespace].DIRECTORY_SEPARATOR.$template)) {
            throw new \InvalidArgumentException("template '$template' in '$templatePath' not found");
        }

        return $templatePath;
    }

    public function extend($template)
    {
        $this->parents[$this->current] = $template;
    }

    public function offsetGet($name)
    {
        return $this->helpers[$name];
    }

    public function offsetExists($name)
    {
        return isset($this->helpers[$name]);
    }

    public function offsetSet($name, $value)
    {
        $this->helpers[$name] = $value;
    }

    public function offsetUnset($name)
    {
        throw new \LogicException(sprintf('You can\'t unset a helper (%s).', $name));
    }
}
