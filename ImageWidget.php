<?php

namespace r0n1k\yii2imagewidget;

class ImageWidget extends \yii\widgets\InputWidget
{

    public $dom_id_prefix = "imagewidget";

    public $multiply = true;

    public $group = null;


    public function run()
    {
        ImageWidgetAsset::register( $this->view );

        $images = ImageModel::find();
        if(!is_null($this->group)){
            $images = $images->where(['group'=>$this->group])->asArray()->all();
        } else {
            $images = $images->asArray()->all();
        }

        $attribute = $this->attribute;
        $input_value = $this->model->$attribute;
        if(!is_array($input_value)){
            if(! is_null($input_value) ){
                $input_value = [$input_value];
            } else {
                $input_value = [];
            }
        }

        $input_name = \yii\helpers\Html::getInputName($this->model, $this->attribute);
        if( $this->multiply ){
            $input_name.="[]";
        }

        $images_json = json_encode((function($array){
            $ids = [];
            $result = [];
            foreach($array as $i){
                array_push($ids, intval($i['id']));
            }
            foreach($ids as $id){
                $image = array_shift($array);
                $result[$id] = [
                    'id'      => intval($id),
                    'lg_path' => $image['lg_path'],
                    'md_path' => $image['md_path'],
                    'sm_path' => $image['sm_path'],
                    'group'   => $image['group'],
                ];
            }
            return $result;
        })($images));

        return $this->render('index',[
            'group'       => $this->group,
            'dom_id'      => 'imagewidget'.$this->getId(),
            'images'      => $images,
            'multiply'    => $this->multiply,
            'input_name'  => $input_name,
            'input_value' => $input_value,
            'images_json' => $images_json,
        ]);
    }

}
