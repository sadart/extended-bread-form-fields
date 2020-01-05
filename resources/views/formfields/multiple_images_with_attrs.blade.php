<br>
@if(isset($dataTypeContent->{$row->field}))
    <?php $images = json_decode($dataTypeContent->{$row->field}); ?>
    @if($images != null)
        <div class="multiple-images">
            @foreach($images as $image)
                <div class="img_settings_container" data-field-name="{{ $row->field }}">
                    <img src="{{ Voyager::image( $image->name ) }}" data-image="{{ $image->name }}" data-id="{{ $dataTypeContent->getKey() }}">
                    <div class="links">
                        <a href="#" class="voyager-params show-inputs"></a>
                        <a href="#" class="voyager-x remove-multi-image-ext"></a>
                    </div>
                    
                    <div class="form-group">
                        <label><b>alt:</b><input class="form-control" type="text" name="{{ $row->field }}_ext[{{ $loop->index }}][alt]" value="{{ $image->alt }}"></label>
                        <label><b>title:</b><input class="form-control" type="text" name="{{ $row->field }}_ext[{{ $loop->index }}][title]" value="{{ $image->title }}"></label>
                    </div>
                    
                </div>
            @endforeach
        </div>
    @endif

@endif
<div class="clearfix"></div>
<input @if($row->required == 1) required @endif type="file" name="{{ $row->field }}[]" multiple="multiple" accept="image/*">

<!-- Start Delete File Modal -->

<div class="modal fade modal-danger" id="confirm_delete_modal_ext">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"
                        aria-hidden="true">&times;</button>
                <h4 class="modal-title"><i class="voyager-warning"></i> {{ __('voyager::generic.are_you_sure') }}</h4>
            </div>

            <div class="modal-body">
                <h4>{{ __('voyager::generic.are_you_sure_delete') }} '<span class="confirm_delete_name_ext"></span>'</h4>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{{ __('voyager::generic.cancel') }}</button>
                <button type="button" class="btn btn-danger" id="confirm_delete_ext">{{ __('voyager::generic.delete_confirm') }}</button>
            </div>
        </div>
    </div>
</div>
<!-- End Delete File Modal -->

<script>
document.addEventListener('DOMContentLoaded', function(){
    var $file;

    $('.remove-multi-image-ext').on('click', function (e) {
        e.preventDefault();
        $image = $(this).parent().siblings('img');
        $file = $image;

        params = {
            slug:         '{{ $dataType->slug }}',
            image:        $image.data('image'),
            id:           $image.data('id'),
            field:        $image.parent().data('field-name'),
            multiple_ext: true,
            _token:       '{{ csrf_token() }}'
        }

        $('.confirm_delete_name_ext').text($image.data('image'));
        $('#confirm_delete_modal_ext').modal('show');
    });
    
    $('.show-inputs').on('click', function (e) {
        e.preventDefault();
        $(this).parent().parent().children('.form-group').toggle();
    });    

    $('#confirm_delete_ext').on('click', function(){
                $.post('{{ route('voyager.'.$dataType->slug.'.media.remove') }}', params, function (response) {
                    if ( response
                        && response.data
                        && response.data.status
                        && response.data.status == 200 ) {

                        toastr.success(response.data.message);
                        $file.parent().fadeOut(300, function() { $(this).remove(); })
                    } else {
                        toastr.error("Error removing file.");
                    }
                });

                $('#confirm_delete_modal_ext').modal('hide');
            });
});
</script>

<style>
.multiple-images{
    display: flex;
    flex-wrap: wrap;
    margin-bottom: 10px;
}
.multiple-images .links{
    justify-content: center;
    display: flex;
}
.multiple-images .links a{
    margin: 0 5px;
}
.multiple-images>div{
    display: flex;
    flex-direction: column;
    margin-right: 10px;
}
.multiple-images img{
    max-width:200px; 
    height:auto; 
    display:block; 
    padding:2px; 
    border:1px solid #ddd; 
    margin-bottom:5px;
}
.multiple-images .form-group{
    display: none;
}
.multiple-images label{
    display: block;
}
.multiple-images label b{
    display: inline-block;
    font-size: 10px;
    width: 25px;
}
.multiple-images label input{
    width: 160px;
    display: inline-block;
}
</style>