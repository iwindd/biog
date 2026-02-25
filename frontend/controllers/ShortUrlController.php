<?php

namespace frontend\controllers;

use common\models\ShortUrl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use Yii;

/**
 * ShortUrl controller handles tracking and redirection for short URLs
 */
class ShortUrlController extends Controller
{
  /**
   * Resolves short code and redirects to target URL.
   *
   * @param string $code
   * @return \yii\web\Response
   * @throws NotFoundHttpException if the short code is not found
   */
  public function actionRedirect($code)
  {
    $shortUrl = ShortUrl::findOne(['code' => $code]);

    if ($shortUrl) {
      return $this->redirect($shortUrl->target_url);
    }

    throw new NotFoundHttpException('The requested page does not exist or has expired.');
  }
}
