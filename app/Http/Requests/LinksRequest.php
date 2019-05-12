<?php
namespace App\Http\Requests;

use App\FolderPermission;
use App\FamilyMember;
use App\Http\Requests\Request;
use Auth;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Factory;

class LinksRequest extends FormRequest
{
    public function __construct(Factory $factory)
    {
        if (request('q') != '' && request('q') !== null && request('q') != 'undefined') {
            $name = 'is_folder_assigned';
            $test = function () {
                return $this->testValid();
            };
            $errorMessage = 'Error You dont have permissions for this action';
            $factory->extend($name, $test, $errorMessage);
        }
    }
    public function testValid()
    {
        //get my permission to view // all when creator of the folder is declared as dead for the family members
        $data = FolderPermission::select('id', 'folder_id', 'permission_id', 'user_id')
            ->wherehas(
                'folder', function ($q) {
                    $q->select('id', 'user_id', 'name', 'slug');
                })
            ->wherehas(
                'folder.creator', function ($q) {
                    // , 'primary_declaration' => 1, 'guarantee_declaration' => 1
                    $q->select('id', 'user_id', 'name', 'slug')->where(['status' => 1]);
                })
            ->with(['permission' => function ($q) {
                $q->select('id', 'name', 'slug');
            }, 'folder' => function ($q) {
                $q->select('id', 'user_id', 'name', 'slug');
            }, 'folder.creator' => function ($q) {
                $q->select('id', 'name', 'slug', 'primary_declaration', 'guarantee_declaration','status');
            }])
            ->where([
                'folder_id' => request('folder_id'),
                'user_id' => Auth::user()->id,
                'status' => 1,
            ])->get();

        if (isset($data) && !empty($data)) {
            $data = $data->toArray();
            if (!empty($data) && count($data) > 0) {
                return true;
            }  
           
            /*if ($data["permission"][0]["slug"] == 'all' || $data["permission"][0]["slug"] == 'view') {
                return true;
            } else {
                return false;
            }*/
        } else {
            return false;
        }
        /* $count = FolderPermission::where(["user_id" => Auth::user()->id, 'folder_id' => request('folder_id') ])->count();
    return ($count > 0) ? true : false;*/
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
        $id = Auth::user()->id;
        if (request('q') != '' && request('q') !== null && request('q') != 'undefined') {
            return [
                '*' => 'is_folder_assigned',
            ];
        } else {
            return [
                'folder_id' => 'required|numeric|exists:folders,id,user_id,' . $id,
            ];
        }
    }

    public function messages()
    {

        if (!empty(request('q')) && request('q') !== null && request('q') != 'undefined') {
            return [

            ];
        } else {
            return [
                'folder_id.required' => 'Folder id is required',
                'folder_id.numeric' => 'Invalid folder id',
                'folder_id.exists' => 'You have do not access for this folder',
            ];
        }
        /**/
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
    // getFolderData
    //Link Request
    // AssignedFolderRequest
    // getMyAssignedFolders
    // getFolderByUser
}
