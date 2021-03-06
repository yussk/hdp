<?php
// 環境
define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));

require_once 'Zend/Rest/Server.php';
require_once 'Zend/Config/Ini.php';
require_once '../model/kv.class.php';
require_once 'Zend/Oauth/Token/Access.php';
require_once 'Zend/Service/Twitter.php';
require_once 'Zend/Db/Expr.php';

class HappyDinnerAPI
{
    public function __construct()
    {
        // DB
        $config = new Zend_Config_Ini('../configs/application.ini', APPLICATION_ENV);
        $db = Zend_Db::factory($config->database);
        Zend_Db_Table_Abstract::setDefaultAdapter($db);
    }

    public function getkvs($key){
        $kv = new KV();
        $select = $kv->select()->where('`key` = ?', $key);
        $row = $kv->fetchrow($select);
        return array('value' => $row->value);
    }

    public function setkvs($key, $value){
        $kv = new KV();
        try {
             $kv->insert(array('key' => $key, 'value' => $value));
        } catch ( Exception $e ){
             // insert 失敗時は update することで upsert を実現
             $where = $kv->getAdapter()->quoteInto('`key` = ?', $key);
             $kv->update(array('value' => $value), $where);
        }

    }
    
    // エラー起きてます・・・
    public function getCategory() {
        require_once '../model/Category.class.php';
        $category = new Category();
        $select = $category->select()->where('display = ?', 1);
        $rows = $category->fetchAll($select);
        $response = array();
        foreach ($rows as $row) {
            $response += array(
                "id"    => $row->id, 
                "name"  => $row->category_name,
            );
        }
        return $response;
    }
}

$server = new Zend_Rest_Server();
$server->setClass('HappyDinnerAPI');
$server->handle();
