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

Usage
-----

    <?php
    
    use Clay\Clay;
    
    $data = array(
        'this' => array(
            'is' => 'just',
            'a' => array(
                'simple',
                'test'
            )
        ),
        'isnt' => 'anything'
    );

    $clay = new Clay(
        $data,
        array(
            'this.is.a' => array(
                'upper' => function(&$new) { $new = strtoupper($new); return true; }
            ),
            'this.is.a.simple.test' => array(
                'type' => function(&$new) { return !is_numeric($new); },
                'merge' => function(&$new, $current) { return $new = array_merge((array)$current, (array)$new); }
            ),
            'this.is.test.1' => array(
                'convert' => function(&$new) { return $new = preg_replace('/[^\d]+/', '', $new); },
                'type' => function(&$new) { return is_numeric($new); },
                'merge_higher' => function(&$new, $current) {
                    if($new <= max((array)$current)) return false;
                    return $new = array_merge((array)$current, (array)$new);
                }
            ),
            'this.is.test.2' => array(
                'replace_longer' => function(&$new, $current) {
                    if(strlen($new) <= max(array_map('strlen', (array)$current))) return false;
                    return $new;
                }
            ),
            'this.is.test.3' => array(
                'numeric_lower' => function(&$new, $current) {
                    if(empty($current)) return $new;
                    $newnum = preg_replace('/[^\d]+/', '', $new);
                    $curnum = preg_replace('/[^\d]+/', '', $current);
                    return $new = $newnum < $curnum ? $new : $current;
                }
            )
        )
    );
    
    $clay->set('this.is.a.simple.test', '100 pounds');
    $clay->set('this.is.a.simple.test', '200 pounds');
    $clay->set('this.is.a.simple.test', '300 pounds');
    $clay->set('this.is.test.1', '200 pounds');
    $clay->set('this.is.test.1', '100 pounds');
    $clay->set('this.is.test.1', '300 pounds');
    $clay->set('this.is.test.2', '200 pounds');
    $clay->set('this.is.test.2', '100 pounds and 50 pence');
    $clay->set('this.is.test.2', '300 pounds');
    $clay->set('this.is.test.3', '200 pounds');
    $clay->set('this.is.test.3', '100 pounds');
    $clay->set('this.is.test.3', '300 pounds');
    
    foreach($clay as $k => $v) {
        print_r(array($k => $v));
    }
