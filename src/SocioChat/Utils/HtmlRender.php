<?php

namespace SocioChat\Utils;

use SocioChat\DI;

class HtmlRender
{
    protected $componentPath;
    protected $template;
    protected $vars;

    public function __construct()
    {
        $ds = DIRECTORY_SEPARATOR;
        $this->componentPath = ROOT.$ds.DI::get()->getConfig()->templateDir.$ds;
    }

    public function setVars(array $vars)
    {
        $this->vars = $vars;
        return $this;
    }

    public function setTemplate($tmpl)
    {
        $this->template = $tmpl;
        return $this;
    }

    public function render()
    {
        $template = $this->componentPath . $this->template;

        if (file_exists($template)) {
            if (is_array($this->vars))
                extract($this->vars);

            try {
                ob_start();
                include $template;
                $content = ob_get_contents();
                ob_end_clean();
            } catch (\Exception $e) {
                throw new HtmlRenderException($e->getMessage());
            }
            return $content;
        }

        throw new HtmlRenderException($template.' not found!');
    }
}