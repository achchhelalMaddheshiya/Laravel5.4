<?php

namespace App\Interfaces;

use App\Http\Requests\DeleteMemberRequest;
use App\Http\Requests\FamilyCreateRequest;
use App\Http\Requests\FamilyEditRequest;
use App\Http\Requests\FamilyProfileGetRequest;
use App\Http\Requests\FamilyStatusRequest;
use Illuminate\Http\Request;

interface FamilyInterface
{

    public function getFamilyTypes(Request $request);

    public function createFamilyProfile(FamilyCreateRequest $request);

    public function changeFamilyRequestStatus(FamilyStatusRequest $request);

    public function getFamilyProfile(FamilyProfileGetRequest $request);

    public function editFamilyProfile(FamilyEditRequest $request);
    
    public function getMyFamilyMembers(Request $request);

    public function deleteMember(DeleteMemberRequest $request);

    public function getUserFolderWithPermissions(Request $request);
    
    public function swapUser(Request $request);
}