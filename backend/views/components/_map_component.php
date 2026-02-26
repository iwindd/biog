<?php
/**
 * Map Component - Reusable Google Map with LatLng/UTM coordinate input
 *
 * Usage in _form.php:
 *   <?= $this->render('../components/_map_component', [
 *       'model' => $model,
 *       'form' => $form,
 *   ]) ?>
 *
 * @var $model backend\models\Content
 * @var $form yii\widgets\ActiveForm
 * @var $readonly bool (optional, default false)
 */

use yii\helpers\Url;
use frontend\components\GoogleMapHelper;

$readonly = isset($readonly) ? $readonly : false;

// Register assets
$this->registerCssFile(Url::base() . '/css/map-component.css', ['depends' => [\backend\assets\AppAsset::className()]]);
$this->registerJsFile(Url::base() . '/js/map-component.js', ['depends' => \yii\web\JqueryAsset::className()]);
$this->registerJsFile(GoogleMapHelper::getGoogleMapApiUrl(), ['depends' => \yii\web\JqueryAsset::className(), 'async' => true, 'defer' => true]);

$latitude = $model->latitude ?? '';
$longitude = $model->longitude ?? '';
?>

<div class="map-component-panel">
    <div class="map-component-title">
        <i class="fa fa-map-marker"></i> ระบุพิกัด
    </div>

    <?php if (!$readonly): ?>
    <!-- Coordinate mode selector -->
    <div class="map-coord-mode-group">
        <label for="map-coord-mode">รูปแบบพิกัด</label>
        <select id="map-coord-mode" class="form-control">
            <option value="latlng">Latitude / Longitude</option>
            <option value="utm">UTM (Universal Transverse Mercator)</option>
        </select>
    </div>

    <!-- LatLng inputs -->
    <div class="map-coord-fields" id="map-latlng-group">
        <div class="map-coord-row">
            <?= $form->field($model, 'latitude', [
                'options' => ['class' => 'form-group map-coord-field'],
            ])->textInput([
                'maxlength' => true,
                'placeholder' => 'เช่น 13.7563',
            ])->label('Latitude (ละติจูด)') ?>

            <?= $form->field($model, 'longitude', [
                'options' => ['class' => 'form-group map-coord-field'],
            ])->textInput([
                'maxlength' => true,
                'placeholder' => 'เช่น 100.5018',
            ])->label('Longitude (ลองจิจูด)') ?>
        </div>
    </div>

    <!-- UTM inputs (hidden by default) -->
    <div class="map-coord-fields" id="map-utm-group" style="display:none;">
        <div class="map-coord-row">
            <div class="form-group utm-zone-field">
                <label for="map-utm-zone">Zone</label>
                <input type="text" id="map-utm-zone" class="form-control" placeholder="เช่น 47N" maxlength="3">
            </div>
            <div class="form-group">
                <label for="map-utm-easting">Easting (ค่า X)</label>
                <input type="text" id="map-utm-easting" class="form-control" placeholder="เช่น 661234.56">
            </div>
            <div class="form-group">
                <label for="map-utm-northing">Northing (ค่า Y)</label>
                <input type="text" id="map-utm-northing" class="form-control" placeholder="เช่น 1521234.56">
            </div>
        </div>
    </div>

    <!-- Geolocation status -->
    <div id="map-geolocation-status" class="map-geolocation-status">
        <i class="fa fa-spinner"></i> กำลังค้นหาตำแหน่งปัจจุบัน...
    </div>
    <?php else: ?>
        <!-- Hidden inputs for view mode -->
        <input id="content-latitude" type="hidden" value="<?= htmlspecialchars($latitude) ?>">
        <input id="content-longitude" type="hidden" value="<?= htmlspecialchars($longitude) ?>">
    <?php endif; ?>

    <!-- Google Map -->
    <div id="map-component-container"
         class="map-component-map"
         data-readonly="<?= $readonly ? 'true' : 'false' ?>">
    </div>

    <?php if (!$readonly): ?>
    <!-- Sync address from pin -->
    <div class="map-sync-address" style="margin-top: 10px;">
        <button type="button" id="btn-sync-address" class="btn btn-info btn-sm" title="ดึงที่อยู่จากตำแหน่ง pin บนแผนที่">
            <i class="fa fa-map-pin"></i> ดึงที่อยู่จากพิกัด
        </button>
        <span id="sync-address-status" style="margin-left: 8px; display: none;"></span>
    </div>
    <?php endif; ?>
</div>
