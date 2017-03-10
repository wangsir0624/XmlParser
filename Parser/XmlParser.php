<?php
namespace Parser;

use SplStack;
use Node\Node;
use Node\CompositeNode;

class XmlParser {
    protected static $version = '1.0';

    protected static $encode = 'UTF-8';

    protected static $stack;

    protected static $root_node;

    protected static $tmp_node_name;

    protected static $tmp_node_attributes;

    protected static $current_line;

    protected static $level;

    public static function loadFromFile($filename) {
        $handle = fopen($filename, 'r');

        if(!$handle) {
            throw RuntimeException("Couldn't open the file $filename");
        }

        self::init();

        $line = '';
        while(($char = fgetc($handle)) !== false) {
            $line .= $char;

            if($char == '>') {
                $line = iconv(self::$encode, 'UTF-8', trim($line));

                if($line == '') {
                    continue;
                }

                self::$current_line++;

                while($line) {
                    $startWith = substr($line, 0, 2);

                    if($startWith == '<?') {
                        if(self::$current_line == 1) {
                            if(preg_match("/^<\?xml *([^<>]+=\"[^<>]+\")* *\?>/", $line)) {
                                preg_match_all("/ +([^<>]*?)=\"([^<>]*?)\"/", $line, $matches, PREG_SET_ORDER);
                                foreach($matches as $match) {
                                    switch($match[1]) {
                                        case 'version':
                                            self::$version = $match[2];
                                            break;
                                        case 'encoding':
                                            self::$encode = $match[2];
                                            break;
                                    }
                                }

                                $line = preg_replace("/^<\?xml *([^<>]+=\"[^<>]+\")* *\?>/", '', $line);
                            } else {
                                echo "$line\r\n1";
                                return false;
                            }
                        } else {
                            return false;
                        }
                    } else if($startWith == '</') {
                        if(self::$tmp_node_name != '') {
                            $node_name = self::$tmp_node_name;
                            self::$tmp_node_name = '';
                            self::$tmp_node_attributes = array();
                        } else {
                            $node = self::$stack->pop();
                            self::$level--;
                            $node_name = $node->getName();
                        }

                        if(preg_match("/^<\/".$node_name.">/", $line)) {
                            $node_name = '';

                            $line = preg_replace("/^<\/[^<>]+?>/", '', $line);
                        } else {
                            echo "$line\r\n2";
                            return false;
                        }
                    } else {
                        if(substr($startWith, 0, 1) == '<') {
                            if(self::$tmp_node_name != '') {
                                $node = self::createCompositeNode();
                                $node->setLevel(self::$level);

                                if(!(self::$root_node instanceof Node)) {
                                    self::$root_node = $node;
                                }

                                if(!self::$stack->isEmpty()) {
                                    self::$stack->top()->addChild($node);
                                }

                                self::$stack->push($node);
                                self::$level++;
                            }

                            if(preg_match("/^<[^<>]+ *([^<>]+=\"[^<>]+\")* *>/", $line)) {
                                preg_match("/^<([^<>]+?)[ >]/", $line, $matches);
                                if(empty($matches[1])) {
                                    return false;
                                }

                                self::$tmp_node_name = $matches[1];

                                preg_match_all("/ +([^<>]*?)=\"([^<>]*?)\"/", $line, $matches, PREG_SET_ORDER);
                                self::$tmp_node_attributes = array();
                                foreach($matches as $match) {
                                    self::$tmp_node_attributes[$match[1]] = $match[2];
                                }

                                $line = preg_replace("/^<([^\/<>]+) *([^<>]+=\"[^<>]+\")* *>/", '', $line);
                            } else {
                                return false;
                            }
                        } else {
                            preg_match("/^[^<>]+/", $line, $matches);

                            $node = self::createNode();
                            $node->setLevel(self::$level);

                            if(!self::$stack->isEmpty()) {
                                self::$stack->top()->addChild($node);
                            }

                            $node->text($matches[0]);

                            $line = preg_replace("/^[^<>]+/", '', $line);
                        }
                    }
                }
            }
        }

        return self::$root_node;
    }

    public static function loadFromString($string) {
        self::init();

        while($string) {
            $startWith = substr($string, 0, 2);

            if($startWith == '</') {
                if(self::$tmp_node_name != '') {
                    $node_name = self::$tmp_node_name;
                    self::$tmp_node_name = '';
                    self::$tmp_node_attributes = array();
                } else {
                    $node = self::$stack->pop();
                    self::$level--;
                    $node_name = $node->getName();
                }

                if(preg_match("/^<\/".$node_name.">/", $string)) {
                    $node_name = '';

                    $string = preg_replace("/^<\/[^<>]+?>/", '', $string);
                } else {
                    return false;
                }
            } else {
                if (substr($startWith, 0, 1) == '<') {
                    if (self::$tmp_node_name != '') {
                        $node = self::createCompositeNode();
                        $node->setLevel(self::$level);

                        if (!(self::$root_node instanceof Node)) {
                            self::$root_node = $node;
                        }

                        if (!self::$stack->isEmpty()) {
                            self::$stack->top()->addChild($node);
                        }

                        self::$stack->push($node);
                        self::$level++;
                    }

                    if (preg_match("/^<[^<>]+ *([^<>]+=\"[^<>]+\")* *>/", $string)) {
                        preg_match("/^<([^<>]+?)[ >]/", $string, $matches);
                        if (empty($matches[1])) {
                            return false;
                        }

                        self::$tmp_node_name = $matches[1];

                        preg_match_all("/ +([^<>]*?)=\"([^<>]*?)\"/", $string, $matches, PREG_SET_ORDER);
                        self::$tmp_node_attributes = array();
                        foreach ($matches as $match) {
                            self::$tmp_node_attributes[$match[1]] = $match[2];
                        }

                        $string = preg_replace("/^<([^\/<>]+) *([^<>]+=\"[^<>]+\")* *>/", '', $string);
                    } else {
                        return false;
                    }
                } else {
                    preg_match("/^[^<>]+/", $string, $matches);

                    $node = self::createNode();
                    $node->setLevel(self::$level);

                    if (!self::$stack->isEmpty()) {
                        self::$stack->top()->addChild($node);
                    }

                    $node->text($matches[0]);

                    $string = preg_replace("/^[^<>]+/", '', $string);
                }
            }
        }

        return self::$root_node;
    }

    public static function save($outpath, Node $xml) {
        self::init();

        $output = "<?xml version=\"".self::$version."\" encoding=\"".self::$encode."\"?>\r\n\r\n";

        return file_put_contents($outpath, $output.$xml);
    }

    protected static function createNode() {
        $node = new Node(self::$tmp_node_name);

        foreach(self::$tmp_node_attributes as $key => $value) {
            $node->attribute($key, $value);
        }

        return $node;
    }

    protected static function createCompositeNode() {
        $node = new CompositeNode(self::$tmp_node_name);

        foreach(self::$tmp_node_attributes as $key => $value) {
            $node->attribute($key, $value);
        }

        return $node;
    }

    protected static function init() {
        self::$version = '1.0';
        self::$encode = 'UTF-8';
        self::$stack = new SplStack();
        self::$root_node = null;
        self::$tmp_node_name = '';
        self::$tmp_node_attributes = array();
        self::$current_line = 0;
        self::$level = 0;
    }
}