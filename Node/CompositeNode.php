<?php
namespace Node;

use SplDoublyLinkedList;

class CompositeNode extends Node {
    protected $nodes;

    public function __construct($name) {
        parent::__construct($name);

        $this->nodes = new SplDoublyLinkedList();
    }

    public function addChild(Node $node, $pos = null) {
        if(is_null($pos)) {
            $this->nodes->push($node);
        } else {
            $this->nodes->add($node, $pos);
        }
    }

    public function text($text = null) {
        $text = '';

        foreach($this->nodes as $node) {
            $text .= $node->text();
        }

        return $text;
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