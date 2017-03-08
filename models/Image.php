<?php

namespace seiweb\yii2images\models;

use seiweb\sortable\behaviors\SortableGridBehavior;
use seiweb\yii2images\ModuleTrait;
use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\helpers\BaseFileHelper;
use yii\image\ImageDriver;

/**
 * This is the model class for table "tbl_images".
 *
 * @property string $id
 * @property integer $id_object
 * @property integer $id_owner
 * @property string $file_name
 * @property integer $sorter
 * @property string $comment
 * @property integer $is_main
 * @property string $date_created
 * @property string $date_updated
 * @property string $model_name
 * @property string $model_name_md5
 * @property string $c_bits
 */
class Image extends \yii\db\ActiveRecord
{
	use ModuleTrait;

    public function behaviors()
    {
        return [
            'sort' => [
                'class' => SortableGridBehavior::className(),
                'sortableAttribute' => 'sorter',
                'scopeAttribute'=>['id_object','model_name']
            ],
        ];
    }

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'tbl_image';
	}

	public function getUrl($width, $height, $crop_mode = 'crop')
	{
		$res = $this->getModule()->imagesCacheBaseUrl .
			$this->getModule()->getModelSubDir($this->model_name, $this->id_object) . '/' .
			$this->getModifiedFileName($width, $height, $crop_mode);

		return $res;
	}

	public function getModifiedFileName($width, $height, $crop_mode = 'crop')
	{
		$filePath = $this->getCachePath() . DIRECTORY_SEPARATOR . $this->createSizeFileName($width, $height, $crop_mode);
		if (!file_exists($filePath)) {
			$this->createModifiedImage($width, $height, $crop_mode);

			if (!file_exists($filePath)) {
				throw new \Exception('Problem with image creating.');
			}
		}

		return $this->createSizeFileName($width, $height, $crop_mode);;
	}

	/**
	 * @return string
	 * @throws \yii\base\Exception
	 */
	public function getCachePath()
	{
		$module = $this->getModule();
		$res = $module->getCachePath() . DIRECTORY_SEPARATOR .
			$module->getModelSubDir($this->model_name, $this->id_object);
		BaseFileHelper::createDirectory($res, 0775, true);
		return $res;
	}

	/**
	 * @param $width
	 * @param $height
	 * @param $crop_mode
	 * @return string
	 */
	private function createSizeFileName($width, $height, $crop_mode)
	{
		$d = '_';
		return $width . $d . $height . $d . $crop_mode . $d . $this->file_name;
	}

	/**
	 * @param $width
	 * @param $height
	 * @param string $crop_mode
	 * @return \\SimpleImage|\Imagick
	 * @throws \Exception
	 * @throws \yii\base\Exception
	 */
	public function createModifiedImage($width, $height, $crop_mode)
	{
		$originalPath = \Yii::getAlias($this->getOriginalPath()) . DIRECTORY_SEPARATOR . $this->file_name;
		$modifiedFileName = \Yii::getAlias($this->getCachePath()) . DIRECTORY_SEPARATOR . $this->createSizeFileName($width, $height, $crop_mode);

		$driver = new ImageDriver();
		$driver->driver = $this->getModule()->graphicsLibrary;
		$image = $driver->load($originalPath);

		//WaterMark
		if($this->getModule()->waterMark){

			if(!file_exists(Yii::getAlias($this->getModule()->waterMark))){
				throw new Exception('WaterMark not detected!');
			}

			$wmMaxWidth = intval($image->width*0.4);
			$wmMaxHeight = intval($image->height*0.4);

			$waterMarkPath = Yii::getAlias($this->getModule()->waterMark);
			$waterMark = $driver->load($waterMarkPath);
				//new \abeautifulsite\SimpleImage($waterMarkPath);



			/*
			if(
				$waterMark->height > $wmMaxHeight
				or
				$waterMark->width > $wmMaxWidth
			){

				$waterMarkPath = $this->getModule()->getCachePath().DIRECTORY_SEPARATOR.
					pathinfo($this->getModule()->waterMark)['filename'].
					$wmMaxWidth.'x'.$wmMaxHeight.'.'.
					pathinfo($this->getModule()->waterMark)['extension'];

				//throw new Exception($waterMarkPath);
				if(!file_exists($waterMarkPath)){
					$waterMark->fit_to_width($wmMaxWidth);
					$waterMark->save($waterMarkPath, 100);
					if(!file_exists($waterMarkPath)){
						throw new Exception('Cant save watermark to '.$waterMarkPath.'!!!');
					}
				}

			}
*/
			$image->watermark($waterMark, null,null,$this->getModule()->waterMarkOpacity);

		}

		switch ($crop_mode) {
			case 'none':
				$image->resize($width, $height, \yii\image\drivers\Image::NONE);
				break;
			case 'width':
				$image->resize($width, $height, \yii\image\drivers\Image::WIDTH);
				break;
			case 'height':
				$image->resize($width, $height, \yii\image\drivers\Image::HEIGHT);
				break;
			case 'auto':
				$image->resize($width, $height, \yii\image\drivers\Image::AUTO);
				break;
			case 'inverse':
				$image->resize($width, $height, \yii\image\drivers\Image::INVERSE);
				break;
			case 'precise':
				$image->resize($width, $height, \yii\image\drivers\Image::PRECISE);
				break;
			case 'adapt':
				$image->resize($width, $height, \yii\image\drivers\Image::ADAPT);
				break;
			case 'crop':
				$image->resize($width, $height, \yii\image\drivers\Image::CROP);
				break;
		}


		$image->save($modifiedFileName, $this->getModule()->cachedImagesQuality);

		return $image;
	}

	/**
	 * @return string
	 * @throws \yii\base\Exception
	 */
	public function getOriginalPath()
	{
		$module = $this->getModule();
		return $module->getStorePath() . DIRECTORY_SEPARATOR .
		$module->getModelSubDir($this->model_name, $this->id_object);
	}

	public function getOriginalUrl()
	{
		$res = $this->getModule()->imagesStoreBaseUrl . '/' .
			$this->getModule()->getModelSubDir($this->model_name, $this->id_object) . '/' .
			$this->file_name;
		return $res;
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['id_object', 'file_name', 'is_main', 'model_name', 'model_name_md5'], 'required'],
			[['id_object', 'sorter', 'is_main','size'], 'integer'],
			[['file_name', 'comment'], 'string', 'max' => 255],
			[['model_name'], 'string', 'max' => 200]
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'id_object' => 'Id Object',
			'id_owner' => 'Id Owner',
			'file_name' => 'File Name',
			'file_name_modified' => 'File Name Modified',
			'sorter' => 'Sorter',
			'comment' => 'Comment',
			'is_main' => 'Is Main',
			'date_created' => 'Date Created',
			'date_updated' => 'Date Updated',
			'model_name' => 'Model Name',
			'c_bits' => 'C Bits',
		];
	}

	public function beforeDelete()
	{
		$storePath = $this->getOriginalPath();
		$fileToRemove = $storePath . DIRECTORY_SEPARATOR . $this->file_name;
		if (preg_match('@\.@', $fileToRemove) and is_file($fileToRemove)) {
			unlink($fileToRemove);
		}

		if (count(BaseFileHelper::findFiles($storePath)) == 0) {
			BaseFileHelper::removeDirectory($storePath);
		}

		$subDir = $this->getCachePath();
		array_map("unlink", glob($subDir . '/*' . $this->file_name . '*'));

		if (count(BaseFileHelper::findFiles($subDir)) == 0) {
			BaseFileHelper::removeDirectory($subDir);
		}


		return parent::beforeDelete(); // TODO: Change the autogenerated stub
	}

	public static function getModelImages($model)
	{
		$query = Image::find();
		$i = new ActiveRecord();
		$query->andWhere(['model_name'=>$model->className()]);
		$query->andWhere(['id_object'=>$model->primaryKey]);
		$query->orderBy('sorter');

		return $query;
	}
}
