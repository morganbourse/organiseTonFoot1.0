<?php
$path = str_replace(DIRECTORY_SEPARATOR,'/',realpath(dirname(__FILE__)));

//define constants
define("ROOT_DIR", $path);
define("PHP_EXTENSION", ".php");

//import the import manager
require_once (ROOT_DIR . '/src/utils/ImportUtils.php');

//get annotation list
$dirIterator = new DirectoryIterator(ROOT_DIR_SRC . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . 'validator' . DIRECTORY_SEPARATOR . 'annotation');
$annotationList = array();
foreach($dirIterator as $file){
    if(!$file->isDot()){
        $className = explode(".", $file->getFilename());
        $className = $className[0];
        $annotationList[] = $className;
    }
}

$GLOBALS["ANNOTATION_LIST"] = $annotationList;

require_once (ROOT_DIR_SRC . 'controller/validator/bean/BeanValidator.php');
importController('bean/player/PlayerBean.php');
    
$validator = new BeanValidator();

$bean = new PlayerBean();
$bean->setLogin("testaaaaaaaaaaaaaaaa ");
$validator->validateBean($bean);

?>