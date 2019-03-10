@php
    if($dataTypeContent->{$row->field}){
        $old_parameters = json_decode($dataTypeContent->{$row->field});
    }
    $end_id = 0;

    if (is_field_translatable($dataTypeContent, $row)) {
        // $translate_json = get_field_translations($dataTypeContent, $row->field, $row->type, true);
        //  dd(json_decode($translate_json));
        $isFieldTranslatable = true;
    }
@endphp


<div class="custom-parameters">
@if($dataTypeContent->{$row->field})
    @foreach($old_parameters as $parameter)
        <div class="form-group row" row-id="{{$loop->index}}">
            <div class="col-xs-3" style="margin-bottom:0;">
                <input type="text" class="form-control" name="{{ $row->field }}[{{$loop->index}}][key]" value="{{ $parameter->key }}" id="key"/>
            </div>
            <div class="col-xs-3" style="margin-bottom:0;">
                <input type="text" class="form-control" name="{{ $row->field }}[{{$loop->index}}][value]" value="{{ $parameter->value }}" id="value"/>
            </div>
            
            <div class="col-xs-1" style="margin-bottom:0;">
                <button type="button" class="btn old_btn btn-xs" style="margin-top:0px;"><i class="voyager-trash"></i></button>
            </div>
        </div>
        @php 
            $end_id = $loop->index + 1;
        @endphp
    @endforeach
@endif
    <div class="form-group row" row-id="{{ $end_id }}">
        <div class="col-xs-3" style="margin-bottom:0;">
            <input type="text" class="form-control" name="{{ $row->field }}[{{ $end_id }}][key]" value="" id="key"/>
        </div>
        <div class="col-xs-3" style="margin-bottom:0;">
            <input type="text" class="form-control" name="{{ $row->field }}[{{ $end_id }}][value]" value="" id="value"/>
        </div>
        <div class="col-xs-1" style="margin-bottom:0;">
            <button type="button" class="btn btn-success btn-xs" style="margin-top:0px;"><i class="voyager-plus"></i></button>
        </div>
    </div>

    <input type="hidden" name="keyvaluejson" value="{{$row->field}}"/>
</div>



<script>

    function editNameCount(el){
        var str = el.getAttribute('name');
        var old_id = parseInt(el.parentNode.parentNode.getAttribute('row-id'));
        new_str = str.substring(0,str.indexOf('[')+1)
                    + (old_id+1)
                    + str.substring(str.indexOf(']'), str.length);
        return(new_str);
    }

    function addRow(){
        var new_row = this.parentNode.parentNode.cloneNode(true);
        
        new_row.querySelector("#key").setAttribute('name', editNameCount(new_row.querySelector("#key")));
        new_row.querySelector("#key").value = '';
        new_row.querySelector("#value").setAttribute('name', editNameCount(new_row.querySelector("#value")));
        new_row.querySelector("#value").value = '';
        new_row.setAttribute('row-id', parseInt(this.parentNode.parentNode.getAttribute('row-id'))+1)
        
        this.classList.remove('btn-success');
        this.innerHTML = '<i class="voyager-trash"></i>';
        new_row.querySelector('.btn-success').onclick = this.onclick;
        this.onclick = removeRow;
        this.parentNode.parentNode.parentNode.appendChild(new_row);
    };

    function removeRow() {
        this.parentNode.parentNode.remove();
    }

    var buttons = document.querySelectorAll('.custom-parameters .old_btn');
    for (var i = 0; i < buttons.length; i++) buttons[i].onclick = removeRow;
    var suc_buttons = document.querySelectorAll('.custom-parameters .btn-success');
    suc_buttons[suc_buttons.length - 1].onclick = addRow;

    
    
    
    // Multilanguage support
    @if ($isFieldTranslatable)
    document.querySelector(".js-language-label").addEventListener("DOMNodeInserted", function(){ 
        $(document).ready(function () {
            var ml = $('.side-body').data(),
                current_lang = $(".js-language-label").first().text(),
                current_translation = JSON.parse($('#{{$row->field}}_i18n').attr('value')),
                parse_translation = JSON.parse(eval('current_translation.'+current_lang));

            ml.multilingual.settings.editing = false

            // remove input from multilangual.js plugin
            for (let i = 0; i < ml.multilingual.transInputs.length; i++) {
                if(ml.multilingual.transInputs[i].id.substring(0,ml.multilingual.transInputs[i].id.indexOf('_')) == '{{$row->field}}'){
                    ml.multilingual.transInputs.splice(i, 1);
                }
            }

            console.log(current_lang);
            console.log(current_translation);
            console.log(parse_translation[0].key);

            $(".custom-parameters .row").each(function(i) {
                if(typeof(parse_translation[i]) != "undefined" && parse_translation[i] !== null) {
                    console.log(i);
                    $("input[name~='{{$row->field}}["+i+"][key]']").val(parse_translation[i].key);
                    $("input[name~='{{$row->field}}["+i+"][value]']").val(parse_translation[i].value);
                }
                
            });



            $(".custom-parameters input").change(function() {
                var id = $(this).parent().parent().attr('row-id')
                if($(this).is('#key')){
                    parse_translation[id].key = $(this).val();
                }else if($(this).is('#value')){
                    parse_translation[id].value = $(this).val();
                }
                console.log($(this).val());
                console.log(parse_translation);
                current_translation[current_lang] = JSON.stringify(parse_translation);
                $('#{{$row->field}}_i18n').attr('value', JSON.stringify(current_translation));
            });

        });

    });
    @endif

    
</script>


