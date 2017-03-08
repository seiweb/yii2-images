<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 22.02.2016
 * Time: 1:15
 */

namespace seiweb\yii2images\widgets;

use seiweb\yii2images\models\Image;
use seiweb\yii2images\ModuleTrait;
use yii\base\Exception;
use yii\base\Widget;

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
        $initialPreview = [];
        $initialPreviewConfig = null;

        $images = Image::find()->where(['model_name' => $this->model->className(), 'id_object' => $this->model->primaryKey])->orderBy('sorter')->all();

        foreach ($images as $img) {

            $initialPreview[] = $img->getUrl(195, 170, 'crop');
            $initialPreviewConfig[] = [
                'caption' => $img->file_name,
                'size' => $img->size,
                'url' => "/admin/yii2images/default/delete?id=" . $img->id,
                'key' => $img->id
            ];
        }
        return $this->render('admin_images', [
            'model' => $this->model,
            'sizeLimit' => $this->getModule()->sizeLimit,
            'initialPreview' => $initialPreview,
            'initialPreviewConfig' => $initialPreviewConfig
        ]);
    }
}