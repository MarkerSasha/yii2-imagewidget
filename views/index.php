<script>
    (function(){
        var onload = window.onload;
        window.onload = function(){
            if( onload instanceof Function ){
                onload.apply(window,arguments);
            }
            new ImageWidget("<?=$dom_id?>", "<?=$input_name?>", <?=$images_json?>, "<?=$group?>", <?=$multiply?"true":"false"?>);
        };
    })();
</script>

<div class="container imagewidget-main" id="<?=$dom_id?>">

<?= $this->render('popup',[
    'dom_id' => $dom_id,
    'images' => $images,
]) ?>

    <input type="hidden" name="<?=$base_input_name?>" data-filler=1 />
    <?php foreach($input_value as $value): ?>
        <input type="hidden" value="<?=$value->id?>" name="<?=$input_name?>" />
    <?php endforeach; ?>

    <div class="row imagewidget-input">

        <?php foreach($images as $image): continue; ?>
            <div class="col col-xs-6 col-sm-4 col-md-3 col-lg-2 imagewidget-input-image" data-id=<?=$image->id?>>
                <button type="button" class="btn btn-danger pull-right">
                    <span class="glyphicon glyphicon-remove"></span>
                </button>
                <div class="thumbnail zoom">
                    <img src="<?= $image->makeThumb(200,200) ?>">
                </div>
            </div>
        <?php endforeach; ?>

        <div class="col col-xs-6 col-sm-4 col-md-3 col-lg-2 imagewidget-add-image">
            <div class="thumbnail">
                <span class="glyphicon glyphicon-plus"></span>
            </div>
        </div>

    </div>
</div>
