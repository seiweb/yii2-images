	<div class="thumbnail ">
		<a href="" class="thumbnail no-margin">
			<?= \yii\helpers\Html::img($model->getUrl(250, 150, 'crop'), ['class' => 'img-polaroid']) ?>
		</a>
		<div class="caption">
			<div class="row">

				<div class="col-sm-4">
						<button type="button" class="btn btn-<?=($model->is_main)?'success':'primary';?> btn-sm set_as_main"  data-id="<?=$model->primaryKey;?>" >
							<i class="glyphicon glyphicon-home"></i>
						</button>
				</div>
				<div class="col-sm-8 text-right">
					<div class="btn-group" role="group" aria-label="...">
						<button type="button" class="btn btn-danger btn-sm delete" data-id="<?=$model->primaryKey;?>">
							<i class="glyphicon glyphicon-trash"></i>
							<span></span>
						</button>
						<!--
						<div class="btn-group" role="group">
							<button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown"
							        aria-haspopup="true" aria-expanded="false">
								<i class="glyphicon glyphicon-asterisk"></i>
								<span class="caret"></span>
							</button>
							<ul class="dropdown-menu">
								<li><a href="#">Dropdown link</a></li>
								<li><a href="#">Dropdown link</a></li>
							</ul>
						</div>
						-->
					</div>
				</div>
			</div>
		</div>
	</div>
