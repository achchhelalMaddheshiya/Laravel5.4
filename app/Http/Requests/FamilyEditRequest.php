<?php
namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;


class FamilyEditRequest extends FormRequest
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

        return [
            'id' => 'required',
            'name' => 'required',
            'dob' => 'required',
            'relation' => 'required',
            'location' => 'required',
            'lng' => 'required',
            'lat' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'id.required' => 'Id is required',
            'name.required' => 'Name is required',
            'relation.required' => 'Relation is required',
            'location' => 'Location is required',
            'lng' => 'Longitude is required',
            'lat' => 'Latitude is required',
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