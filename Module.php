<?php

namespace seiweb\yii2images;


use rico\yii2images\models\PlaceHolder;
use yii;
use rico\yii2images\models\Image;

class Module extends \yii\base\Module
{
    public $imagesRoot = '@frontend/web/uploads/yii2images/';
    public $originalSubFolder = '';
    public $modifiedSubFolder = 'cache';

    public $imagesStorePath = '@frontend/web/uploads/yii2images';
    public $imagesCachePath = '@frontend/web/uploads/yii2images/cache';

    public $imagesStoreBaseUrl = '/uploads/yii2images';
    public $imagesCacheBaseUrl = '/uploads/yii2images/cache';

    //todo: сделать красиво размер файлов
    public $sizeLimit=8048000;

    public $graphicsLibrary = 'GD';

    public $controllerNamespace = 'seiweb\yii2images\controllers';

    public $placeHolderPath;

    public $waterMark = null;
    public $waterMarkOpacity = 100;

    public $className;

    public $cachedImagesQuality = 100;

    public $resizeOriginalTo = [1024,768];

    /**
     * @param $item
     * @param $dirtyAlias
     * @return array|null|PlaceHolder|yii\db\ActiveRecord
     */
    public function getImage($item, $dirtyAlias)
    {
        //Get params
        $params = $data = $this->parseImageAlias($dirtyAlias);

        $alias = $params['alias'];
        $size = $params['size'];

        $itemId = preg_replace('/[^0-9]+/', '', $item);
        $modelName = preg_replace('/[0-9]+/', '', $item);


        //Lets get image
        if(empty($this->className)) {
            $imageQuery = Image::find();
        } else {
            $class = $this->className;
            $imageQuery = $class::find();
        }
        $image = $imageQuery
            ->where([
                'modelName' => $modelName,
                'itemId' => $itemId,
                'urlAlias' => $alias
            ])
            /*     ->where('modelName = :modelName AND itemId = :itemId AND urlAlias = :alias',
                     [
                         ':modelName' => $modelName,
                         ':itemId' => $itemId,
                         ':alias' => $alias
                     ])*/
            ->one();
        if(!$image){
            return $this->getPlaceHolder();
        }

        return $image;
    }

    public function getStorePath()
    {
        return Yii::getAlias($this->imagesStorePath);
    }


    public function getCachePath()
    {
        return Yii::getAlias($this->imagesCachePath);

    }

    public function getModelSubDirold($model)
    {
        $modelName = $this->getShortClass($model);
        $modelDir = \yii\helpers\Inflector::pluralize($modelName).'/'. $modelName . $model->primaryKey;
        return $modelDir;
    }

    public function getModelSubDir($model_name,$primaryKey)
    {
        return '';
        return substr(md5($model_name),5,10).'_'.$primaryKey;
    }


    public function getShortClass($obj)
    {
        $className = get_class($obj);

        if (preg_match('@\\\\([\w]+)$@', $className, $matches)) {
            $className = $matches[1];
        }

        return $className;
    }


    /**
     *
     * Parses size string
     * For instance: 400x400, 400x, x400
     *
     * @param $notParsedSize
     * @return array|null
     */
    public function parseSize($notParsedSize)
    {
        $sizeParts = explode('x', $notParsedSize);
        $part1 = (isset($sizeParts[0]) and $sizeParts[0] != '');
        $part2 = (isset($sizeParts[1]) and $sizeParts[1] != '');
        if ($part1 && $part2) {
            if (intval($sizeParts[0]) > 0
                &&
                intval($sizeParts[1]) > 0
            ) {
                $size = [
                    'width' => intval($sizeParts[0]),
                    'height' => intval($sizeParts[1])
                ];
            } else {
                $size = null;
            }
        } elseif ($part1 && !$part2) {
            $size = [
                'width' => intval($sizeParts[0]),
                'height' => null
            ];
        } elseif (!$part1 && $part2) {
            $size = [
                'width' => null,
                'height' => intval($sizeParts[1])
            ];
        } else {
            throw new \Exception('Something bad with size, sorry!');
        }

        return $size;
    }

    public function parseImageAlias($parameterized)
    {
        $params = explode('_', $parameterized);

        if (count($params) == 1) {
            $alias = $params[0];
            $size = null;
        } elseif (count($params) == 2) {
            $alias = $params[0];
            $size = $this->parseSize($params[1]);
            if (!$size) {
                $alias = null;
            }
        } else {
            $alias = null;
            $size = null;
        }


        return ['alias' => $alias, 'size' => $size];
    }


    public function init()
    {
        parent::init();
        if (!$this->imagesStorePath
            or
            !$this->imagesCachePath
            or
            $this->imagesStorePath == '@app'
            or
            $this->imagesCachePath == '@app'
        )
            throw new \Exception('Setup imagesStorePath and imagesCachePath images module properties!!!');
        // custom initialization code goes here
    }

    public function getPlaceHolder(){

        if($this->placeHolderPath){
            return new PlaceHolder();
        }else{
            return null;
        }
    }
}
