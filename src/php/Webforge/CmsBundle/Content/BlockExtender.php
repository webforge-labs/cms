<?php

namespace Webforge\CmsBundle\Content;

interface BlockExtender
{

    /**
     * Converts special properties from the backend model to the view model used in templates
     *
     * for example consider a markdown block:
     *
     * ```
     *   {
     *     "type": "markdown",
     *     "content": "# headline",
     *     "uuid": "xxxxxxxxxx-xxxxx-xxxxxxxxx"
     *   }
     * ```
     *
     * will be extended like this:
     *
     * ```
     *   {
     *     "type": "markdown",
     *     "content": "# headline",
     *     "contentHtml": "<h1>headline</h1>"
     *     "uuid": "xxxxxxxxxx-xxxxx-xxxxxxxxx"
     *   }
     * ```
     *
     * @param array $blocks the array of blocks how it was written by the content-manager.js
     * @param stdclass $context pass whatever you like to the blockExtenders
     * @return bool if the extender has extended the block it returns true
     */
    public function extend(array &$blocks, \stdClass $context);
}
