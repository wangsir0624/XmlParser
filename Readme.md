###XmlParser
用PHP写的XML解析器，使用简单，效率高。

###Usage
####XML读取
```PHP
//从XML文档中读取
$xml = XmlParser::loadFromFile('./data.xml');

//从字符串中读取，字符串不能含有声明行
$xml = XmlParser::loadFromString('<filter name="test"><whitelist><directory>./</directory></whitelist></filter>');

<<<<<<< HEAD
```
=======
````
>>>>>>> 85dcdb78ff1acbf5445a2e34820a7b38b64af9e1

####获取子节点
可以使用find函数来获取节点的子节点，此函数只会获取直接子节点
```PHP
/*
 * 采用类似jquery的选择器
 * 支持标签选择器，属性选择器以及过滤选择器
 */

//获取所有子节点
var_dump($xml->find()); //或者var_dump($xml->find('*'));

<<<<<<< HEAD
//获取指定标签子节点
=======
//获取标签子节点
>>>>>>> 85dcdb78ff1acbf5445a2e34820a7b38b64af9e1
var_dump($xml->find('testsuites'));

//获取带有特定属性的子节点
var_dump($xml->find('testsuites[test=test]'));

//筛选子节点
var_dump($xml->find('testsuites[test=test]:ge(0)')); //支持eq, gt, lt, ge, le五中过滤选择器
<<<<<<< HEAD
```

=======
````
>>>>>>> 85dcdb78ff1acbf5445a2e34820a7b38b64af9e1
####检查是否包含特定子节点
可以采用has函数来检查是否含有特定的子节点，此函数接受find函数相同的参数值

####获取兄弟节点
```PHP
//获取节点的序号值
$testsuite = $xml->find('testsuites:eq(0)');
echo $testsuite->index();

//获取节点的父节点
var_dump($testsuite->parent());

//获取所有兄弟节点
var_dump($testsuite->sibings());

//获取上一个兄弟节点
var_dump($testsuite->prev());

//获取下一个兄弟节点
var_dump($testsuite->next());
<<<<<<< HEAD
```
=======
>>>>>>> 85dcdb78ff1acbf5445a2e34820a7b38b64af9e1

####子节点的添加与删除
```PHP
//创建子节点
$node1 = XmlParser::createCompositeNode('test', ['attr1' => 'value1']);
$node2 = XmlParser::createNode('test2', ['arrt2' => 'value2']);

//添加子节点
$node1->addChild($node2);
$xml->addChild($node1);

//删除节点
$xml->removeChild($node1);    //$xml->removeChild(1); 也可以传递子节点序号来进行删除

//删除所有子节点
$xml->removeAllChild();
```

####节点属性获取
```PHP
$directory = $xml->find('filter')[0]->find('whitelist')[0]->find('directory')[0];

//获取节点名字
echo $directory->name();

//获取节点的值
echo $directory->text();    //$directory->text('./test/'); 可以通过传递参数来设置节点的值

//获取节点属性
echo $directory->attr('test');    //$directory->attr('test', 'test1'); 可以通过传递参数来设置节点的属性
```