<?php
namespace Node;

class Node {
    protected $name = '';

    protected $attributes = array();

    protected $text = '';

    protected $level;

    public function __construct($name) {
        $this->name = $name;
    }

    public function text($text = null) {
        if(is_null($text)) {
            return $this->text;
        } else {
            $this->text = $text;

            return true;
        }
    }

    public function attribute($key, $value = null) {
        if(is_null($value)) {
            return $this->attributes[$key];
        } else {
            $this->attributes[$key] = $value;
        }
    }

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
        $string .= ">\r\n\t$space$this->text\r\n$space</$this->name>\r\n";

       return $string;
    }

    public function setLevel($level) {
        $this->level = $level;
    }
}