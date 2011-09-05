<?php

namespace SimpleFramework;

class Templating
{
    /**
     * @var array An array of directories, keys are section names
     */
    protected $directories;

    protected $globalVars;

    public function __construct($directories = array())
    {
        foreach ($directories as $templatesDir) {
            if (!is_dir($templatesDir)) {
                throw new \InvalidArgumentException("templates dir '{$templatesDir}' is not a directory");
            }
        }

        $this->directories = $directories;
    }

    public function setGlobalVars(array $globalVars)
    {
        $this->globalVars = $globalVars;
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
        $templatePath   = $this->findTemplate($template);
        $vars           = array('view' => $this->globalVars) + $vars;
        $content        = $this->doRender($templatePath, $vars);

        if (null === $layout) {
            return $content;
        }

        $layoutPath = $this->findTemplate($layout);
        $layoutVars = array('content' => $content, 'view' => $this->globalVars);

        return $this->doRender($layoutPath, $layoutVars);
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

        if (!isset($this->directories[$namespace])) {
            throw new \OutOfBoundsException("No template where found in '$namespace'");
        }

        if (!file_exists($templatePath = $this->directories[$namespace].DIRECTORY_SEPARATOR.$template)) {
            throw new \InvalidArgumentException("template '$template' in '$templatePath' not found");
        }

        return $templatePath;
    }
}
