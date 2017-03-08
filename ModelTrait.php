<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 20.02.2017
 * Time: 19:37
 */

namespace seiweb\yii2images;


use seiweb\yii2images\models\Image;

trait ModelTrait
{
	public function getMainImage()
	{
		return $this->hasOne(Image::className(), ['id_object' => 'id'])
			->where(Image::tableName() . '.model_name=:m_name', [':m_name' => $this->className()])->orderBy('sort')->limit(1);
	}

	public function getImages()
	{
		return $this->hasMany(Image::className(), ['id_object' => 'id'])
			->where(Image::tableName() . '.model_name=:m_name', [':m_name' => $this->className()]);
	}
}