<?php

namespace Laminas\Code\Generator;

class BodyGenerator extends AbstractGenerator
{
    /** @var string */
    protected $content = '';

    /**
     * @param  string $content
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = (string) $content;
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function generate()
    {
        return $this->getContent();
    }
}
