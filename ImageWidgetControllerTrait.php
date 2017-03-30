<?php

namespace r0n1k\yii2imagewidget;

trait ImageWidgetControllerTrait
{

    /**
     *  Upload image by POST request, save it and handle
     */
    public function actionUpload()
    {
        ini_set("upload_max_filesize", "20M");
        $group = \Yii::$app->request->post('group');
        $result = [];
        foreach( $_FILES as $file ){
            if( $file['error'] !== 0 ){
                throw new UploadingImageException($file['error']);
            } else if(! is_uploaded_file( $file['tmp_name'] ) ){
                throw new \yii\web\ForbiddenHttpException("File isn't uploaded");
            }
            break;
        }
        foreach( $_FILES as $file ){
            $model = ImageModel::handleFile($file, $group);
            $model->group = $group;
            $model->save(false);
            $result['id'] = $model->id;
            $result['sm_path'] = $model->sm_path;
            $result['md_path'] = $model->md_path;
            $result['lg_path'] = $model->lg_path;
            break;
        }
        return $result;
    }

    /**
     *  @param int $id id of image
     *  @throws \yii\web\NotFoundHttpException
     *  @return r0n1k\yii2imagewidget\ImageModel
     */
    public function actionGet(int $id) : ImageModel
    {
        $result = ImageModel::findOne($id);
        if(! $result ){
            throw new \yii\web\NotFoundHttpException();
        }
        return $result;
    }


    public function beforeAction($action)
    {
        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return parent::beforeAction($action);
    }
}
