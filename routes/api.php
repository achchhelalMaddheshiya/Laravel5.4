<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "API" middleware group. Enjoy building your API!
|
 */

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
return $request->user();
});*/

Route::group(['middleware' => 'cors'], function () {

    // Routes For UsersController Without Login
    Route::post('login', 'API\UsersController@login');
    Route::post('signUp', 'API\UsersController@signUp');
    Route::post('verify', 'API\UsersController@verify');
    Route::post('resendVerification', 'API\UsersController@resendVerification');
    Route::post('validateForgotExpiry', 'API\UsersController@validateForgotPasswordExpiry');
    Route::post('forgotPassword', 'API\UsersController@forgotPassword');
    Route::post('resetPassword', 'API\UsersController@resetPassword');

    Route::get('getAds', 'API\AdsController@getAds');
    Route::post('saveAdStats', 'API\AdsController@saveAdStats');

    //Routes For PagesController
    Route::post('contactUs', 'API\PagesController@contactUs');
    Route::group(['middleware' => [
        'auth:api',
        'user_data',
    ]], function () {
        // Routes For UsersController
        Route::get('getUser', 'API\UsersController@getUser');
        Route::get('logout', 'API\UsersController@logout');
        Route::post('uploadProfileImage', 'API\UsersController@uploadProfileImage');
        Route::put('changePassword', 'API\UsersController@changePassword');
        Route::get('userProfile', 'API\UsersController@getPersonalProfile');
        Route::put('updateProfile', 'API\UsersController@updatePersonalProfile');
        Route::get('getPackages', 'API\UsersController@getPackages');
        Route::post('changeEmail', 'API\UsersController@changeEmail');
        Route::post('verify-change-email', 'API\UsersController@verifyChangeEmail');

        // Routes For NotificationController
        Route::post('readNotification', 'API\NotificationController@readNotification');
        Route::get('getNotifications', 'API\NotificationController@getNotifications');
        Route::get('getMyRequests', 'API\NotificationController@getMyRequests');

        // Routes For CheckoutController
        Route::post('charge', 'API\CheckoutController@charge');
        Route::post('unsubscribePayment', 'API\CheckoutController@unsubscribePayment');
        
        // Routes For FamilyController
        Route::get('getFamilyTypes', 'API\FamilyController@getFamilyTypes');
        Route::get('getFamilyRelations', 'API\FamilyController@getFamilyRelations');
        Route::post('createFamily', 'API\FamilyController@createFamilyProfile');
        Route::post('changeRequestStatus', 'API\FamilyController@changeFamilyRequestStatus');
        Route::get('getFamilyProfile', 'API\FamilyController@getFamilyProfile');
        Route::put('editFamilyProfile', 'API\FamilyController@editFamilyProfile');
        Route::get('getMyFamilyMembers', 'API\FamilyController@getMyFamilyMembers');
        Route::post('deleteMember', 'API\FamilyController@deleteMember');
        Route::get('getUserFolderWithPermissions', 'API\FamilyController@getUserFolderWithPermissions');
        Route::put('swapUser', 'API\FamilyController@swapUser');

        // Routes for Folder Controller
        Route::get('myPersonalVault', 'API\FoldersController@myPersonalVault');
        Route::get('getVaultDetail', 'API\FoldersController@getVaultDetail');
        Route::post('createFolder', 'API\FoldersController@createFolder');
        Route::put('updateFolder', 'API\FoldersController@updateFolder');
        Route::post('assignMember', 'API\FoldersController@assignMember');
        Route::get('download', 'API\FoldersController@download');
        Route::get('getFolderDetail', 'API\FoldersController@getFolderDetail');
        Route::get('getFolderPermissions', 'API\FoldersController@getFolderPermissions');
        Route::get('getFolderPermissionUsers', 'API\FoldersController@getFolderPermissionUsers');
        Route::get('getMyAssignedFolders', 'API\FoldersController@getMyAssignedFolders');
        Route::get('getFolderByUser', 'API\FoldersController@getFolderByUser');

        //links page route
        Route::post('writeFolder', 'API\FoldersController@writeFolder');
        Route::get('getFolderData', 'API\FoldersController@getFolderData');
        Route::post('deleteFolderData', 'API\FoldersController@deleteFolderData');

        // Routes for upload Controller
        Route::post('upload', 'API\UploadController@Upload');

         // Routes for NomineeController Controller
        Route::get('getNominee', 'API\NomineeController@getNominee');
        Route::post('declareUser', 'API\NomineeController@declareUser');
        Route::post('forgotPin', 'API\NomineeController@forgotPin');

        //Route for ads controller 
        Route::post('createAd', 'API\AdsController@createAd');
       
    });
});

Route::post('stripe/webhook', 'API\WebhookController@handleWebhook');
