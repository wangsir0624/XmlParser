<?php
namespace Wangjian\XmlParser\Test;

use Wangjian\XmlParser\XmlParser;

require_once __DIR__.'/../../../autoload.php';

//从字符串中解析XML
//$xml = XmlParser::loadFromString('<filter name="test"><whitelist><directory>./</directory></whitelist></filter>');

//从XML文档中解析XML
$xml = XmlParser::loadFromFile('./data.xml');

//获取节点名字
//echo $xml->name();

//获取节点的子节点
//var_dump($xml->find());
//var_dump($xml->find('testsuites');
var_dump($xml->find('testsuites:eq(0)'));
//var_dump($xml->has('testsuites'));

//$node = $xml->find()[0];
//var_dump($xml->find());
//var_dump($node->parent());
//echo $node->index();
//var_dump($node->siblings());

//删除节点
//方法一
//$xml->removeChild(1);
//方法二
//$node = $xml->find()[0];
//$xml->removeChild($node);

//增加节点
//$testNode = XmlParser::createCompositeNode('test', ['attr1' => 'value1']);
//$testNode2 = XmlParser::createNode('test2', ['arrt2' => 'value2']);
//$testNode->addChild($testNode2);
//$xml->addChild($testNode);

$directory = $xml->find('filter')[0]->find('whitelist')[0]->find('directory')[0];
//echo $directory->name();
//echo $directory->text();

//导出Xml
//XmlParser::save('./output.xml', $xml);

//var_dump($xml);