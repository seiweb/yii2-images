<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 25.02.2016
 * Time: 19:12
 */

namespace seiweb\yii2images\models;


use yii\base\Model;
use yii\data\ActiveDataProvider;

class ImageSearch extends Image
{
	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			//[['id_object'],'required'],
			[['id_object'], 'integer'],
			[['model_name'], 'string', 'max' => 255]
		];
	}


	/**
	 * @inheritdoc
	 */
	public function scenarios()
	{
		// bypass scenarios() implementation in the parent class
		return Model::scenarios();
	}

	public function search()
	{
		$query = ImageSearch::find();

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'pagination'=>['pageSize'=>1000]
		]);

		if($this->id_object == null)
		{
			$query->where('0=1');
			return $dataProvider;
		}


		if (!$this->validate()) {
			// uncomment the following line if you do not want to return any records when validation fails
			 $query->where('0=1');
			var_dump($this->errors);
			return $dataProvider;
		}

		$query->andFilterWhere([
			'id_object' => $this->id_object,
			'model_name' => $this->model_name,
		]);

		$query->orderBy('is_main desc, sorter');

		return $dataProvider;
	}

}