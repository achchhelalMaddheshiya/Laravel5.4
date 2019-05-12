<?php
namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Auth;
class UpdateFolderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $uid = Auth::user()->id;
       
        return [
            'id' => 'required|numeric|exists:folders,id',
            'parent_id' => 'required|numeric|exists:folders,parent_id,user_id,'.$uid, // check parent id matched with user id 
            'name' => 'required|unique:folders,name,'.request('id').'id'// on edit check name is unique or not, if not then update
        ];
    }
   
    public function messages()
    {
        return [
            'id.required' => 'Folder row id is required',
            'id.exists' => 'Invalid folder id',
            'id.numeric' => 'Invalid folder id',
            'parent_id.required' => 'Vault id required',
            'parent_id.exists' => 'You do have access to this folder',
            'parent_id.numeric' => 'Invalid parent id',
            'name.required' => 'Name is required',
            'name.unique' => 'You have already created folder with this name'
        ];
    }

     /**
     * [failedValidation [Overriding the event validator for custom error response]]
     * @param  Validator $validator [description]
     * @return [object][object of various validation errors]
     */
    public function failedValidation(Validator $validator)
    {
        $data_error = [];
        $error = $validator->errors()->all(); #if validation fail print error messages
      
        foreach ($error as $key => $errors):
            $data_error['status'] = 400;
            $data_error['message'] = $errors;
        endforeach;
        //write your bussiness logic here otherwise it will give same old JSON response
        throw new HttpResponseException(response()->json($data_error, 400));

    }
}
