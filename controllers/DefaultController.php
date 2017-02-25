<?php

namespace seiweb\yii2images\controllers;

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

	public function actionIndex()
	{
		$imagesRoot = '@frontend/web/uploads/yii2images/';
		$originalSubFolder = 'original';
		$modifiedSubFolder = 'modified';

		\Yii::setAlias('@original', 'original_rp');
		\Yii::setAlias('@modified', 'modified_rp');


		var_dump(\Yii::getAlias($imagesRoot));
		var_dump(\Yii::getAlias($imagesRoot . '/' . 'original'));


		Yii::setAlias('@foo', '/path/to/foo');
		Yii::setAlias('@foo/bar', '/path2/bar');
		echo '<br/>' . Yii::getAlias('@foo/test/file.php') . '<br/>';  // выведет: /path/to/foo/test/file.php
		echo Yii::getAlias('@foo/bar/file.php');   // выведет: /path2/bar/file.php
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

			echo Json::encode($res);
			return;
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

			$res = ['result' => 'OK', 'initialPreview' => [
				//Html::img($image->getUrl(350,150,'crop'))
			]];
			return Json::encode($res);
		}


		$res = ['result' => 'OK', 'initialPreview' => [
		]];
		return Json::encode($res);

	}


	public function actionSetAsMain($id)
	{
		$current = Image::findOne($id);
		$connection = \Yii::$app->db;
		$connection->createCommand()->update('tbl_image', ['is_main' => 0], ['model_name' => $current->model_name, 'id_object' => $current->id_object])->execute();
		$current->is_main = 1;
		$current->save();

		echo Json::encode(['result' => 'ok', 'item_id' => $current->primaryKey]);
	}

	public function actionDelete($id)
	{

		$res = [];
		$current = Image::findOne($id);

		//is_main?
		if ($current->is_main > 0) {
			$next_main = Image::findOne(['model_name_md5' => $current->model_name_md5, 'id_object' => $current->id_object, 'is_main' => 0]);
			if ($next_main != null) {
				$next_main->is_main = 1;
				$next_main->save();
				$res['new_main_id'] = $next_main->id;
			}
		}

		/*
				$storePath = $current->getOriginalPath();

				$fileToRemove = $storePath . DIRECTORY_SEPARATOR . $current->file_name;
				if (preg_match('@\.@', $fileToRemove) and is_file($fileToRemove)) {
					unlink($fileToRemove);
				}

				$subDir = $current->getCachePath();

				array_map("unlink", glob($subDir.'/*'.$current->file_name.'*'));
		*/
		$current->delete();

		$res['result'] = 'ok';
		$res['item_id'] = $current->primaryKey;
		echo Json::encode($res);
	}
}
