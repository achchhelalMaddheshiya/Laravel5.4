<?php
namespace App\Http\Requests;

use App\FamilyMember;
use App\Http\Requests\Request;
use Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Factory;

class DeclareUserRequest extends FormRequest
{
    public function __construct(Factory $factory)
    {
        $name = 'is_valid_assignee';
        $test = function () {
            return $this->testValid();
        };
        $errorMessage = 'Invalid Code';
        $factory->extend($name, $test, $errorMessage);
    }
    public function testValid()
    {
        $count = FamilyMember::where(function ($query) {
                $query->where('invited_by', request('invited_by'))
                    ->where('user_id', Auth::user()->id)
                    ->where('code',  request('code'))
                     ->where('status',  1);
            })->count();
        return ($count > 0) ? true : false;
    }

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
            'row_id' => 'required|numeric|exists:family_members,id',
            'invited_by' => 'required|numeric|exists:users,id',
            'code' => 'required|is_valid_assignee',
        ];
    }

    public function messages()
    {
        return [
            'row_id.required' => 'Row id is required',
            'row_id.exists' => 'Invalid row id',
            'row_id.numeric' => 'Invalid row id',
            'invited_by.required' => 'Invited by id is required',
            'invited_by.exists' => 'Invalid invited by id',
            'invited_by.numeric' => 'Invalid invited by id',
            'code.required' => 'Code is required',
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

