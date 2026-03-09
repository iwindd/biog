<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\helpers\Url;
use common\models\UserThaid;
use dektrium\user\models\User;

class ThaidController extends Controller
{
    private function getThaidBaseUrl()
    {
        $env = Yii::$app->params['thaid_env'];
        if ($env === 'production') {
            return 'https://imauth.bora.dopa.go.th';
        }
        return 'https://imauthsbx.bora.dopa.go.th';
    }

    public function actionAuth()
    {
        $clientId = Yii::$app->params['thaid_client_id'];
        $redirectUri = Url::to(['/thaid/callback'], true);
        
        $state = Yii::$app->security->generateRandomString(16);
        Yii::$app->session->set('thaid_state', $state);

        $baseUrl = $this->getThaidBaseUrl();
        $authUrl = $baseUrl . '/api/v2/oauth2/auth/?' . http_build_query([
            'response_type' => 'code',
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => 'pid name birthdate',
            'state' => $state
        ]);

        return $this->redirect($authUrl);
    }

    public function actionCallback()
    {
        $request = Yii::$app->request;
        $code = $request->get('code');
        $state = $request->get('state');

        $savedState = Yii::$app->session->get('thaid_state');
        if (!$state || $state !== $savedState) {
            Yii::$app->session->setFlash('alert-login', [
                'body' => 'Invalid state parameter.',
                'options' => ['class' => 'alert-danger']
            ]);
            return $this->redirect(['/login']);
        }

        Yii::$app->session->remove('thaid_state');

        $baseUrl = $this->getThaidBaseUrl();
        $tokenUrl = $baseUrl . '/api/v2/oauth2/token/';
        $redirectUri = Url::to(['/thaid/callback'], true);
        $basicToken = Yii::$app->params['thaid_basic_token'];

        if (!$basicToken) {
            $clientId = Yii::$app->params['thaid_client_id'];
            $clientSecret = Yii::$app->params['thaid_secret'];
            $basicToken = base64_encode($clientId . ':' . $clientSecret);
        }

        $postData = http_build_query([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $redirectUri,
        ]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tokenUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
            'Authorization: Basic ' . $basicToken
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            Yii::$app->session->setFlash('alert-home', [
                'body' => 'Failed to authenticate with ThaID.',
                'options'=>['class'=>'alert-danger']
            ]);
            return $this->redirect(['/login']);
        }

        $data = json_decode($response, true);
        if (!isset($data['pid'])) {
            Yii::$app->session->setFlash('alert-home', [
                'body' => 'Incomplete data received from ThaID.',
                'options'=>['class'=>'alert-danger']
            ]);
            return $this->redirect(['/login']);
        }

        $pid = $data['pid'];
        
        $userThaid = UserThaid::findOne(['pid' => $pid]);

        if (!Yii::$app->user->isGuest) {
            // Flow: Existing logged-in admin connecting to ThaID
            if ($userThaid) {
                if ($userThaid->user_id == Yii::$app->user->id) {
                    Yii::$app->session->setFlash('edit_success', [
                        'body'=> 'บัญชีนี้เชื่อมต่อกับ ThaID เรียบร้อยแล้ว',
                        'options'=>['class'=>'alert-info']
                    ]);
                } else {
                    $errorMsg = 'หมายเลขประจำตัวประชาชนนี้ถูกใช้งานเข้ากับบัญชีอื่นไปแล้ว หากพบปัญหากรุณาติดต่อแอดมิน';
                    Yii::$app->session->setFlash('alert-home', [
                        'body' => $errorMsg,
                        'options'=>['class'=>'alert-danger']
                    ]);
                }
                return $this->redirect(['/users/view', 'id' => Yii::$app->user->id]);
            }

            // Create new linkage
            $newConnection = new UserThaid();
            $newConnection->user_id = Yii::$app->user->id;
            $newConnection->pid = $pid;
            $newConnection->created_at = date('Y-m-d H:i:s');
            
            if ($newConnection->save(false)) {
                // BackendHelper usually has saveUserLog
                \backend\components\BackendHelper::saveUserLog('user', Yii::$app->user->id, Yii::$app->user->id, 'connect_thaid', 'เชื่อมต่อบัญชีกับ ThaID สำเร็จ IP: '.$_SERVER['REMOTE_ADDR']);
                Yii::$app->session->setFlash('edit_success', [
                    'body'=> 'เชื่อมต่อกับ ThaID สำเร็จแล้ว!',
                    'options'=>['class'=>'alert-success']
                ]);
            } else {
                Yii::$app->session->setFlash('alert-home', [
                    'body' => 'ระบบเกิดข้อผิดพลาดในการเชื่อมต่อ',
                    'options'=>['class'=>'alert-danger']
                ]);
            }
            return $this->redirect(['/users/view', 'id' => Yii::$app->user->id]);
        }

        if (!$userThaid) {
            // Backend strategy: Do NOT allow auto-registration
            Yii::$app->session->setFlash('alert-login', [
                'body' => 'ไม่พบข้อมูลผู้ดูแลระบบที่ผูกกับ ThaID นี้ กรุณาเข้าสู่ระบบด้วย Username/Password ปกติก่อนเพื่อผูกบัญชีในหน้า Profile (หากมี)',
                'options' => ['class' => 'alert-danger']
            ]);
            return $this->redirect(['/login']);
        }

        // Login user
        $identity = User::findOne($userThaid->user_id);
        if ($identity && Yii::$app->user->login($identity, 3600 * 24 * 30)) {
            // Check if user has permission to access backend (optional but recommended)
            // PermissionAccess::BackendAccess handled in SiteController::actionIndex usually
            return $this->redirect(['/site/index']);
        } else {
            Yii::$app->session->setFlash('alert-login', [
                'body' => 'Could not login user identity.',
                'options' => ['class' => 'alert-danger']
            ]);
            return $this->redirect(['/login']);
        }
    }

    public function actionDisconnect($id = null)
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['/login']);
        }

        $currentUid = Yii::$app->user->id;
        $targetUid = $id ?: $currentUid;

        // Security check: If trying to disconnect someone else, must be admin
        if ($id !== null && $id != $currentUid) {
            // Check if current user has admin role or permission
            $isAdmin = strpos(\backend\components\BackendHelper::getRoleNameText($currentUid), 'Admin') !== false;
            $hasUserPermission = \backend\components\PermissionAccess::BackendAccess('user_update', 'helper');

            if (!$isAdmin && !$hasUserPermission) {
                Yii::$app->session->setFlash('edit_success', [
                    'body'=> 'คุณไม่มีสิทธิ์ดำเนินการนี้',
                    'options'=>['class'=>'alert-danger']
                ]);
                return $this->redirect(['/users/view', 'id' => $id]);
            }
        }

        $userThaid = UserThaid::findOne(['user_id' => $targetUid]);

        if ($userThaid) {
            if ($userThaid->delete()) {
                \backend\components\BackendHelper::saveUserLog('user', $targetUid, $currentUid, 'disconnect_thaid', 'ยกเลิกการเชื่อมต่อกับ ThaID สำเร็จ (โดยแอดมิน ID: '.$currentUid.') IP: '.$_SERVER['REMOTE_ADDR']);
                Yii::$app->session->setFlash('edit_success', [
                    'body'=> 'ยกเลิกการเชื่อมต่อกับ ThaID สำเร็จแล้ว',
                    'options'=>['class'=>'alert-success']
                ]);
            } else {
                Yii::$app->session->setFlash('edit_success', [
                    'body'=> 'ไม่สามารถยกเลิกการเชื่อมต่อได้ในขณะนี้ กรุณาลองใหม่ภายหลัง',
                    'options'=>['class'=>'alert-danger']
                ]);
            }
        }

        return $this->redirect(['/users/view', 'id' => $targetUid]);
    }
}
