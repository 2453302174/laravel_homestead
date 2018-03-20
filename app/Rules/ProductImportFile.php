<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ProductImportFile implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $clientExt = $value->getClientOriginalExtension();
        if(!in_array($clientExt, array('xls', 'xlsx'))){
            return false;
        }

        /*
        $mime = $value->getMimeType();
        if(!in_array($mime, array('application/vnd.ms-office'))){
            return false;
        }
        $clientMime = $value->getClientMimeType();
        if(!in_array($clientMime, array('application/vnd.ms-excel'))){
            return false;
        }
        */
        
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '请上传excel格式（支持.xls | .xlsx）的报表文件';
    }
}
