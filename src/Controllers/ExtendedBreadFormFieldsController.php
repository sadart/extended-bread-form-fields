<?php

namespace ExtendedBreadFormFields\Controllers;

use Illuminate\Http\Request;
use TCG\Voyager\Http\Controllers\VoyagerBaseController;
use TCG\Voyager\Http\Controllers\Controller;
use TCG\Voyager\Facades\Voyager;

use ExtendedBreadFormFields\ContentTypes\MultipleImagesWithAttrsContentType;
use ExtendedBreadFormFields\ContentTypes\KeyValueJsonContentType;

class ExtendedBreadFormFieldsController extends VoyagerBaseController
{

    public function getContentBasedOnType(Request $request, $slug, $row, $options = null)
    {
        switch ($row->type) {
            case 'key-value_to_json':
                return (new KeyValueJsonContentType($request, $slug, $row, $options))->handle();
            case 'multiple_images_with_attrs':
                return (new MultipleImagesWithAttrsContentType($request, $slug, $row, $options))->handle();
            default:
                return Controller::getContentBasedOnType($request, $slug, $row, $options);
        }
    }


    public function insertUpdateData($request, $slug, $rows, $data)
    {
        foreach ($rows as $row) {
            if ($row->type == 'multiple_images_with_attrs'){
                $is_multiple_image_attrs = 1;
                $fieldName = $row->field;
                $ex_files = json_decode($data->{$row->field}, true);
                $request->except("{$row->field}");
            }
        }

        $new_data = VoyagerBaseController::insertUpdateData($request, $slug, $rows, $data);
        
        if(isset($is_multiple_image_attrs)){
            foreach ($rows as $row) {
                $content = $new_data->$fieldName;
                if ($row->type == 'multiple_images_with_attrs' && !is_null($content) && $ex_files != json_decode($content,1)) {
                    if (isset($data->{$row->field})) {
                        if (!is_null($ex_files)) {
                            $content = json_encode(array_merge($ex_files, json_decode($content,1)));
                        }
                    }
                    $new_content = $content;
                }
            }
            
            if(isset($new_content)) $content = json_decode($new_content,1);
                else $content = json_decode($content,1);
            
            if(isset($content)){
                foreach ($content as $i => $value) {
                    if(isset($request->{$fieldName.'_ext'}[$i])){
                        $end_content[] = array_merge($content[$i], $request->{$fieldName.'_ext'}[$i]);
                    }else{
                        $end_content[] = $content[$i];
                    }
                }
                $data->{$fieldName} = json_encode($end_content);
            }
            
            $data->save();
                    
            return $data;
        } else {
            return $new_data;
        }
    }
  
    public function remove_media(Request $request)
    {
        if($request->get('multiple_ext')){
            try {
                // GET THE SLUG, ex. 'posts', 'pages', etc.
                $slug = $request->get('slug');
    
                // GET image name
                $image = $request->get('image');
    
                // GET record id
                $id = $request->get('id');
    
                // GET field name
                $field = $request->get('field');
    
                // GET THE DataType based on the slug
                $dataType = Voyager::model('DataType')->where('slug', '=', $slug)->first();
    
                // Check permission
                // Voyager::canOrFail('delete_'.$dataType->name);
                // Voyager::canOrFail('delete_'.$dataType->name);
                auth()->user()->can('delete', app($dataType->model_name));
    
                // Load model and find record
                $model = app($dataType->model_name);

                $data = $model::find([$id])->first();
    
                // Check if field exists
                if (!isset($data->{$field})) {
                    throw new Exception(__('voyager::generic.field_does_not_exist'), 400);
                }
    
                // Check if valid json
                if (is_null(@json_decode($data->{$field}))) {
                    throw new Exception(__('voyager::json.invalid'), 500);
                }
                
                // Decode field value
                $fieldData = @json_decode($data->{$field}, true);
                foreach ($fieldData as $i => $single) {
                    // Check if image exists in array
                    if(in_array($image,array_values($single)))
                        $founded = $i;
                }
                if(!isset($founded))
                    throw new Exception(__('voyager::media.image_does_not_exist'), 400);
                
                // Remove image from array
                unset($fieldData[$founded]);
    
                // print_r($field);
                // Generate json and update field
                $data->{$field} = json_encode($fieldData);
                $data->save();
    
                return response()->json([
                   'data' => [
                       'status'  => 200,
                       'message' => __('voyager::media.image_removed'),
                   ],
                ]);
            } catch (Exception $e) {
                $code = 500;
                $message = __('voyager::generic.internal_error');
    
                if ($e->getCode()) {
                    $code = $e->getCode();
                }
    
                if ($e->getMessage()) {
                    $message = $e->getMessage();
                }
    
                return response()->json([
                    'data' => [
                        'status'  => $code,
                        'message' => $message,
                    ],
                ], $code);
            }
        } else{
            VoyagerBaseController::remove_media($request);
        }
    }    
}
