

<?php
use yii\widgets\ListView;

echo ListView::widget([
	'dataProvider' => $dataProvider,
	'itemOptions' => ['class' => 'item'],
	'options'=>[
		'class'=>'images-area row',
	],
	'itemOptions'=>[
		'class'=>'item col-sm-4 col-md-3 col-lg-3'
	],
	'layout'=>"{items}\n{pager}",
	'itemView' => function ($model, $key, $index, $widget) {
		return $this->render('_list', ['model' => $model]);
	},
]) ?>



<?php

$script = '
		$(\'.set_as_main\').on(\'click\', function () {
		var $btn = $(this);
		$btn.html(\'<i class="fa fa-spinner fa-spin"></i>\');

		$.ajax({
			type: "GET",
			url: "/admin/yii2images/default/set-as-main",
			data: "id="+$btn.data("id"),
			dataType: "json",
			success: function(msg){
				if(msg.result=="ok")
				{
					$(".set_as_main.btn-success").removeClass("btn-success").addClass("btn-primary");
					$btn.removeClass("btn-primary").addClass("btn-success");
					$btn.html(\'<i class="glyphicon glyphicon-home"></i>\');
				}

			}
		});

	});


	$(\'.delete\').on(\'click\', function () {
		if(!confirm("Удалить?")) return;
		var $btn = $(this);
		$btn.html(\'<i class="fa fa-spinner fa-spin"></i>\');
		$.ajax({
			type: "GET",
			url: "/admin/yii2images/default/delete",
			data: "id="+$btn.data("id"),
			dataType: "json",
			success: function(msg){
				if(msg.result=="ok")
				{
					if(msg.new_main_id)
					{
					console.log(msg);
						var bt = $(".set_as_main[data-id=\'"+msg.new_main_id+"\']");
						//bt.removeClass("btn-success").addClass("btn-primary");
						bt.removeClass("btn-primary").addClass("btn-success");
						bt.html(\'<i class="glyphicon glyphicon-home"></i>\');
					}
					$(".item[data-key=\'"+$btn.data("id")+"\']").remove();
				}

			}
		});
	});

';

$this->registerJs($script);

?>
