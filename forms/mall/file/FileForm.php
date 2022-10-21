<?php
/**
 * link: http://www.zjhejiang.com/
 * copyright: Copyright (c) 2018 浙江禾匠信息科技有限公司
 * author: xay
 */

namespace app\forms\mall\file;

use app\core\response\ApiCode;
use app\models\CoreFile;
use app\models\Model;

class FileForm extends Model
{
    public $id;
    public $keyword;
    public $time;
    public $status;

    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['keyword', 'status'], 'string'],
            [['time'], 'safe'],
        ];
    }

    //GET
    public function getList()
    {
        if (!$this->validate()) {
            return $this->getErrorResponse();
        }
        
        try {
            $query = CoreFile::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'is_delete' => 0,
            ]);

            if ($this->keyword) {
                $query->andWhere(['like', 'file_name', $this->keyword]);
            }

            if (is_array($this->time) && count($this->time) == 2) {
                $query->andWhere(['>=', 'created_at', $this->time[0]]);
                $query->andWhere(['<=', 'created_at', $this->time[1]]);
            }

            if ($this->status != '' && $this->status != null) {
                $query->andWhere(['status' => $this->status]);
            }

            $list = $query->page($pagination)->orderBy('created_at DESC')->all();

            $newList = [];
            foreach ($list as $item) {
                $id = \Yii::$app->mall->id . '_' . \Yii::$app->user->identity->mch_id;
                $downloadUrl = \Yii::$app->request->hostInfo . \Yii::$app->request->baseUrl . '/csv/' . $id . '/' . $item->file_name;
                $newList[] = [
                    'id' => $item->id,
                    'created_at' => $item->created_at,
                    'percent' => ($item->percent * 100) . '%',
                    'status' => $item->status,
                    'status_text' => $item->getStatusText($item),
                    'download_url' => $downloadUrl,
                    'file_name' => $item->file_name,
                ];
            }

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'data' => [
                    'list' => $newList,
                    'pagination' => $pagination
                ]
            ];
        }catch(\Exception $exception) {
            return [
                'code' => ApiCode::CODE_ERROR,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function destroyAll()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $res = CoreFile::updateAll([
                'is_delete' => 1
            ], [
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id
            ]);

            $id = \Yii::$app->mall->id . '_' . \Yii::$app->user->identity->mch_id;
            $filePath = sprintf('%s%s%s%s', \Yii::$app->basePath, '/web/csv/', $id, '/');
            if (file_exists($filePath)) {
                $res = $this->deldir($filePath);

                if (!$res) {
                    throw new \Exception('文件删除失败');
                }
            }

            $transaction->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功'
            ];
        }catch(\Exception $exception) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $exception->getMessage()
            ];
        }
    }

    public function destroy()
    {
        $transaction = \Yii::$app->db->beginTransaction();
        try {
            $coreFile = CoreFile::find()->andWhere([
                'mall_id' => \Yii::$app->mall->id,
                'mch_id' => \Yii::$app->user->identity->mch_id,
                'id' => $this->id,
                'is_delete' => 0
            ])->one();

            if (!$coreFile) {
                throw new \Exception('数据不存在');
            }

            $coreFile->is_delete = 1;
            $res = $coreFile->save();
            if (!$res) {
                throw new \Exception($this->getErrorMsg($coreFile));
            }

            $id = \Yii::$app->mall->id . '_' . \Yii::$app->user->identity->mch_id;
            $filePath = sprintf('%s%s%s%s%s', \Yii::$app->basePath, '/web/csv/', $id, '/', $coreFile->file_name);
            if (file_exists($filePath)) {
                $res = unlink($filePath);;
                if (!$res) {
                    throw new \Exception('文件删除失败');
                }
            }

            $transaction->commit();

            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => '删除成功'
            ];
        }catch(\Exception $exception) {
            $transaction->rollBack();
            return [
                'code' => ApiCode::CODE_SUCCESS,
                'msg' => $exception->getMessage()
            ];
        }
    }

    private function deldir($path){
        //如果是目录则继续
        if(is_dir($path)){
            //扫描一个文件夹内的所有文件夹和文件并返回数组
            $p = scandir($path);
            //如果 $p 中有两个以上的元素则说明当前 $path 不为空
            if(count($p)>2){
                foreach($p as $val){
                    //排除目录中的.和..
                    if($val !="." && $val !=".."){
                        //如果是目录则递归子目录，继续操作
                        if(is_dir($path.$val)){
                            //子目录中操作删除文件夹和文件
                            $this->deldir($path.$val.'/');
                        }else{
                            //如果是文件直接删除
                            unlink($path.$val);
                        }
                    }
                }
            }
        }
        //删除目录
        return rmdir($path);
    }
}
