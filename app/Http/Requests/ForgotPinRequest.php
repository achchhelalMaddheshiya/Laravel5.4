<?php
namespace App\Http\Requests;

use App\Http\Requests\Request;
use Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class ForgotPinRequest extends FormRequest
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
            'dob' => 'required',
            'email' => 'required',
            'name' => 'required',
            'row_id' => 'required|numeric|exists:family_members,id,user_id,'.$id,
        ];
    }

    public function messages()
    {
        return [
            'row_id.required' => 'Row id is required',
            'row_id.exists' => 'You do have access to this',
            'row_id.numeric' => 'Invalid row id',
            'name.required' => 'Name is required',
            'email.required' => 'Email is required',
            'dob.required' => 'Dob is required',
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
