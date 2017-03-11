<?php
/**
 * XML解析器
 * @author Wangjian
 * @email 1636801376@qq.com
 * 解析思路为：
 * 假设有这么一个XML文件 <books><book><title>php</title><author>wangjian</author></book><book></book></books>
 * 依次解析文件，如果是<*开头的，表示遇到一个开始标签，初始化一个books节点，继续往下执行，又遇到一个开始标签，初始化book节点，并将之前的books节点压入栈中，然后把book节点添加到栈最上方的节点的子节点中
 * 继续执行，遇到title标签，初始化title节点，并将book节点压入到栈中，此时栈顶部节点为book节点，然后把title节点加入到栈顶部节点的子节点中
 * 继续执行，遇到文本标签，将"php"赋值给title标签的text属性，再往下执行，遇到</这样开头的结束标签。author节点的解析过程与title节点同理
 * 继续执行，遇到book节点的结束标签，将book节点从栈中弹出，此时栈顶部节点又是books节点
 * 继续执行，又遇到一个book节点，将book节点加入到栈顶部节点（即为books节点）的子节点中
 * 循环如上过程，既可以得到一颗节点树
 */
namespace Wangjian\XmlParser\Parser;

use SplStack;
use Node\Node;
use Node\CompositeNode;

class XmlParser {
    /**
     * the default version of the xml document
     * @const string
     */
    const VERSION = '1.0';

    /**
     * the default charset of the xml document
     * @const string
     */
    const ENCODING = 'UTF-8';

    /**
     * the xml version
     * @var string
     */
    protected static $version;

    /**
     * the xml encoding
     * @var string
     */
    protected static $encode;

    /**
     * node stack
     * @var SplStack
     */
    protected static $stack;

    /**
     * root node of the document
     * @var Node
     */
    protected static $root_node;

    /**
     * the current node name
     * @var string
     */
    protected static $tmp_node_name;

    /**
     * the current node attributes
     * @var array
     */
    protected static $tmp_node_attributes;

    /**
     * @var int
     */
    protected static $current_times;

    /**
     * whether the document contains the declaration line
     * @va
     */
    protected static $with_header;

    /**
     * the node level
     * @var int
     */
    protected static $level;

    /**
     * create a node tree from a XML document
     * @param $filename
     * @return Node  return false on failure
     * @throws \RuntimeException
     */
    public static function loadFromFile($filename) {
        $handle = @fopen($filename, 'r');

        if(!$handle) {
            throw RuntimeException("Couldn't open the file $filename");
        }

        //initialize the parser
        self::init();

        //read the file character by character until come with an '>' character
        $line = '';
        while(($char = fgetc($handle)) !== false) {
            $line .= $char;

            if($char == '>') {
                $line = trim(iconv(self::$encode, 'UTF-8', trim($line)));

                //parse the line recursively
                while($line) {
                    self::$current_times++;

                    $startWith = substr($line, 0, 2);

                    if($startWith == '<?') {
                        //when the line starts with '<?', it is a declaration
                        if(self::$current_times == 1) {
                            if(preg_match("/^<\?xml +([^ \/<>]+=\"[^\/<>]+\")* *\?>/", $line)) {
                                self::$with_header = 1;

                                preg_match_all("/ +([^ \/<>]*?)=\"([^\/<>]*?)\"/", $line, $matches, PREG_SET_ORDER);
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

                                $line = preg_replace("/^<\?xml +([^ \/<>]+=\"[^\/<>]+\")* *\?>/", '', $line);
                            } else {
                                return false;
                            }
                        } else {
                            return false;
                        }
                    } else if($startWith == '</') {
                        //when the line starts with '</', it is an ending tag
                        if(self::$current_times >= 2+self::$with_header) {
                            if (self::$tmp_node_name != '') {
                                $node_name = self::$tmp_node_name;
                                self::$tmp_node_name = '';
                                self::$tmp_node_attributes = array();
                            } else {
                                $node = self::$stack->pop();
                                self::$level--;
                                $node_name = $node->getName();
                            }

                            if (preg_match("/^<\/" . $node_name . ">/", $line)) {
                                $node_name = '';

                                $line = preg_replace("/^<\/[^<>]+?>/", '', $line);
                            } else {
                                echo "$line\r\n2";
                                return false;
                            }
                        } else {
                            return false;
                        }
                    } else {
                        if(substr($startWith, 0, 1) == '<') {
                            //when the line starts with '<*', it is a starting tag
                            if(self::$tmp_node_name != '') {
                                $node = self::createCompositeNode(self::$tmp_node_name, self::$tmp_node_attributes);
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

                            if(preg_match("/^<[^<>]+ *([^ \/<>]+=\"[^\/<>]+\")* *>/", $line)) {
                                preg_match("/^<([^ \/<>]+?)[ >]/", $line, $matches);
                                if(empty($matches[1])) {
                                    return false;
                                }

                                self::$tmp_node_name = $matches[1];

                                preg_match_all("/ +([^ \/<>]*?)=\"([^\/<>]*?)\"/", $line, $matches, PREG_SET_ORDER);
                                self::$tmp_node_attributes = array();
                                foreach($matches as $match) {
                                    self::$tmp_node_attributes[$match[1]] = $match[2];
                                }

                                $line = preg_replace("/^<([^\/<>]+) *([^<>]+=\"[^<>]+\")* *>/", '', $line);
                            } else {
                                return false;
                            }
                        } else {
                            //otherwise, it is pure text
                            if(self::$current_times >= 2+self::$with_header) {
                                preg_match("/^[^<>]+/", $line, $matches);
                                $node = self::createNode(self::$tmp_node_name, self::$tmp_node_attributes);
                                $node->setLevel(self::$level);

                                if (!self::$stack->isEmpty()) {
                                    self::$stack->top()->addChild($node);
                                }

                                $node->text($matches[0]);

                                $line = preg_replace("/^[^<>]+/", '', $line);
                            } else {
                                return false;
                            }
                        }
                    }
                }
            }
        }

        return self::$root_node;
    }

    /**
     * create a node tree from XML string
     * @param $string
     * @return Node  return false on failure
     */
    public static function loadFromString($line) {
        self::init();

        //parse the string recursively
        while($line) {
            self::$current_times++;

            $startWith = substr($line, 0, 2);

           if($startWith == '</') {
                //when the line starts with '</', it is an ending tag
                if(self::$current_times >= 2+self::$with_header) {
                    if (self::$tmp_node_name != '') {
                        $node_name = self::$tmp_node_name;
                        self::$tmp_node_name = '';
                        self::$tmp_node_attributes = array();
                    } else {
                        $node = self::$stack->pop();
                        self::$level--;
                        $node_name = $node->getName();
                    }

                    if (preg_match("/^<\/" . $node_name . ">/", $line)) {
                        $node_name = '';

                        $line = preg_replace("/^<\/[^<>]+?>/", '', $line);
                    } else {
                        echo "$line\r\n2";
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                if (substr($startWith, 0, 1) == '<') {
                    //when the line starts with '<*', it is a starting tag
                    if (self::$tmp_node_name != '') {
                        $node = self::createCompositeNode(self::$tmp_node_name, self::$tmp_node_attributes);
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

                    if (preg_match("/^<[^<>]+ *([^ \/<>]+=\"[^\/<>]+\")* *>/", $line)) {
                        preg_match("/^<([^ \/<>]+?)[ >]/", $line, $matches);
                        if (empty($matches[1])) {
                            return false;
                        }

                        self::$tmp_node_name = $matches[1];

                        preg_match_all("/ +([^ \/<>]*?)=\"([^\/<>]*?)\"/", $line, $matches, PREG_SET_ORDER);
                        self::$tmp_node_attributes = array();
                        foreach ($matches as $match) {
                            self::$tmp_node_attributes[$match[1]] = $match[2];
                        }

                        $line = preg_replace("/^<([^\/<>]+) *([^<>]+=\"[^<>]+\")* *>/", '', $line);
                    } else {
                        return false;
                    }
                } else {
                    //otherwise, it is pure text
                    if (self::$current_times >= 2 + self::$with_header) {
                        preg_match("/^[^<>]+/", $line, $matches);
                        $node = self::createNode(self::$tmp_node_name, self::$tmp_node_attributes);
                        $node->setLevel(self::$level);

                        if (!self::$stack->isEmpty()) {
                            self::$stack->top()->addChild($node);
                        }

                        $node->text($matches[0]);

                        $line = preg_replace("/^[^<>]+/", '', $line);
                    } else {
                        return false;
                    }
                }
            }
        }

        return self::$root_node;
    }

    /**
     * ouput the xml node tree into a file
     * @param $outpath
     * @param Node $xml
     * @return int  return the output bytes on success, and false on failure
     */
    public static function save($outpath, Node $xml) {
        self::init();

        $output = "<?xml version=\"".self::$version."\" encoding=\"".self::$encode."\"?>\r\n\r\n";

        return file_put_contents($outpath, $output.$xml);
    }

    /**
     * create a leaf node
     * @param string $node_name
     * @param array $node_attr
     * @return Node
     */
    public static function createNode($node_name, $node_attr = array()) {
        $node = new Node($node_name);

        foreach($node_attr as $key => $value) {
            $node->attribute($key, $value);
        }

        return $node;
    }

    /**
     * create a branch node
     * @param string $node_name
     * @param array $node_attr
     * @return Node
     */
    public static function createCompositeNode($node_name, $node_attr = array()) {
        $node = new CompositeNode($node_name);

        foreach($node_attr as $key => $value) {
            $node->attribute($key, $value);
        }

        return $node;
    }

    /**
     * initialize the parser
     */
    protected static function init() {
        self::$version = self::VERSION;
        self::$encode = self::ENCODING;
        self::$stack = new SplStack();
        self::$root_node = null;
        self::$tmp_node_name = '';
        self::$tmp_node_attributes = array();
        self::$current_times = 0;
        self::$with_header = 0;
        self::$level = 0;
    }
}