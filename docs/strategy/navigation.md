# Navigation

A navigation is a tree that includes several navigationNodes. As for our context (a navigation tree) the tree has **only one root node**. For sites this represents the url `/`.

There is no splitting up of the site navigation into several "navigation-types". Like footer or sidebar-navigation. They should be constructed from nodes that are part of the navigation tree. Because every navigation-node(-path) should represent one url, we need navigations for footers included in the main nav.

Note that the Webforge\CMS\Navigation\Node is an incomplete interface. It needs set/getChildren and set/getParent. (the converter uses fromParentpointer but there is no getParent in the interface)

## Mapping to urls

every path of NavigationNodes represents a url. Note that for an internationalization-context the root node may represent the url `/language-code` so like `/de` or `/en`.


## Storage

The tree of navigation nodes is stored mainly in nested-sets format. But the representation contains some more attributes that might help to convert into other formats (yet to check). For example properties like parent, children and depth.

# Frontend-Backend

So in the Backend we have a nested list of nodes. Every node has a list of children of other nodes.  
In the Frontend we display the tree with jquery-nestable (a plugin). This needs a nested ul <-> li list => no need to convert.  
When we save the tree to the db we use the `Webforge\Doctrine\NavigationBridge` which converts from parentPointer-Array to the nested set. This is also very easy, because we can manage the parent pointer in the frontend easily. => no need for conversion.