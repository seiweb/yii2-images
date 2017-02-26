<div class="box">
	<div class="box-header with-border">
		<h3 class="box-title">Изображения</h3>

		<div class="box-tools pull-right">
			<button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
			</button>
			<div class="btn-group">
				<button type="button" class="btn btn-box-tool dropdown-toggle" data-toggle="dropdown">
					<i class="fa fa-wrench"></i></button>
				<ul class="dropdown-menu" role="menu">
					<li><a id="delete_all_images" href="#">Удалить все изображения</a></li>
					<li><a href="#">Another action</a></li>
					<li><a href="#">Something else here</a></li>
					<li class="divider"></li>
					<li><a href="#">Separated link</a></li>
				</ul>
			</div>
			<button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
		</div>
	</div>
	<!-- /.box-header -->
	<div class="box-body">
		<div class="row">
			<div class="col-md-12">
				<?php


				use kartik\file\FileInput;

				yii\widgets\Pjax::begin(['id' => $this->context->getId() . '_images_area']);
				echo \seiweb\yii2images\widgets\AdminImagesListWidget::widget([
					'model' => $model
				]);
				yii\widgets\Pjax::end();

				?>

			</div>
			<!-- /.col -->
		</div>
		<!-- /.row -->
	</div>
	<!-- ./box-body -->
	<div class="box-footer">
		<div class="row">
			<div class="col-md-12">

				<?php

				echo FileInput::widget([
					'model' =>new \seiweb\yii2images\models\UploadFile(),
					'attribute' => 'file',

					'language' => 'ru',
					'options' => [
						'multiple' => true,
						'accept' => '*/*',
						'id' => 'FileInput' . $this->context->getId(),
					],
					'pluginOptions' => [
						'previewFileType' => 'any',
						'uploadUrl' =>\yii\helpers\Url::to(['/yii2images/default/upload']),

						'uploadExtraData' => [
							'model_name' => $model::className(),
							'primaryKey' => $model->primaryKey
						],

						'showPreview' => false,
						'uploadAsync' => true,
						'showUpload' => false,
						'showCaption' => false,
						'showRemove' => false,
						'showCancel'=>false,
						'browseClass' => 'btn btn-primary btn-block',
						'browseIcon' => '<i class="glyphicon glyphicon-camera"></i> ',
						'browseLabel' => 'Выберите файлы',
						'maxFileSize' => 1024 * 15,
						'elErrorContainer' => '#kv-error-2',
					],
					'pluginEvents' => [
						'filebatchselected' => "function(e) { $(this).fileinput('upload');}",
						'fileuploaded' => "function(event, data, previewId, index) {
							    var out = '';
						        out = out + '<li>' + 'Файл \"' +   data.files[index].name + '\" успешно загружен.' + '</li>';
							    $('#kv-success-2 ul').append(out);
						}",
						'filebatchuploadcomplete' => 'function(event, data) {
							$.pjax.defaults.timeout = false;//IMPORTANT
							$.pjax.reload({container:"#' . $this->context->getId() . '_images_area'. '"});
							}
						',
					]

				]);


				?>




				<div id="kv-success-2" style="margin-top:10px;">
					<ul></ul>
				</div>
				<div id="kv-error-2" style="margin-top:10px;">
					<ul></ul>
				</div>

			</div>
		</div>
		<!-- /.row -->
	</div>
	<!-- /.box-footer -->
</div>
