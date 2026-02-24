<?php 
    use yii\helpers\Html; 

?>



<!-- Main Wrapper -->
<div class="main-wrapper">
			
	<div class="error-box">
		<h1><?= Html::encode($exception->statusCode) ?></h1>
		<h3><i class="fa fa-warning"></i> </h3>
		<p><?= Html::encode($exception->getMessage()) ?></p>
		<a href="/" class="btn btn-custom">Back to Home</a>
	</div>

</div>
<!-- /Main Wrapper -->