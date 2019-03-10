<?php

namespace ExtendedBreadFormFields\ContentTypes;
use TCG\Voyager\Http\Controllers\ContentTypes\BaseType;
use Illuminate\Http\Request;

class KeyValueJsonContentType extends BaseType
{
 
    public function __construct(&$request)
    {
        dd($request->all());
    }

    /**
     * @return null|string
     */
    public function handle()
    {

        // dd($this->request->input());

        $value = $this->request->input($this->row->field);
        
        // dd($value);

        $new_parameters = array();
        foreach ($value as $key => $val) {
            if($value[$key]['key']){
                $new_parameters[] = $value[$key];
            }
        }
        
        return json_encode($new_parameters);
    }
}
