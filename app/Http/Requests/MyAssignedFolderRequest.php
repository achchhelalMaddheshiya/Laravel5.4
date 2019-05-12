<?php
namespace App\Http\Requests;

use App\FolderPermission;
use App\Http\Requests\Request;
use Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Factory;
// https://8thlight.com/blog/mike-knepper/2016/09/26/laravel-controllers-clean-with-form-requests.html
class MyAssignedFolderRequest extends FormRequest
{
    public function __construct(Factory $factory)
    {
        $name = 'is_folder_assigned_to_me';
        $test = function () {
            return $this->testValid();
        };
        $errorMessage = 'You dont have permissions to view this folder';
        $factory->extend($name, $test, $errorMessage);
    }
    public function testValid()
    {
        $count = FolderPermission::select(
            'folders.user_id as creator_id',
            'users.name as name',
            'users.slug as slug',
            'users.image as image'
        )
            ->join('folders', 'folders.id', '=', 'folder_permissions.folder_id')
            ->join('users', 'users.id', '=', 'folders.user_id')
            ->where('folders.status', 1)
            ->where('users.status', 1)
            ->where('users.primary_declaration', 1)
            ->where('users.guarantee_declaration', 1)
            ->where('folder_permissions.user_id', Auth::user()->id) // 
            ->groupBy('users.id')->count();
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
            // '*' => 'is_folder_assigned_to_me'
        ];
    }

    public function messages()
    {
        return [
           
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

