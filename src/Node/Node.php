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
     * Node constructor.
     * @param $name
     */
    public function __construct($name) {
        $this->name = $name;
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
     * get the node name
     * @return string
     */
    public function getName() {
        return $this->name;
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

    /**
     * set the node level
     * @param $level
     * @return $this
     */
    public function setLevel($level) {
        $this->level = $level;

        return $this;
    }
}