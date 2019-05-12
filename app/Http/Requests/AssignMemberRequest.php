<?php
namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Auth;

class AssignMemberRequest extends FormRequest
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
        $id = Auth::user()->id;
        return [
            'folder_id' => 'required|numeric|exists:folders,id,user_id,'.$id,
            'user_id' => 'required|numeric|exists:users,id',
            'permission_id' => 'required|numeric|exists:folder_permissions,id'
        ];
    }

    public function messages()
    {
        return [
            'folder_id.required' => 'Folder id is required',
            'folder_id.exists' => 'You do have access to this folder',
            'folder_id.numeric' => 'Invalid folder id',
            'user_id.required' => 'User id is required',
            'user_id.exists' => 'Invalid User id',
            'user_id.numeric' => 'Invalid User id',
            'permission_id.required' => 'Permission id is required',
            'permission_id.exists' => 'Invalid Permission id',
            'permission_id.numeric' => 'Invalid Permission id'
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

