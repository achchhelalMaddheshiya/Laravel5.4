<?php
namespace App\Http\Traits;

use App\FamilyMember;
use App\FamilyType;
use Illuminate\Support\Facades\Auth;

trait FamilyTrait
{

    /*
     * function to fetch allowed members count in selected family type
     */
    public function getAllowedMembers($id, $requested_data)
    {
        $familyType = FamilyType::select('members_count', 'slug')->where('id', $id)->first();
        if ($familyType->slug == 'primary' || $familyType->slug == 'guarantee') {
            return 1;
        } else {
            return $requested_data["data"]["packages"]["details"]["members_count_limit"];
        }
    }

    /*
     * function to fetch users members added for selected family type
     */
    public function getUsersAddedMembers($id)
    {
        $userCount = FamilyMember::where(['family_id' => $id, 'invited_by' => Auth::user()->id, 'status' => 1])->count();
        return $userCount;
    }

    /*
     * function to check is invitor already member with user or not
     */
    public function checkAlreadyMember($email)
    {
        $isAlready = FamilyMember::where(['invited_by' => Auth::user()->id, 'email' => $email, 'status' => [0, 1]])->count();
        return $isAlready;
    }

    /*
     * function to fetch users members added for selected family type
     */
    public function getInvitorAddedMembers($family_id, $invitor_id)
    {
        $userCount = FamilyMember::where(['family_id' => $family_id, 'invited_by' => $invitor_id, 'status' => 1])->count();
        return $userCount;
    }

}
