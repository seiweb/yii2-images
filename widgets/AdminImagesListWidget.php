<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 22.02.2016
 * Time: 1:15
 */

namespace seiweb\yii2images\widgets;

use seiweb\yii2images\models\Image;
use seiweb\yii2images\models\ImageSearch;
use seiweb\yii2images\ModuleTrait;
use yii\base\Exception;
use yii\base\Widget;
use yii\data\ActiveDataProvider;
use yii\helpers\Html;

class AdminImagesListWidget extends Widget
{
	use ModuleTrait;

	public $model = null;

	public function init()
	{
		parent::init();
		if ($this->model === null) {
			throw new Exception("Need model");
		}

	}

	public function run()
	{
		$sModel = new ImageSearch();

		$sModel->id_object = $this->model->primarykey;
		$sModel->model_name = $this->model->className();

		return $this->render('admin_images_list', [
			'model' => $this->model,'dataProvider'=>$sModel->search()
		]);
	}
}