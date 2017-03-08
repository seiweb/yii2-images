<?php

use kartik\widgets\FileInput;

$widgetId = $this->context->getId();

$filesorted = <<<EOD
function(event, params) {
    var items = params.stack.map(function(item) {
        return item.key;
        }); 
    
    $.post($(this).data('sortable-url'), {
       sorting: items
    });
}

EOD;

$this->registerJs("
 $(document).on('click', '.kv-image-main', function(e){

        e.preventDefault();
        var id = $(this).data('key');
        var url = $(this).data('url');
        var widget = $(this).data('widget');
        
        $.ajax({
            url: url+'?id='+id,
            type: 'get',
            success: function(result) {
                    
                       var files = $('#'+widget).fileinput.stack;
                       console.log(files);
                    }
        });
    });
");

echo FileInput::widget([
    'model' => new \seiweb\yii2images\models\UploadFile(),
    'attribute' => 'file',

    'language' => 'ru',
    'options' => [
        'multiple' => true,
        'accept' => '*/*',
        'id' => $widgetId,
        'data-sortable-url'=>\yii\helpers\Url::to(['/yii2images/default/sort']),
    ],
    'pluginOptions' => [
        'initialPreview' => $initialPreview,
        'initialPreviewAsData' => true,
        'initialPreviewConfig' => $initialPreviewConfig,

        '1layoutTemplates' => [
            'actions' => '<div class="file-actions">' .
                '    <div class="file-footer-buttons">' .
                '        {upload} {delete} {test}' .
                '    </div>' .
                '    {drag}' .
                '    <div class="clearfix"></div>' .
                '</div>',
        ],

        'otherActionButtons' =>
            "<button type='button' class='kv-image-main btn btn-xs btn-default' title='Сделать главной' data-url='/admin/yii2images/default/set-as-main' {dataKey} data-widget='{$widgetId}'><i class='glyphicon glyphicon-home text-primary'></i></button>"
        ,

        'previewSettings' => [
            'image' => ['width' => '195px', 'height' => 'auto']
        ],


        'overwriteInitial' => false,
        'uploadUrl' => \yii\helpers\Url::to(['/yii2images/default/upload']),
        'uploadExtraData' => [
            'model_name' => $model::className(),
            'primaryKey' => $model->primaryKey
        ],
        'dragSettings' => [
            'animation' => 500,
        ],
    ],
    'pluginEvents' => [
        'filesorted' => new \yii\web\JsExpression($filesorted),
    ]
]);

?>