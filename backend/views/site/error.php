<?php
    use yii\helpers\Html;
?>

<div class="site-error">
        <div class="row ">
        <div class="col-md-12">
	        <h3 style="text-align: center;">#<?= Html::encode($exception->statusCode) ?>: <?= Html::encode($exception->getMessage()) ?></h3>
    
    	</div>
    	<?php if (!empty(Yii::$app->user->identity->id)) {  ?>
    		<div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3 text-center">
	    		<a href="/admin">Go to Home</a>
	    	</div>
    	<?php } else { ?>
    		<div class="col-md-4 col-md-offset-4 col-sm-6 col-sm-offset-3 text-center">
	    		<a href="/admin">Go to Home</a>
	    	</div>
        <?php } ?>
</div>