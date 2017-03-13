<?php
namespace Wangjian\XmlParser\Node;

class Node {
    /**
     * node name
     * @var string
     */
    protected $name = '';

    /**
     * node attributes
     * @var array
     */
    protected $attributes = array();

    /**
     * node text
     * @var string
     */
    protected $text = '';

    /**
     * node level
     * @var int
     */
    protected $level;

    /**
     * parent node
     * @var Node
     */
    protected $parent;

    /**
     * Node constructor.
     * @param $name
     */
    public function __construct($name) {
        $this->name = $name;
        $this->level = 0;
        $this->parent = null;
    }

    /**
     * get/set the node text
     * @param null $text
     * @return string|$this
     */
    public function text($text = null) {
        if(is_null($text)) {
            return $this->text;
        } else {
            $this->text = $text;

            return $this;
        }
    }

    /**
     * get/set the node attribute
     * @param $key
     * @param null $value
     * @return mixed|$this
     */
    public function attribute($key, $value = null) {
        if(is_null($value)) {
            return $this->attributes[$key];
        } else {
            $this->attributes[$key] = $value;
            return $this;
        }
    }

    /**
     * get the parent node
     * @return Node
     */
    public function parent() {
        return $this->parent;
    }

    /**
     * get the index of this node
     * @return int
     */
    public function index() {
        $index = 0;

        if(is_null($this->parent)) {
            return $index;
        }

        foreach($this->parent->nodes as $node) {
            if($this === $node) {
                return $index;
            }

            $index++;
        }
    }

    /**
     * get the node name
     * @return string
     */
    public function name() {
        return $this->name;
    }

    /**
     * get the sublings
     * @param Node $node
     * @return array
     */
    public function siblings() {
        $siblings = array();

        foreach($this->parent->nodes as $node) {
            if($node !== $this) {
                $siblings[] = $node;
            }
        }

        return $siblings;
    }

    /**
     * get the previous sibling node
     * @return Node|null
     */
    public function prev() {
        if($this->index() == 0) {
            return null;
        }

        return $this->parent->find()[$this->index()-1];
    }

    /**
     * get the next sibling node
     * @return Node|null
     */
    public function next() {
        if($this->index() == $this->parent->nodes->count()-1) {
            return null;
        }

        return $this->parent->find()[$this->index()+1];
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
        $string .= ">$this->text</$this->name>\r\n";

       return $string;
    }
}