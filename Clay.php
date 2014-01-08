<?php
namespace Clay;

/**
 * A flexible data model for validating, filtering and processing data as it
 * gets passed in.
 * 
 * Clay uses a map to define processing at various levels of a tree. Processing
 * is handled by callback functions. Processing bubbles up from root to leaf.
 * Processors can be overwritten by using a key for the processor item, or added
 * regardless with no key e.g.
 * $processors = array(
 *  'type' => function() {},
 *  'valid' => function() {},
 *  function() {},
 *  function() {}
 * )
 * 
 * Functions can take one or two args, the first being the new value, the second
 * being the existing value. If the function modifies the first arg, it should
 * accept it as a reference. Functions should return false/non-false. If false,
 * value is discarded.
 * 
 * Using dot notation to access map members in a flat format should avoid tree
 * traversal and make access quicker.
 * 
 * */
class Clay implements \IteratorAggregate {
    
    private $data = array();
    private $map = array();
    
    function __construct($data = array(), $map = array()) {
        $this->initialiseMap($map);
        $this->setData($data);
    }
    
    function initialiseMap($map = array()) {
        $this->map = $map;
    }
    
    function setData($data = array()) {
        foreach($this->flatten($data) as $path => $value) {
            $this->set($path, $value);
        }
    }
    
    private function flatten($data, $key = null, $chain = array(), $flat = array()) {
        // Put the key on the chain
        if(!is_null($key)) $chain[] = $key;
        
        // Recurse the data
        if(is_array($data)) {
            foreach($data as $key => $value) {
                $flat = $this->flatten($value, $key, $chain, $flat);
            }
        } else {
            // Add the value to the flat array
            $flat[implode('.', $chain)] = $data;
        }
        
        return $flat;
    }
    
    function set($path, $value) {
        // Path as list
        if(!is_array($path)) $path = explode('.', $path);
        
        // Leaf item
        $leaf = array_pop($path);
        
        // Initialise the location reference
        $location =& $this->data;
        
        // Initialise the map reference
        $mapRef = array();
        
        // Initialise processors
        $processors = array();
        
        foreach($path as $branch) {
            // Equate [] to index
            if('[]' == $branch) $branch = count((array)$location);
            
            // Initialise the branch
            if(!isset($location[$branch])) $location[$branch] = array();
            
            // Gather processing
            $mapRef[] = $branch;
            if(isset($this->map[implode('.', $mapRef)])) {
                $processors = array_replace($processors, (array)$this->map[implode('.', $mapRef)]);
            }
            
            // Update reference
            $location =& $location[$branch];
            // This is a branch so make sure it's an array
            $location = (array)$location;
        }
        
        if('[]' == $leaf) $leaf = count((array)$location);
        
        // get the leaf processors
        $mapRef[] = $leaf;
        if(isset($this->map[implode('.', $mapRef)])) {
            $processors = array_replace($processors, (array)$this->map[implode('.', $mapRef)]);
        }
        
        foreach($processors as $processor) {
            // Get the current value
            $current = isset($location[$leaf]) ? $location[$leaf] : null;
            if(!@call_user_func_array($processor, array(&$value, $current))) return false;
        }
        
        return $location[$leaf] = $value;
    }
    
    public function get($path) {
        // Path as list
        if(!is_array($path)) $path = explode('.', $path);
        
        // Leaf item
        $leaf = array_pop($path);
        
        // Initialise the location reference
        $location =& $this->data;
        foreach($path as $branch) {
            
            // Does the branch exist? If not, don't continue
            if(!isset($location[$branch])) return null;
            
            // Update reference
            $location =& $location[$branch];
        }
        
        if(!isset($location[$leaf])) return null;
        return $location[$leaf];
    }
    
    public function getIterator() {
        return new \ArrayIterator($this->data);
    }
    
    public function export() {
        return $this->data;
    }
    
}
