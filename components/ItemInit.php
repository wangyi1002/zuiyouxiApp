<?php
namespace app\components;
use app\models\UserData;
use app\models\Project;
use yii\base\Behavior;
use yii\caching\TagDependency;

class ItemInit extends Behavior{

    const SESSION_ITEM = 'seven_item_project';

    const CACHE_TAG = 'seven.project';

    const CACHE_DURATION = 3600;

    private $_item; # 当前项目

    private $_userData; # 用户扩展数据

    public function init()
    {
        if(!\Yii::$app->user->isGuest){
            $this->_userData =UserData::getData(\Yii::$app->user->identity->getId());
        }
        parent::init(); // TODO: Change the autogenerated stub
    }

    # 返回项目
    public function getItem(){
        if($this->_item !== null){
            return $this->_item;
        }

        if($item = \Yii::$app->session->get(static::SESSION_ITEM, null)){
            return  $this->_item = $item;
        }

        if(!empty($this->_userData['item'])){
            $item = Project::findOne($this->_userData['item']);
        }else{
            $item = Project::find()->one();
        }
        \Yii::$app->session->set(static::SESSION_ITEM, $item);
        return $this->_item = $item;
    }

    # 设置项目
    public function setItem($siteId){
        $this->_item =  Project::findOne($siteId);
        if($this->_item !== null){
            \Yii::$app->session->set(static::SESSION_ITEM, $this->_item );
            $this->_userData['item'] = $this->_item->id;
            UserData::setData($this->getUserId(), $this->_userData);
        }
    }

    # 获取项目列表
    public function getSites(){
        $key = __METHOD__ ;
        $cache = \Yii::$app->cache;
        if($cache !== null && $sites = $cache->get($key)){
            return $sites;
        }
        $sites = Project::find()->where('status=1')->all();

        if($cache !==null){
            $cache->set($key, $sites, static::CACHE_DURATION, new TagDependency(['tags'=>static::CACHE_TAG]));
        }
        return $sites;
    }

    public function getUserId(){
        return \Yii::$app->user->identity->getId();
    }

}