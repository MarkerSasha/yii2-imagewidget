<div id="<?=$dom_id?>-backlight" class="imagewidget-backlight closed">
<div id="<?=$dom_id?>-zoom" class="imagewidget-zoom closed"><div class="img-port"><img/></div></div>
<div id="<?=$dom_id?>-popup" class="imagewidget-popup closed">

    <div class="container-fluid">
        <div class="row imagewidget-popup-content">
        </div>

        <div class="row">
            <div class="col col-xs-12 col-md-9 paginator">
                <ul class="pagination">
                    <li class="active"><a href="#">1</a></li>
                    <li class=""><a href="#">2</a></li>
                    <li class=""><a href="#">3</a></li>
                    <li class=""><a href="#">4</a></li>
                    <li class=""><a href="#">5</a></li>
                </ul>
            </div>
            <div class="col col-xs-12 col-md-3">
                <div class="input-field">
                    <div class="input-group">
                        <span class="input-group-btn">
                            <input type="file" multiple=<?= $multiply ? "multiply" : "false" ?> title='Загрузить <span class="glyphicon glyphicon-upload"></span>'/>
                            <button type="button" id="<?=$dom_id."image-choose-btn"?>" class="btn btn-success imagewidget-ok-btn">
                                ОК
                                <span class="glyphicon glyphicon-ok"></span>
                            </button>
                        </span>
                    </div>
                </div>
            </div>
        </div>


    </div>

</div>
</div>
