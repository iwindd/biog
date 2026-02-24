<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Variables */

$this->title = 'Config';

?>
<div class="config">

    <h1><?= Html::encode($this->title) ?></h1>

    <?php print_r($variables);?>

</div>
