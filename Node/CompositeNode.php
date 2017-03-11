<?php
namespace Node;

use SplDoublyLinkedList;
use Exception;

class CompositeNode extends Node {
    /**
     * the children nodes
     * @var SplDoublyLinkedList
     */
    protected $nodes;

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
        $node->setLevel($this->level+1);

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

        return $this;
    }

    /**
     * remove the child node
     * @param Node|int $node
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
    }

    /**
     * remove all children
     */
    public function removeAllChildren() {
        $this->nodes = new SplDoublyLinkedList();
    }

    /**
     * get the children node
     * @param string $node_name  if the node name is unset, return all children node
     * @return array
     */
    public function getChildren($node_name = null) {
        $nodes = array();

        foreach($this->nodes as $node) {
            if(!is_null($node_name) && $node->name != $node_name) {
                continue;
            }

            $nodes[] = $node;
        }

        return $nodes;
    }

    /**
     * only called by Node
     * @throws Exception;
     */
    public function text($text = null) {
        throw new Exception('only called by Node object');
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