<?php

namespace r0n1k\yii2imagewidget;

/**
 *  @property integer $id
 *  @property string $group
 */
class ImageModel extends \yii\db\ActiveRecord
{

    public $imagesPath = "@frontend/web/images";
    public $imagesUrl = "/images";

    public static function tableName()
    {
        return "imagewidget";
    }

    public function init()
    {
        $this->group = "";
    }

    public function rules()
    {
        return [
            [['original_path','sm_path','md_path','lg_path','group'],'string','max'=>64],
            [['original_path','sm_path','md_path','lg_path'],'unique'],
        ];
    }


    public static function resizeAndSave($src, $newW, $newH, $dest)
    {

        $arr_image_details = getimagesize($src);
        $original_width  = $arr_image_details[0];
        $original_height = $arr_image_details[1];

        $thumbnail_width  = $newW;
        $thumbnail_height = $newH;


        if ($original_width > $original_height) {
            $new_width = $thumbnail_width;
            $new_height = intval($original_height * $new_width / $original_width);
        } else {
            $new_height = $thumbnail_height;
            $new_width = intval($original_width * $new_height / $original_height);
        }

        $dest_x = intval(($thumbnail_width - $new_width) / 2);
        $dest_y = intval(($thumbnail_height - $new_height) / 2);



        if ($arr_image_details[2] == IMAGETYPE_GIF) {
            $imgt = "ImageGIF";
            $imgcreatefrom = "ImageCreateFromGIF";
        }
        if ($arr_image_details[2] == IMAGETYPE_JPEG) {
            $imgt = "ImageJPEG";
            $imgcreatefrom = "ImageCreateFromJPEG";
        }
        if ($arr_image_details[2] == IMAGETYPE_PNG) {
            $imgt = "ImagePNG";
            $imgcreatefrom = "ImageCreateFromPNG";
        }

        if ($imgt) {
            $old_image = $imgcreatefrom($src);
            $new_image = imagecreatetruecolor($thumbnail_width, $thumbnail_height);
            $backgroundColor = imagecolorallocate($new_image, 255, 255, 255);
            imagefill($new_image, 0, 0, $backgroundColor);
            imagecopyresized($new_image, $old_image, $dest_x, $dest_y, 0, 0, $new_width, $new_height, $original_width, $original_height);
            $imgt($new_image, \Yii::getAlias($dest));

            return true;
        }
    }


    public static function handleUrl($url) : ImageModel
    {
        $url = trim($url);
        $url_array = parse_url($url);

        if (!isset($url_array["host"])) {
            throw new \Exception("Trying to load image from url ($url) but url is invalid");
        }

        if( (gethostbyname($url_array["host"]) == $url_array["host"]) ) {
            throw new \Exception("Trying to load image from url ($url) but site is offline");
        }

        $fname = explode('/', $url);
        $fname = \Yii::getAlias('@runtime/'.$fname[sizeof($fname)-1]);

        file_put_contents($fname, fopen($url, 'r'));
        return static::handle($fname);
    }


    protected static function handle($fname) : ImageModel
    {
        set_time_limit(10);
        $hash = hash_file("sha256", $fname);
        if( static::find()->where(['hash'=>$hash])->exists() ){
            return static::find()->where(['hash' => $hash])->one();
        }

        $image = new ImageModel();
        $image->hash = $hash;

        $extension = substr(mime_content_type($fname), strlen('image/'));

        do {
            $baseName = hash("crc32", $fname.time());
            $original_path = \Yii::getAlias( $image->imagesPath.'/original/'.$baseName.'.'.$extension );
        } while( ImageModel::find()->where(['original_path' => $original_path])->exists() );


        $image->original_path = $original_path;
        rename($fname, $original_path);

        $image->sm_path = '/sm/'.$baseName.'.'.$extension;
        $image->md_path = '/md/'.$baseName.'.'.$extension;
        $image->lg_path = '/lg/'.$baseName.'.'.$extension;

        $resizing =
            ImageModel::resizeAndSave($image->original_path, 256, 256,   $image->imagesPath.$image->sm_path) &&
            ImageModel::resizeAndSave($image->original_path, 812, 812,   $image->imagesPath.$image->md_path) &&
            ImageModel::resizeAndSave($image->original_path, 1500, 1500, $image->imagesPath.$image->lg_path);

        if(!$resizing){
            throw new \Exception("Error resizing");
        }

        $image->save();
        return $image;
    }


    public static function handleFile($file) : ImageModel
    {
        $fname = hash('crc32',time()).$file['name'];
        move_uploaded_file($file['tmp_name'], $fname);

        return static::handle($fname);
    }

    public function getSmUrl()
    {
        return $this->imagesUrl.$this->sm_path;
    }

    public function getMdUrl()
    {
        return $this->imagesUrl.$this->md_path;
    }

    public function getLgUrl()
    {
        return $this->imagesUrl.$this->lg_path;
    }


    public function beforeDelete()
    {
        @unlink(\Yii::getAlias($this->imagesPath.$this->original_path));
        @unlink(\Yii::getAlias($this->imagesPath.$this->sm_path));
        @unlink(\Yii::getAlias($this->imagesPath.$this->md_path));
        @unlink(\Yii::getAlias($this->imagesPath.$this->lg_path));
    }

}
