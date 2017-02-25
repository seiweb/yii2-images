<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 22.02.2016
 * Time: 1:15
 */

namespace seiweb\yii2images\widgets;

use seiweb\yii2images\ModuleTrait;
use yii\base\Exception;
use yii\base\Widget;
use yii\helpers\Html;

class AdminImagesWidget extends Widget
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
		return $this->render('admin_images', ['model' => $this->model, 'sizeLimit' => $this->getModule()->sizeLimit]);
	}
}