<?php

namespace app\controllers\generator;

use app\controllers\BaseController;
use app\models\search\BrandVariationSearch;
use app\models\search\CategoryVariationSearch;
use app\models\Variation;
use app\models\VariationItem;
use yii\base\DynamicModel;
use app\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Контроллер работы с вариациями категорий, брендов
 *
 * Class VariationsController
 * @package app\controllers\generator
 * @author Denis Dyadyun <sysadm85@gmail.com>
 */
class VariationsController extends BaseController
{
    /**
     * @param string $type
     * @param int $shopId
     * @return string
     */
    public function actionIndex($type, $shopId)
    {
        if ($type == Variation::TYPE_BRAND) {
            $searchModel = new BrandVariationSearch();
        } else {
            $searchModel = new CategoryVariationSearch();
        }

        $searchModel->shopId = $shopId;
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $searchModel->search($this->request->queryParams),
            'variationName' => $this->getVariationName($type)
        ]);
    }

    /**
     * @param $id
     * @return Response
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = Variation::findOne($id);

        if (!$model) {
            throw new NotFoundHttpException();
        }

        $model->delete();

        return $this->redirect(['index', 'shopId' => $model->shop_id, 'type' => $model->entity_type]);
    }

    /**
     * @param $type
     * @return string
     */
    protected function getVariationName($type)
    {
        switch ($type) {
            case Variation::TYPE_BRAND:
                return 'бренды';
            case Variation::TYPE_CATEGORY:
                return 'категории';
        }
        
        return '';
    }

    /**
     * Обновление/добавление вариаций
     *
     * @param int $entityId
     * @param string $entityType
     * @param int $shopId
     * @param int $id
     * @return array
     */
    public function actionUpdateVariation($entityId, $entityType, $shopId, $id = null)
    {
        $this->response->format = Response::FORMAT_JSON;
        $variation = $this->request->post('variation');
        $isUseInGeneration = $this->request->post('isUseInGeneration');
        $variationItemId = $this->request->post('variationItemId');

        if ($id) {
            $variationModel = Variation::findOne($id);
        } else {
            $variationModel = $this->getVariationModel($entityId, $entityType, $shopId);
        }

        if ($variationModel->isNewRecord) {
            $variationModel->save();
        }

        if ($variationItemId) {
            $variationItem = VariationItem::findOne($variationItemId);
        } else {
            $variationItem = new VariationItem();
        }

        if (!$variationItem) {
            return ['output' => $variation, 'message' => 'Вариация не найдена'];
        }

        $variationItem->is_use_in_generation = $isUseInGeneration;
        $variationItem->variation_id = $variationModel->id;
        $variationItem->value = $variation;

        $variationItem->save();

        return ['output' => $variation];
    }

    /**
     * Добавление новых вариаций
     *
     * @param int $entityId
     * @param string $entityType
     * @param int $shopId
     * @param int $id
     * @return array
     */
    public function actionAddVariations($entityId, $entityType, $shopId, $id = null)
    {
        $this->response->format = Response::FORMAT_JSON;
        $newVariations = $this->request->post('newVariation');
        $newVariations = array_filter(preg_split("#\r\n|\n#", $newVariations));
        $isUseInGeneration = $this->request->post('isUseInGeneration');

        if (empty($newVariations)) {
            return ['message' => 'Нечего сохранять', 'output' => ''];
        }

        if ($id) {
            $variationModel = Variation::findOne($id);
        } else {
            $variationModel = $this->getVariationModel($entityId, $entityType, $shopId);
        }

        if ($variationModel->isNewRecord) {
            $variationModel->save();
            array_unshift($newVariations, $this->request->post('modelTitle'));
        }

        foreach ($newVariations as $newVariation) {
            $variationItem = new VariationItem([
                'is_use_in_generation' => $isUseInGeneration,
                'value' => $newVariation,
                'variation_id' => $variationModel->id
            ]);
            if (!$variationItem->save()) {
                return ['message' => 'Ошибка при сохранении: ' . ArrayHelper::first($variationModel->getFirstErrors())];
            }
        }

        return ['message' => '', 'output' => 'Добавить вариации'];
    }

    /**
     * @return array
     * @throws NotFoundHttpException
     * @throws BadRequestHttpException
     */
    public function actionDeleteVariation()
    {
        $this->response->format = Response::FORMAT_JSON;
        $variationItemId = $this->request->post('variationItemId');

        if (!$variationItemId) {
            throw new BadRequestHttpException();
        }

        $variationModel = VariationItem::findOne($variationItemId);

        if (!$variationModel) {
            throw new NotFoundHttpException();
        }

        if ($variationModel->delete()) {
            return ['status' => 'success'];
        } else {
            return ['status' => 'error', ArrayHelper::first($variationModel->getFirstErrors())];
        }
    }

    /**
     * @param int $entityId
     * @param string $entityType
     * @param int $shopId
     * @return Variation
     */
    protected function getVariationModel($entityId, $entityType, $shopId)
    {
        $variationModel = Variation::find()
            ->andWhere([
                'entity_id' => $entityId,
                'entity_type' => $entityType,
                'shop_id' => $shopId
            ])->one();

        if (!$variationModel) {
            $variationModel = new Variation([
                'entity_id' => $entityId,
                'entity_type' => $entityType,
                'shop_id' => $shopId
            ]);
        }

        return $variationModel;
    }

    /**
     * @param int $id
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionSetShuffleName($id)
    {
        $this->response->format = Response::FORMAT_JSON;
        $variation = Variation::findOne($id);

        if (!$variation) {
            throw new NotFoundHttpException();
        }

        $variation->shuffle_name = $this->request->post('shuffleName');
        $variation->save();

        return ['output' => $variation->shuffle_name];
    }
}
