<?php

/**
 * Created by IntelliJ IDEA.
 * User: luwei
 * Date: 2017/8/3
 * Time: 14:36
 * Version: 1.5.2
 */

namespace app\modules\admin\controllers;

use app\models\common\admin\update\CommonsUpdate;
use Comodojo\Zip\Zip;
use Curl\Curl;

class UpdateController extends Controller
{
    private $api_root = 'http://one.haoyunfa.net/';

    public function actionIndex()
    {
   
   
           $res  =array();
      
    
            $api = "{$this->api_root}/version.php"; 
          
            $target_version =(hj_core_version() + 0.01) ;
  
            $curl = new Curl();
         
            $curl->get($api, [
                'data' => $this->getSiteData(),
                'target_version' => $target_version,
            ]);
          
          
          
        $res = json_decode($curl->response, true);
 

        $res['current_version'] = hj_core_version();
      
     
      
    
      
    
      
        return $this->render(index, ['res'=>$res]);
    }

    public function actionUpdate()
    {
       // $this->checkIsAdmin();
        if (\Yii::$app->request->isPost) {
            $api = "{$this->api_root}/version.php"; 
          
          
            $target_version = \Yii::$app->request->post('target_version');//获取post来的参数值
            
            $myupdate= \Yii::$app->request->post('myupdate');//获取
          
            
            $curl = new Curl();
         
            $curl->get($api, [
                'data' => $this->getSiteData(),
                'target_version' => $target_version,
                'myupdate' => $myupdate,
            ]);
          
          
            $res = json_decode($curl->response, true);
          
         
         
            if (!$res) {
                return [
                    'code' => 1,
                    'msg' => '更新失败，与云服务器连接失败,咨询<a href="http://wpa.qq.com/msgrd?v=3&uin=3011741003&site=qq&menu=yes">QQ3011741003</a>',
                ];
            }

            if ($res['code'] != 0) {
                return $res;
            }
          
            $temp_dir = \Yii::$app->basePath . "/temp/update/version/{$target_version}";
            $this->mkdir($temp_dir);
         
            $src_file = "{$temp_dir}/src.zip"; //路径+文件名
            $db_file = "{$temp_dir}/db.sql";//路径+文件名
          
           
       
          
            $curl->get($res['data']['src_file']);//获取服务器文件
          

            if (!$curl->error) {
                file_put_contents($src_file, $curl->response);//获取文件 写入 路径+文件名
              
            } else {
                return [
                    'code' => 1,
                    'msg' => '更新失败，更新文件src.zip下载失败，咨询<a href="http://wpa.qq.com/msgrd?v=3&uin=3011741003&site=qq&menu=yes">QQ3011741003</a>',
                ];
            }

            $curl->get($res['data']['db_file']);//获取服务器数据库文件
            if (!$curl->error) {
              
   
                file_put_contents($db_file, $curl->response);//获取sql文件 写入 路径+文件名
            } else {
                return [
                    'code' => 1,
                    'msg' => '更新失败，更新文件db.sql下载失败，咨询<a href="http://wpa.qq.com/msgrd?v=3&uin=3011741003&site=qq&menu=yes">QQ3011741003</a>',
                ];
            }
   
            $t = \Yii::$app->db->beginTransaction();
   
           //$versions='http://'.\Yii::$app->request->hostName.'/'.version.'.'.json;
            
          
                    
          try {
                $sql = file_get_contents($db_file);
				$sql = str_replace('hjmall_', \Yii::$app->db->tablePrefix, $sql);
                try {
                    \Yii::$app->db->createCommand($sql)->execute();
                } catch (\Exception $e) {
                }
                $zip = Zip::open($src_file);
                $zip->extract(\Yii::$app->basePath);
                $zip->close();
                $t->commit();
                unlink($src_file);
                unlink($db_file);
           
                return [
                    'code' => 0,
                    'msg' => '<a href="http://www.xuanjingsi.com">悬镜司</a>持续更新更新中！版本更新成功' . $target_version,
                ];
            } 

   
          catch (\Exception $e) {
                $t->rollBack();
                return [
                    'code' => 1,
                    'msg' => '<a href="http://www.xuanjingsi.com">悬镜司</a>持续更新更新中！更新失败：' . $e->getMessage(),
                ];
            }
        }
    }

    private function getSiteData()
    {
      
    
        $data = base64_encode(json_encode((object)[
            'host' => \Yii::$app->request->hostName,
            'from_url' => \Yii::$app->request->absoluteUrl,
            'current_version' => hj_core_version(),
        ]));
      
      
        return $data;
    }

    private function mkdir($dir)
    {
        if (!is_dir($dir)) {
            if (!$this->mkdir(dirname($dir))) {
                return false;
            }
            if (!mkdir($dir)) {
                return false;
            }
        }
        return true;
    }

}
