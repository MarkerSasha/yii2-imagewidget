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
            $images = $images->where(['group'=>$this->group])->indexBy('id')->all();
        } else {
            $images = $images->all();
        }

        $input_value = $this->model->{$this->attribute};
        if(!is_array($input_value)){
            if(! is_null($input_value) ){
                $input_value = [$input_value];
            } else {
                $input_value = [];
            }
        }

        $input_name = \yii\helpers\Html::getInputName($this->model, $this->attribute);
        $base_input_name = $input_name;
        if( $this->multiply ){
            $input_name.="[]";
        }


        $images_json = json_encode((function($array){
            $ids = array_keys($array);
            $result = [];
            foreach($ids as $id){
                $image = array_shift($array);
                $result[$id] = [
                    'id'      => intval($id),
                    'thumb' => $image->makeThumb(200,200),
                    'group'   => $image->group,
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
            'base_input_name' => $base_input_name,
        ]);
    }

}
