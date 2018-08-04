<?php

namespace Webforge\CmsBundle\Content;

class Markdowner
{
    private $markdownParser;

    public function __construct($markdownParser)
    {
        $this->markdownParser = $markdownParser;
    }

    /**
     * Transforms the markdown into html
     *
     * with some special extensions
     * @param  string $markdown
     * @return string html
     */
    public function transformMarkdown($markdown)
    {
        // >> might have a special meaning for quotes?
        $markdown = str_replace(array('<<', '>>'), array('«', '»'), $markdown);

        $html = $this->markdownParser->transformMarkdown($markdown);

        $html = str_replace(
            [' -- '],
            [' – '],
            $html
        );

        return $html;
    }

    /**
     * Transforms text (which isnt markdown) with some handy replacements (ndashes, etc)
     */
    public function transformText($text)
    {
        $text = str_replace(
            [' -- '],
            [' – '],
            $text
        );

        return $text;
    }
}
