<?php

namespace app\modules\bidManager\lib\traits;

use app\helpers\ArrayHelper;
use yii\db\ActiveRecord;
use yii\web\Response;

/**
 * Class EditableTrait
 * @package app\modules\bidManager\lib\traits
 */
trait EditableTrait
{
    /**
     * @param int $id
     * @return array
     */
    public function actionAjaxUpdate($id)
    {
        $response = \Yii::$app->response;
        $request = \Yii::$app->request;
        $response->format = Response::FORMAT_JSON;
        /** @var ActiveRecord $model */
        $model = $this->findModel($id);

        if ($request->post('hasEditable')) {
            $model->load($request->post());
            $changedAttribute = ArrayHelper::first(array_keys($model->getDirtyAttributes()));
            if (empty($changedAttribute)) {
                return ['output' => '', 'message' => 'Неизветное поле для изменения'];
            }

            if (!$model->save()) {
                return ['output' => '', 'message' => ArrayHelper::first($model->getFirstErrors())];
            }

            return ['output' => $model->{$changedAttribute}, 'message' => ''];
        }

        return ['output' => ''];
    }
}
