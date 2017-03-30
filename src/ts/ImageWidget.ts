import { ImagesInterface } from './ImagesInterface';
import { ImageInterface } from './ImageInterface';

export class ImageWidget {
    public chosenImages: Array<number>;

    public maxImages?:number;

    constructor(public dom_id:string, public inputName:string, public images:ImagesInterface,
        public group:string, public multiply:boolean){

        this.chosenImages = [];


        $('#'+this.dom_id+' [name="'+this.inputName+'"]').each((i, obj):void=>{
            this.choseImage(parseInt($(<any>obj).val()));
        });

        $('#'+this.dom_id).on('click','.thumbnail.zoomable',(evt):void=>{
            this.zoom(parseInt($(evt.target).parents('.imagewidget-input-image').attr('data-id')));
        });

        $('#'+this.dom_id+' input[type=file]').bootstrapFileInput();
        $('#'+this.dom_id+' .file-input-wrapper').removeClass('btn-default').addClass('btn-primary');

        $('#'+this.dom_id+' .imagewidget-add-image').on('click', ():void=>{
            this.openLibrary();
        });

        $('#'+this.dom_id+' .imagewidget-ok-btn').on('click', ():void=>{
            this.closePopups();
        });

        $('#'+this.dom_id+' input[type=file]').on('change', (evt):void=>{
            evt.stopPropagation();
            evt.preventDefault();

            let files = (<any>evt.target).files;


            $.each(files, (key, value):void => {
                let unique = Math.round(Math.random()*1000);
                let data = new FormData();
                data.append(key, value);
                data.append('group', this.group);
                let reader = new FileReader();
                let image = $.Deferred();

                reader.onload = (e)=> {
                    let skeleton = '<div class="loading col col-xs-6 col-sm-4 col-md-3 col-lg-2 imagewidget-input-image" data-unique="'+unique+'">'+
                        '<div class="thumbnail">'+
                        '<img src="'+(<any>e.target).result+'">'+
                        '</div>'+
                        '</div>';

                    $('#'+this.dom_id+' .imagewidget-input .imagewidget-add-image').before(skeleton);

                    image.resolve( $('[data-unique='+unique+']') );
                };


                reader.readAsDataURL(value);

                let ajaxOpts = {
                    url: '/image/upload',
                    type: 'POST',
                    data: data,
                    cache: false,
                    dataType: 'json',
                    processData: false,
                    contentType: false,
                    success: (data:ImageInterface, textStatus:string, jqXHR:any):void=>{
                        image.done((img:any):void=>{
                            img.removeClass('loading').removeClass('error').attr('data-id', data.id);
                            this.addImage( data );
                            img.find('button').remove();
                            img.find('.thumbnail').before(
                                '<button type="button" class="btn btn-danger remove">'+
                                '<span class="glyphicon glyphicon-remove"></span>'+
                                '</button>'
                            ).addClass('zoomable');
                            img.find('button').on('click',():void=>{
                                this.unchoseImage( data.id );
                            });
                            $('#'+this.dom_id).append('<input type="hidden" name="'+this.inputName+'" value="'+data.id+'"/>');
                            this.chosenImages.push(data.id);
                        });
                    },
                    error:(err:any)=>{
                        image.done((img:any)=>{
                            if(! img.data('tried') ){
                                img.data('tried', true);
                                img.find('.thumbnail').before(
                                    '<button type="button" class="btn btn-success retry">'+
                                    '<span class="glyphicon glyphicon-refresh"></span>'+
                                    '</button>'+
                                    '<button type="button" class="btn btn-danger remove">'+
                                    '<span class="glyphicon glyphicon-remove"></span>'+
                                    '</button>'
                                );
                                img.addClass('error').find('button.retry').on('click',()=>{$.ajax(ajaxOpts);});
                                img.find('button.remove').on('click', ()=>{
                                    img.remove();
                                });
                            }
                        });
                    }
                };

                $.ajax(ajaxOpts);

            });

            this.closePopups();

        });

        $('#'+this.dom_id+' .imagewidget-zoom > .img-port').on('click',()=>{ this.closePopups(); });
        let $inp = $('input[data-imagewidget]');
        $inp.data('value', JSON.stringify($inp.val()));
    }





    public zoom(id:number){
        $('body').css('overflow', 'hidden');
        let $backlight = $('#'+this.dom_id+' .imagewidget-backlight'),
            $zoom = $backlight.find('.imagewidget-zoom');
        $backlight.removeClass('closed');
        $zoom.removeClass('closed').find('img').attr('src', '/images'+this.images[id].lg_path);
        $zoom.find('.img-port').css('background-image', "url('/images"+this.images[id].lg_path+"')");

    }


    public choseImage(id:number){
        /** @todo */
        //if( this.chosenImages.length >= this.maxImages || 20 ){
            //alert("Достигнуто максимальное количиство изображений");
            //return;
        //}
        if( this.chosenImages.indexOf(id) !== -1 ){ return; }
        $('#'+this.dom_id).append('<input type="hidden" name="'+this.inputName+'" value="'+id+'"/>');
        this.chosenImages.push(id);
        $('#'+this.dom_id+' .imagewidget-library-image[data-id='+id+']').addClass('chosen');
        $('#'+this.dom_id+' .imagewidget-input .imagewidget-add-image').before(
            '<div class="col col-xs-6 col-sm-4 col-md-3 col-lg-2 imagewidget-input-image" data-id="'+id+'">'+

            '<button type="button" class="btn btn-danger remove">'+
            '<span class="glyphicon glyphicon-remove"></span>'+
            '</button>'+
            '<div class="thumbnail zoomable">'+
            '<img src="/images'+this.images[id].sm_path+'">'+
            '</div>'+
            '</div>');
        $('#'+this.dom_id+' .imagewidget-input-image[data-id='+id+'] button.remove').on('click',(evt)=>{
            this.unchoseImage(parseInt($((<any>evt.currentTarget).parentElement).data('id')));
        });

        if( !this.multiply ){
            $('#'+this.dom_id+' .imagewidget-input .imagewidget-add-image').hide();
        }
    }


    public unchoseImage(id:number){
        $('#'+this.dom_id+' .imagewidget-library-image.chosen[data-id='+id+']').removeClass('chosen');
        $('#'+this.dom_id+' .imagewidget-input-image[data-id='+id+']').remove();
        $('#'+this.dom_id+' [name="'+this.inputName+'"][value='+id+']').remove();
        this.chosenImages.splice( this.chosenImages.indexOf(id), 1 );

        if( !this.multiply ){
            $('#'+this.dom_id+' .imagewidget-input .imagewidget-add-image').show();
        }
    }

    public unchoseImages(){
        let currentImages:number[] = [];
        $('#'+this.dom_id+' [name="'+this.inputName+'"]').each(()=>{
            currentImages.push(parseInt($(this).val()));
        });

        for(let i = 0; i < currentImages.length; i++){
            this.unchoseImage(currentImages[i]);
        }
    }

    public renderLibrary(pagination = 0) {
        let images = this.images,
            $library = $('#'+this.dom_id+' .imagewidget-popup'),
            $images_container = $library.find('.imagewidget-popup-content').empty(),
            $pagination = $library.find('.pagination').empty(),
            imagesCount = 0;

        if( $images_container.width() === 0 ){
            return;
        }

        for( let i in images ){
            if(images.hasOwnProperty(i)){
                imagesCount++;
            }
        }

        let imagesPerPage = Math.floor($images_container.width() / 240),
            maxPagination = Math.ceil( imagesCount / imagesPerPage );

        for(let i:number = 0; i < maxPagination; i++){
            $pagination.append('<li data-pagination-num="'+i+'" class="'+(pagination==i?'active':'')+'"><a>'+(i+1)+'</a></li>')
            $pagination.find('li[data-pagination-num="'+i+'"]').on('click',(evt)=>{
                let newPagination = parseInt($((evt).target.parentElement).data('pagination-num'));
                this.renderLibrary(newPagination);
            });
        }

        let index = imagesPerPage * pagination,
            counter = 0;
        for( let img_id in images ){
            if( images.hasOwnProperty(img_id) ){
                let image = images[img_id];
                if(counter >= index && counter < (index + imagesPerPage)){
                    $images_container.append(
                        '<div class="col thumbnail imagewidget-library-image '+
                            (this.chosenImages.indexOf(image.id)!==-1?'chosen':'')+'" data-id="'+img_id+'">'+
                        '<img src="/images'+image['sm_path']+'" />'+
                        '</div>'
                    );
                }
                counter++;
            }
        }

        if( $images_container.data('evt_handler') !== true ){
            $images_container.data('evt_handler', true);
            $images_container.on('click', '.thumbnail', (evt):void => {
                let target = evt.currentTarget;
                if( $(target).hasClass('chosen') ){
                    this.unchoseImage(parseInt($(target).data('id')));
                } else {
                    this.choseImage(parseInt($(target).data('id')));
                }

            });
        }
    }



    public addImage(data:ImageInterface) : void {
        this.images[data.id] = {
            id: data.id,
            lg_path: data.lg_path,
            md_path: data.md_path,
            sm_path: data.sm_path,
            group: data.group,
        };
    }

    public closePopups() : void {
        $('body').css('overflow', 'initial');
        $('#'+this.dom_id+' .imagewidget-backlight').addClass('closed').children().addClass('closed');
    }


    public openLibrary() : void {
        $('body').css('overflow', 'hidden');
        $('#'+this.dom_id+' .imagewidget-backlight').removeClass('closed').find('.imagewidget-popup').removeClass('closed');
        this.renderLibrary();
    }

}
