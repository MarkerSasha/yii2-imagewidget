<?php

namespace r0n1k\yii2imagewidget;

/**
 *  @property integer $id
 *  @property string $group
 *  @property string $original_name
 *  @property string $hash
 *  @property string $extension
 */
class ImageModel extends \yii\db\ActiveRecord
{

    public $imagesPath = "@frontend/web/images";
    public $imagesUrl = "@web/images";
    public $runtimePath = "@runtime/imagewidget";

    public static function tableName()
    {
        return "imagewidget";
    }

    public function init()
    {
        $this->group = "";
        $this->runtimePath = \Yii::getAlias($this->runtimePath);
        if(! is_dir($this->runtimePath) ){
            if(!mkdir($this->runtimePath, 0777, true)){
                throw new \Exception("ImageWidget: make runtime dir exception");
            }
        }
    }

    public function rules()
    {
        return [
            [['original_name','group', 'extension'],'string','max' => 64],
            [['original_name','hash'],'unique'],
            ['extension', 'in', 'range' => ['jpg','jpeg','gif','png']],
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


    public function getFullPath()
    {
        return \Yii::getAlias( $this->imagesPath ) . 
            DIRECTORY_SEPARATOR . 'original' . DIRECTORY_SEPARATOR . 
            $this->original_name . '.' . $this->extension;
    }



    public function makeThumb(int $width, int $height){

        $fileName = $this->original_name. '.' . "$width.$height." . $this->extension;
        $basePath = \Yii::getAlias( $this->imagesPath );
        /* fullPath like: /www/project/frontend/web/images/<hash>.<width>.<height>.<extension> */
        $fullPath = $basePath . DIRECTORY_SEPARATOR . $fileName;
        $fullUrl = \Yii::getAlias( $this->imagesUrl ) . DIRECTORY_SEPARATOR . $fileName;

        if(! is_file( $fullPath ) ){
            if(! $this->resizeAndSave( $this->getFullPath() ,$width, $height, $fullPath ) ){
                throw new \Exception("makeThumb error");
            }
        }

        return $fullUrl;
    }


    /**
     *  upload by url
     *  @param string $url
     *  @return ImageModel
     */
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

        $image->extension = $extension;

        if(!$image->validate(['extension']) ){
            @unlink($fname);
        }

        do {
            $baseName = hash("crc32", $fname.time());
            $original_name = $baseName;
        } while( ImageModel::find()->where(['original_name' => $original_name])->exists() );


        $image->original_name = $original_name;
        rename(
            $fname, 
            \Yii::getAlias(
                $image->imagesPath.
                DIRECTORY_SEPARATOR.
                'original'.
                DIRECTORY_SEPARATOR.
                $original_name.'.'.$extension
            )
        );

        $image->save();
        return $image;
    }


    /**
     *  @deprecated use handleUploadedFile instead
     *  alias for handleUploadedFile
     */
    public static function handleFile($file) : ImageModel
    {
        return static::handleUploadedFile($file);
    }

    public static function handleUploadedFile($file) : ImageModel
    {
        $fname = hash('crc32',time()).$file['name'];
        move_uploaded_file($file['tmp_name'], $fname);

        return static::handle($fname);
    }

    public function beforeDelete()
    {
        @unlink(\Yii::getAlias($this->imagesPath.$this->original_name));
        $basePath = \Yii::getAlias($this->imagesPath);

        $files = array_filter(scandir( $basePath ), function($file) use ($basePath) {
            return is_file( $basePath . DIRECTORY_SEPARATOR . $file ) && preg_match("/^\w+\.\d+\.\d+\.\w{3,4}$/", $file);
        });

        foreach( $files as $file ){
            @unlink( $basePath . DIRECTORY_SEPARATOR . $file );
        }
    }

}
