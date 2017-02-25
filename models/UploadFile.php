<?php
/**
 * Created by PhpStorm.
 * User: kostanevazno
 * Date: 05.08.14
 * Time: 18:21
 *
 * TODO: check that placeholder is enable in module class
 * override methods
 */

namespace seiweb\yii2images\models;

/**
 * TODO: check path to save and all image method for placeholder
 */

use yii;

class UploadFile extends yii\base\Model
{
    public $file;

    public function rules()
    {
        return [
            [['file'], 'file'],
        ];
    }

}

