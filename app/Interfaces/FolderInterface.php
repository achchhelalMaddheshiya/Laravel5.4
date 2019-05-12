<?php

namespace App\Interfaces;
use App\Http\Requests\UserVaultRequest;
use App\Http\Requests\CreateFolderRequest;
use App\Http\Requests\AssignMemberRequest;
use App\Http\Requests\WriteFolderRequest;
use App\Http\Requests\LinksRequest;
use App\Http\Requests\DeleteRequest;
use App\Http\Requests\AssignedFolderRequest;
use App\Http\Requests\MyAssignedFolderRequest;

use Illuminate\Http\Request;


interface FolderInterface {
    
    public function myPersonalVault(Request $request);

    public function getVaultDetail(UserVaultRequest $request);

    public function createFolder(Request $request);

    public function assignMember(Request $request);

    public function writeFolder(WriteFolderRequest $request);

    public function getFolderData(LinksRequest $request);

    public function deleteFolderData(DeleteRequest $request);

    public function getFolderPermissions(Request $request);

    public function getFolderPermissionUsers(Request $request);

    // MyAssignedFolderRequest
    public function getMyAssignedFolders(Request $request);

    public function getFolderByUser(AssignedFolderRequest $request);

    public function getFolderDetail(LinksRequest $request);

}