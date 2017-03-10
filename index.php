<?php
//引入自动加载文件
require_once __DIR__.'/autoload.php';

$xml = \Parser\XmlParser::loadFromString('<filter><whitelist><directory>./</directory></whitelist></filter>');
var_dump($xml);
\Parser\XmlParser::save('./output.xml', $xml);
