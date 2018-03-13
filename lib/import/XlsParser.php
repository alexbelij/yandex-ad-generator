<?php

namespace app\lib\import;

use app\components\LoggerInterface;
use app\lib\import\xls\XlsProduct;
use app\models\ExternalBrand;
use app\models\ExternalCategory;
use app\models\ExternalProduct;
use app\models\FileImport;

/**
 * Импорт товаров из файла xls
 *
 * Class XlsParser
 * @package app\lib\import
 */
class XlsParser implements ImportInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var FileImport
     */
    protected $fileImport;

    /**
     * XlsParser constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function import(FileImport $fileImport)
    {
        $this->fileImport = $fileImport;

        if (!file_exists($fileImport->filename)) {
            throw new ImportException("Файл - '$fileImport->filename' не найден!");
        }

        $phpExcelReader = \PHPExcel_IOFactory::load($fileImport->filename);
        $phpExcelReader->setActiveSheetIndex(0);
        $sheet = $phpExcelReader->getActiveSheet();

        $rowIterator = $sheet->getRowIterator(2);
        foreach ($rowIterator as $row) {
            $xlsProduct = new XlsProduct([
                'category' => trim($sheet->getCellByColumnAndRow(0, $row->getRowIndex())->getValue()),
                'vendor' => trim($sheet->getCellByColumnAndRow(1, $row->getRowIndex())->getValue()),
                'model' => trim($sheet->getCellByColumnAndRow(2, $row->getRowIndex())->getValue()),
                'price' => $sheet->getCellByColumnAndRow(3, $row->getRowIndex())->getValue(),
                'url' => $sheet->getCellByColumnAndRow(4, $row->getRowIndex())->getValue(),
            ]);

            $category = $this->getExternalCategory($xlsProduct->category);
            $brand = $this->getExternalBrand($xlsProduct->vendor);

            $externalProduct = ExternalProduct::find()
                ->andWhere([
                    'shop_id' => $this->fileImport->shop_id,
                    'title' => $xlsProduct->model,
                    'category_id' => $category->primaryKey,
                    'brand_id' => $brand->primaryKey
                ])->one();

            if (!$externalProduct) {
                $externalProduct = new ExternalProduct([
                    'shop_id' => $this->fileImport->shop_id,
                    'title' => $xlsProduct->model,
                    'category_id' => $category->primaryKey,
                    'brand_id' => $brand->primaryKey,
                    'outer_id' => md5(time() . $xlsProduct->model),
                ]);
            }

            $externalProduct->original_title = $xlsProduct->model;
            $externalProduct->is_available = true;
            $externalProduct->url = $xlsProduct->url;
            $externalProduct->price = round($xlsProduct->price);
            $externalProduct->save();
        }
    }


    /**
     * @param string $categoryName
     * @return ExternalCategory
     */
    protected function getExternalCategory($categoryName)
    {
        if (empty($categoryName)) {
            return $this->getOrCreateDefaultCategory();
        }

        $categoryName = trim($categoryName);
        $category = ExternalCategory::find()
            ->andWhere([
                'shop_id' => $this->fileImport->shop_id,
                'title' => $categoryName
            ])->one();

        if (!$category) {
            $category = new ExternalCategory([
                'title' => $categoryName,
                'shop_id' => $this->fileImport->shop_id
            ]);
            $category->save();
        }

        return $category;
    }

    /**
     * @return ExternalCategory
     */
    protected function getOrCreateDefaultCategory()
    {
        static $defaultCategory = null;

        if (is_null($defaultCategory)) {
            $defaultCategory = ExternalCategory::find()
                ->andWhere(['shop_id' => $this->fileImport->shop_id, 'title' => ''])
                ->one();

            if (!$defaultCategory) {
                $defaultCategory = new ExternalCategory([
                    'title' => '',
                    'shop_id' => $this->fileImport->shop_id
                ]);
                $defaultCategory->save();
            }
        }

        return $defaultCategory;
    }

    /**
     * @param string $vendorName
     * @return ExternalBrand|array|null|\yii\db\ActiveRecord
     */
    protected function getExternalBrand($vendorName)
    {
        $vendorName = trim($vendorName);
        $extBrand = ExternalBrand::find()
            ->andWhere([
                'shop_id' => $this->fileImport->shop_id,
                'title' => $vendorName
            ])->one();

        if (!$extBrand) {
            $extBrand = new ExternalBrand([
                'title' => $vendorName,
                'shop_id' => $this->fileImport->shop_id
            ]);
            $extBrand->save();
        }

        return $extBrand;
    }
}
