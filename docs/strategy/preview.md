# Previews

While editing a content-stream one should open another window with the site in a "dummy-mode" that displays the current content-stream that is edited in its natural context to have a real preview.  
E.g. you're editing a blogpost and the preview page shows the detail view of the blogpost in the context of the blog.

The preview site is rendered with the preview-content-stream, which is saved to the db. But instead of saving the entity the whole time and generating a new preview-content-stream there is only one created, and the values are binded with knockout to the values of the ones in the form. That will allow live updates - while editing - for the site.  
If the template isn't able (or willing) to provide such knockout-bindings in preview-mode the cms can still fallback to creating and saving the preview-content-stream entity all the time.