clay
====

Clay is a highly malleable modelling class.

How it works
------------

Using dot notation with get() and set() methods, clay allows access and processing of any hierarhy of data.

The smarts comes from the moel map. The map defines how data is processed in the tree/hierarchy. Any branch or leaf can have a set of closures passed to it which handles the data in some way. For example:

####Map:
    root.branch.twig = function { append value }
    root.branch.twig.leaf = function { overwrite value only if higher }

Processing of a value is a combination of all processing leading up through the tree. To override processing you can assign a key to each function and override the key with a new function higher up the tree. e.g.:

####Map
    root.branch = function { strtoupper; }
    root.branch.twig.leaf = function { strtolower; ucwords; }

####Use
    set('root.branch.test.[]', 'this is')   # root.branch.test = array('THIS IS')
    set('root.branch.test.[]', 'a test')    # root.branch.test = array('THIS IS', 'A TEST')
    set('root.branch.twig.leaf', 'example') # root.branch.twig.leaf = 'Example'

Things get interesting with tests for higher or lower, overwriting, and trickle-up values.

A Clay model can be extended or defined at construction with an array map and data.

Proposals
---------

Properties should be accessible through standard object notation e.g.:

    $model->root->branch->twig->leaf[0]->vein = $value;
    $value = $model->root->branch->twig->leaf[0]->vein;

I don't have the time to implement these (major) changes right now but they will come in the future.
