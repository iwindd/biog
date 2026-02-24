<div class="container" style="margin-top: 10rem;">

<?php

use yii\helpers\Html;


/*
echo yii\grid\GridView::widget([

    'id'=>'my-grid',

    'dataProvider' => $provider,

    'columns' => [

        'id',

        'name',

        'total',

        [

            'attribute' => 'image',

            'label' => 'Image',

            'format' => 'raw',

            'value' => function($model){
                if (!empty($model['image'])) {
                    return '<img src="'.$model['image'].'" width="150px" >' ;
                }else{
                    return '';
                }
            }

        ],

        //'url',

        [
            'label'=>'url',
            'format' => 'raw',
            'value'=>function ($model) {
                if (!empty($model['url'])) {
                    return Html::a($model['url'], $model['url'], ['target' => '_blank']);
                }else{
                    return $model['url'];
                }
            },
        ],

        // [

        //     'attribute' => 'time',

        //     'label' => 'Time',

        //     //'format'=>'datetime',

        //     'format' => ['date', 'php:M d, Y \a\t H:i:s A'

        //     ],

        // ],

        // 'address'

    ]

]);
*/

?>

<div class="table-responsive">
    <table class="table">
        <thead class="thead-dark">
            <tr>
            <th scope="col">#ID</th>
            <th scope="col">ชื่อเรื่อง</th>
            <th scope="col">ชื่อวิทยาศาสตร์</th>
            <th scope="col">URl ข้อมูล</th>
            </tr>
        </thead>
        <tbody>

            <?php foreach($model as $value): ?>
                <tr>
                    <td><?php echo $value['id']; ?></td>
                    <td><?php echo $value['name']; ?></td>
                    <td><?php echo $value['sciName']; ?></td>
                    <td><?php echo !empty($value['url'])? '<a href="'.$value['url'].'" target="_blank" > '.$value['url'].' </a>':''; ?></td>
                <tr>

            <?php endforeach; ?>
            
        </tbody>
    </table>

    
</div>





</div>