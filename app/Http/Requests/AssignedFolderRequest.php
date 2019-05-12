<?php
namespace App\Http\Requests;

use App\FolderPermission;
use App\Http\Requests\Request;
use Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Factory;

class AssignedFolderRequest extends FormRequest
{
    public function __construct(Factory $factory)
    {
        $name = 'is_valid_assignee';
        $test = function () {
            return $this->testValid();
        };
        $errorMessage = 'You dont have permissions for this action';
        $factory->extend($name, $test, $errorMessage);
    }
    public function testValid()
    {
        $count = FolderPermission::selectRaw('count(folders.user_id) as total')
            ->join('folders', 'folders.id', '=', 'folder_permissions.folder_id')
            ->join('users', 'users.id', '=', 'folders.user_id')
            ->where(function ($query) {
                $query->Where('folders.status', 1)
                    ->where('users.status', 1)
                   // ->where('users.primary_declaration', 1)
                   // ->where('users.guarantee_declaration', 1)
                    ->where('folders.user_id', request('user_id'))
                    ->where('folder_permissions.user_id', Auth::user()->id);
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
            'user_id' => 'required|numeric|exists:users,id|is_valid_assignee',
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => 'User id is required',
            'user_id.exists' => 'Invalid User id',
            'user_id.numeric' => 'Invalid User id',
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
