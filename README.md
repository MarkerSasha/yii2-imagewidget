yii2-image-widget
=================
Simple ajax widget to upload images

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist r0n1k/yii2-imagewidget "*"
```

or add

```
"r0n1k/yii2-imagewidget": "*"
```

to the require section of your `composer.json` file.


Usage
-----

After instalation you need to create *ImageController*
```php
class ImageController extends \yii\web\Controller
{
    use \r0n1k\yii2imagewidget\ImagewidgetControllerTrait;
    public $enableCsrfValidation = false;
}
```

Prepare your model by adding getter and setter for images.
Setter should accept id(s) of image(s), getter should return id(s).
```php
class MyModel extends \yii\db\ActiveRecord
{
    ...
    public function getImage() : int
    {
        /* here some logic to get image id(s)
            probably you have the junction table if the relation is 'many to many'.
            You should make sql query to get ids using your favorite way.
            Or if the relation is one to one, your model has field `image`(or another name)
            and you can just return this field
        */
        return $result;
    }

    public function setImage($value)
    {
        /* Like getter, setter need to implement some logic to set image(s)
            value is array, if widget has option 'multiply' set to true, else value is int.
            When image has not been set, $value === null
        */
    }
    ...
}
```

Now, you can use it in your active forms
```php
...
    $form->field($model, 'image')->widget('\r0n1k\yii2imagewidget\ImageWidget', $options);
...
```
Possible options:

```php
bool $multiply - allow to upload multiply images
string? $group - used to separation images in library. It's like folder name.
```

**Controller can throw \r0n1k\yii2imagewidget\UploadingImageException**

Todo:
- maxImages option
- ImageValidator to use in models
- non-ajax image uploading
