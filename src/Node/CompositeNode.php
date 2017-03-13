<?php
namespace Wangjian\XmlParser\Node;

use SplDoublyLinkedList;
use Exception;

class CompositeNode extends Node {
    /**
     * the children nodes
     * @var SplDoublyLinkedList
     */
    protected $nodes;

    /**
     * find cache
     * @var array
     */
    protected $find_cache = array();

    /**
     * CompositeNode constructor.
     * @param $name
     */
    public function __construct($name) {
        parent::__construct($name);

        $this->nodes = new SplDoublyLinkedList();
    }

    /**
     * add a child node
     * @param Node $node
     * @param int $pos
     * @return $this
     */
    public function addChild(Node $node, $pos = null) {
        //set the child node level
        $node->level = $this->level+1;

        //set the parent node
        $node->parent = $this;

        //if the position is unset, add the node at the endding
        if(is_null($pos)) {
            $this->nodes->push($node);
        } else {
            if($pos < 0) {
                //if the position is less than 0, add the node at the beginning
                $pos = 0;
            } else if($pos > $this->nodes->count()) {
                //if the position is greater than the node count, add the node at the endding
                $pos = $this->nodes->count();
            }

            $this->nodes->add($pos, $node);
        }

        return $this->clearFindCache();
    }

    /**
     * remove the child node
     * @param Node|int $node
     * @return $this
     */
    public function removeChild($node) {
        if($node instanceof Node) {
            foreach($this->nodes as $key => $value) {
                if($value === $node) {
                    $node = $key;
                    break;
                }
            }
        }

        $this->nodes->offsetUnset($node);

        return $this->clearFindCache();
    }

    /**
     * remove all children
     * @return $this
     */
    public function removeAllChildren() {
        $this->nodes = new SplDoublyLinkedList();

        return $this->clearFindCache();
    }

    /**
     * find the child nodes
     * @param string $selector
     * @return array
     */
    public function find($selector = '*') {
        if(empty($selector)) {
            $selector = '*';
        }

        if(!empty($this->find_cache[$selector])) {
            return $this->find_cache[$selector];
        }

        if(preg_match("/^(\w+)?(\[[^ \[\]\/<>]+?=[^\/<>\[\]]+?\])*(\:(eq|gt|lt|ge|le)\(\d+\))?$/", $selector)) {
            preg_match("/^(\w+)/", $selector, $matches);
            $node_name = @$matches[1];

            preg_match_all("/\[([^ \[\]\/<>]+?)=([^\/<>\[\]]+?)\]/", $selector, $matches, PREG_SET_ORDER);
            $node_attrs = array();
            foreach($matches as $match) {
                $node_attrs[$match[1]] = $match[2];
            }

            preg_match("/\:(eq|gt|lt|ge|le)\((\d+)\)$/", $selector, $matches);
            $opt = @$matches[1];
            $opt_value = (int)@$matches[2];

            $nodes = array();
            foreach($this->nodes as $node) {
                $skip = false;

                if(!empty($node_name) && $node_name != '*') {
                    if($node_name != $node->name) {
                        $skip = true;
                    }
                }

                if(!empty($node_attrs)) {
                    foreach($node_attrs as $key => $value) {
                        if($value != @$node->attributes[$key]) {
                            $skip = true;
                            break;
                        }
                    }
                }

                if($skip) {
                    continue;
                }

                $nodes[] = $node;
            }

            if(!empty($opt)) {
                switch($opt) {
                    case 'eq':
                        $nodes = array($nodes[$opt_value]);
                        break;
                    case 'gt':
                        $nodes = array_slice($nodes, $opt_value+1);
                        break;
                    case 'lt':
                        $nodes = array_slice($nodes, 0, $opt_value);
                        break;
                    case 'ge':
                        $nodes = array_slice($nodes, $opt_value);
                        break;
                    case 'le':
                        $nodes = array_slice($nodes, 0, $opt_value+1);
                        break;
                }
            }

            return $nodes;
        } else {
            return array();
        }
    }

    /**
     * check whether contain child nodes
     * @param string $selector
     * @return bool
     */
    public function has($selector = '*') {
        $nodes = $this->find($selector);
        $this->find_cache[$selector] = $nodes;
        return (bool)count($nodes);
    }

    /**
     * only called by Node
     * @throws Exception;
     */
    public function text($text = null) {
        throw new Exception('only called by Node object');
    }

    /**
     * clear find cache
     * @return $this
     */
    protected function clearFindCache() {
        $this->find_cache = array();

        return $this;
    }

    public function __toString() {
        $space = "";
        for($i = 0; $i < $this->level; $i++) {
            $space .= "\t";
        }

        $string = "$space<$this->name";
        foreach($this->attributes as $key => $value) {
            $string .= " $key=\"$value\"";
        }
        $string .= ">\r\n";

        foreach($this->nodes as $node) {
            $string .= $node;
        }

        $string .= "\r\n$space</$this->name>\r\n";

        return $string;
    }
}