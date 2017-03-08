<?php

namespace seiweb\yii2images\controllers;

use seiweb\sortable\actions\SortableGridAction;
use seiweb\yii2images\models\Image;
use seiweb\yii2images\models\UploadFile;
use seiweb\yii2images\ModuleTrait;
use Yii;
use yii\base\Exception;
use yii\helpers\BaseFileHelper;
use yii\helpers\Json;
use yii\image\ImageDriver;
use yii\web\Controller;
use yii\web\UploadedFile;

class DefaultController extends Controller
{
	use ModuleTrait;

    public function actions()
    {
        return [
            'sort' => [
                'class' => SortableGridAction::className(),
                'modelName' => Image::className(),
            ],
        ];
    }

	/**
	 *
	 * @param $model_name
	 * @param $primaryKey
	 * @throws Exception
	 */
	public function actionUpload()
	{
		$model_name = Yii::$app->request->post()['model_name'];
		$primaryKey = Yii::$app->request->post()['primaryKey'];

		$module = $this->getModule();

		$imagesStorePath = $module->getStorePath() . DIRECTORY_SEPARATOR . $module->getModelSubDir($model_name, $primaryKey);
		$imagesCachePath = $module->getCachePath() . DIRECTORY_SEPARATOR . $module->getModelSubDir($model_name, $primaryKey);

		BaseFileHelper::createDirectory($imagesStorePath, 0775, true);


		$file = UploadedFile::getInstance(new UploadFile(), 'file');

		if ($file->size > $this->getModule()->sizeLimit) {
			$res = [
				'files' => [[
					"name" => $file->baseName,
					'size' => $file->size,
					'error' => 'Допустимый размер файла - 2M'
				]]
			];

			return  Json::encode($res);
		}

		if ($file) {
			$filename = substr(md5(uniqid($file->baseName)), 5, 10) .
				'.' . $file->extension;
			$file->saveAs($imagesStorePath . DIRECTORY_SEPARATOR . $filename);

			$driver = new ImageDriver();
			$driver->driver = $this->getModule()->graphicsLibrary;
			$image = $driver->load($imagesStorePath . DIRECTORY_SEPARATOR . $filename);


			$image->resize(1280, 1024, \yii\image\drivers\Image::HEIGHT);

			$image->save(null, 75);


			$image = new Image();
			$image->file_name = $filename;
			$image->model_name = $model_name;
			$image->size = $file->size;
			$image->model_name_md5 = substr(md5($model_name), 5, 10);
			$image->id_object = $primaryKey;

			$count = Image::find()->where(['model_name_md5' => substr(md5($model_name), 5, 10), 'id_object' => $primaryKey])->count();

			$image->is_main = $count == 0 ? 1 : 0;
			$image->save(false);

			$res = [
				'files' => [[
					"name" => $file->baseName,
					'size' => $file->size,
				]]
			];

			return Json::encode($res);
		}


		$res = ['result' => 'FALSE', 'error' => 'Some error blyat'];
		return Json::encode($res);

	}


	public function actionSetAsMain($id)
	{
	    $img = Image::findOne($id);
	    //$items = Image::find()->where(['model_name'=>$img->model_name,'id_object'=>$img->id_object])->select('id')->column();

	    $img->sorter = 0;
	    $img->save();

	    Yii::$app->db->createCommand("update tbl_image set sorter= (select @a:= @a+1 from (select @a:=0) s) where model_name_md5='{$img->model_name_md5}' and id_object={$img->id_object} order by sorter")->execute();

	    //Yii::trace($items);

	    //из поведения
        //$img->gridSort($items);
		return Json::encode(['result' => 'ok']);
	}

	public function actionDelete($id)
	{

	    Yii::trace($id);

		$res = [];
		$current = Image::findOne($id);

		$current->delete();

		$res['result'] = 'ok';
		$res['item_id'] = $current->primaryKey;
		echo Json::encode($res);
	}
}
