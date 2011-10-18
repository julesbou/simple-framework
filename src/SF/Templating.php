<?php

namespace SF;

/*
 * This file is part of the SimpleFramework
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

    protected $currentTemplate;

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
     * @param string $template The template filename (eg: my_page.php)
     * @param array $vars Variables that get passed to template
     * @param integer $index If many templates found choose the specified index
     */
    public function render($template, $vars = array(), $index = 0)
    {
        $templatePath   = $this->findTemplate($template, $index);

        $this->current  = $templatePath;
        $this->currentTemplate  = $template;
        $this->parents[$templatePath] = null;

        $vars           = array('view' => $this) + $vars;
        $content        = $this->doRender($templatePath, $vars);

        // parent
        if ($this->parents[$templatePath] == $template) {
            $index++;
            $layoutPath = $this->findTemplate($this->parents[$templatePath], $index);
            $content .= $this->render($this->parents[$templatePath], $vars, $index);
        }

        // extend
        elseif ($this->parents[$templatePath]) {
            $layoutPath = $this->findTemplate($this->parents[$templatePath]);
            $layoutVars = array('content' => $content, 'view' => $this);
            $content = $this->render($this->parents[$templatePath], $layoutVars + $vars);
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
    private function findTemplate($template, $index = 0)
    {
        $namespace = '';

        if (false !== strpos($template, ':')) {
            list($namespace, $template) = explode(':', $template);
        }

        if ($namespace == '' && !isset($this->directories[$namespace])) {
            $foundIndex = 0;
            foreach ($this->directories as $ns => $dir) {
                if (file_exists($dir.DIRECTORY_SEPARATOR.$template) && (is_int($ns) || $ns == '')) {
                    if ($foundIndex == $index) {
                        $namespace = $ns;
                        break;
                    }
                    $foundIndex++;
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

    public function parent()
    {
        $this->parents[$this->current] = $this->currentTemplate;
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
