<?php


use App\Http\Controllers\ScreenShotController;
use Spatie\Browsershot\Browsershot;

Auth::routes();

Route::group(['middleware' => ['auth']], function () {
    Route::middleware(['password_expired', 'single_session'])->group(function () {
        Route::middleware(['maintenance_mode'])->group(function () {
            Route::get('/', 'Dashboard\DashboardController@index')->name('home');
            Route::post('/', 'Dashboard\DashboardController@index');
            Route::get('/preview_ifc', 'Dashboard\DashboardController@preview_ifc');
            Route::get('/preview_json_ifc', 'Dashboard\DashboardController@preview_json_ifc');
            Route::get('/preview_ifa', 'Dashboard\DashboardController@preview_ifa');
            Route::get('/preview_json_ifa', 'Dashboard\DashboardController@preview_json_ifa');
            Route::get('/preview_ifu', 'Dashboard\DashboardController@preview_ifu');
            Route::get('/preview_json_ifu', 'Dashboard\DashboardController@preview_json_ifu');
            Route::get('/preview_afc', 'Dashboard\DashboardController@preview_afc');
            Route::get('/preview_json_afc', 'Dashboard\DashboardController@preview_json_afc');
            
            
            Route::get('/incoming/auto-revision/{document_id}/{issue_status_id}', [IncomingController::class, 'getAutoRevisionAjax']);
            
            //Route::get('/incoming/auto-revision/{document_id}/{issue_status_id}', [IncomingController::class, 'getAutoRevisionAjax']);
            
            
           // Route::get('/vendor_outgoing/edit/{id}', 'Transmittal\IncomingController@edit');
            
            /* ------- transmital company ---- */
            
// Transmittal → Incoming Company
/*
Route::group(['prefix' => 'incoming_company'], function () {
    Route::get('/index', 'Transmittal\IncomingController@incomingCompanyIndex')
        ->name('incoming_company.index');
    Route::post('/index', 'Transmittal\IncomingController@incomingCompanyIndex');

    Route::get('/assignment/{id}', 'Transmittal\IncomingController@incomingCompanyAssignment')
        ->name('incoming_company.assignment');

    Route::get('/transmittal_detail/{id}', 'Transmittal\IncomingController@incomingCompanyTransmittalDetail')
        ->name('incoming_company.transmittal_detail');
});
*/



// routes/web.php
Route::group(['prefix' => 'incoming_company'], function () {
    Route::get('/index', 'Transmittal\IncomingController@incomingCompanyIndex')
        ->name('incoming_company.index');

    // Halaman Assignment (Incoming Company)
    Route::get('/assignment/{document_id}', 'Transmittal\IncomingController@showIncomingAssignment')
        ->name('incoming_company.assignment');

    // Halaman Incoming Company List
    Route::get('/incoming-company-list', 'Transmittal\IncomingController@incomingCompanyList')
        ->name('incoming_company.incoming_company_list');

    // Simpan Assignment (Incoming Company)
    Route::post('/assignment/{document_id}', 'Transmittal\IncomingController@storeIncomingAssignment')
        ->name('incoming_company.assignment.store');
        
    // External flow routes
    Route::post('/external/complete/{document_id}', 'Transmittal\IncomingController@completeExternalFlow')
        ->name('incoming_company.external.complete');
        
    // Delete user assignment
    Route::get('/assignment/delete-user/{comment_id}', 'Transmittal\IncomingController@deleteAssignmentUser')
        ->name('incoming_company.assignment.delete_user');

    // Detail Transmittal
    Route::get('/transmittal_detail/{document_id}', 'Transmittal\IncomingController@incomingCompanyTransmittalDetail')
        ->name('incoming_company.transmittal_detail');
        
        

     

         
         



        
        
        
            Route::get('/comment/{document_id}', 
        'Transmittal\IncomingController@commentCompany')
        ->name('incoming_company.comment');

    Route::post('/comment/save', 
        'Transmittal\IncomingController@saveCommentCompany')
        ->name('incoming_company.comment.save');
        
    // Download attachment untuk company comment
    Route::get('/comment/download/{comment_id}', 'Transmittal\IncomingController@downloadAttachmentCompany')
        ->name('incoming_company.comment.download');
        
    // View document attachment (untuk tombol di form utama)
    Route::get('/document/view/{document_id}', 'Transmittal\IncomingController@viewDocumentAttachment')
        ->name('incoming_company.document.view');
        
    // IDC External workflow
    Route::get('/idc-external-list', 'Transmittal\IncomingController@idcExternalList')
        ->name('incoming_company.idc_external.list');
        
    Route::get('/idc-external/{document_id}', 'Transmittal\IncomingController@idcExternal')
        ->name('incoming_company.idc_external');
        
    Route::post('/idc-external/{document_id}', 'Transmittal\IncomingController@saveIdcExternal')
        ->name('incoming_company.idc_external.save');
        
    // quick approve (marks backdoor as done so it disappears from the list)
    Route::post('/idc-external/approve/{document_id}', 'Transmittal\IncomingController@approveIdcExternal')
        ->name('incoming_company.idc_external.approve');
        
});

Route::prefix('comment_company')->group(function(){

Route::get('/list',
'Transmittal\IncomingController@commentCompanyList')
->name('comment_company.list');

Route::get('/{document_id}',
'Transmittal\IncomingController@commentCompany')
->name('comment_company.comment');

Route::post('/save',
'Transmittal\IncomingController@saveCommentCompany')
->name('comment_company.save');

});



            /* ----------
            User
            ----------------------- */
            Route::group(['prefix' => 'user'], function () {
                Route::get('/index', 'UserManagement\UserController@index');
                Route::post('/index', 'UserManagement\UserController@index');
                Route::get('/add', 'UserManagement\UserController@add');
                Route::post('/save', 'UserManagement\UserController@save');
                Route::get('/edit/{id}', 'UserManagement\UserController@edit');
                Route::put('/update', 'UserManagement\UserController@update');
                Route::get('/delete/{id}', 'UserManagement\UserController@delete');
                Route::delete('/remove', 'UserManagement\UserController@remove');
                Route::get('/changepassword', 'UserManagement\UserController@changepassword');
                Route::post('/updatepassword', 'UserManagement\UserController@updatepassword');
                Route::get('/unfilter', 'UserManagement\UserController@unfilter');
                Route::get('/reset/{id}', 'UserManagement\UserController@reset');
                Route::put('/reset_password', 'UserManagement\UserController@reset_password');
                Route::get('/privilege/{id}', 'UserManagement\UserController@privilege');
                Route::post('/update_privilege', 'UserManagement\UserController@update_privilege');
                Route::get('/duplicate/{id}', 'UserManagement\UserController@duplicate');
                Route::post('/create_duplicate', 'UserManagement\UserController@create_duplicate');
                Route::get('/preview', 'UserManagement\UserController@preview');
                Route::get('/preview_json', 'UserManagement\UserController@preview_json');
            });
            Route::group(['prefix' => 'incoming'], function () {
                Route::get('/index', 'Transmittal\IncomingController@index');
                Route::post('/index', 'Transmittal\IncomingController@index');
                Route::get('/add', 'Transmittal\IncomingController@add');
                Route::post('/save', 'Transmittal\IncomingController@save');
                Route::post('/attach_item', 'Transmittal\IncomingController@attach_item');
                Route::get('/delete_item/{id}', 'Transmittal\IncomingController@delete_item');
                Route::get('/unfilter', 'Transmittal\IncomingController@unfilter');
                Route::get('/detail/{id}', 'Transmittal\IncomingController@detail');
                Route::get('/edit/{id}', 'Transmittal\IncomingController@edit');
                Route::post('/update', 'Transmittal\IncomingController@update');
                Route::get('/delete_receipt/{id}', 'Transmittal\IncomingController@delete_receipt');
                Route::get('/report', 'Transmittal\IncomingController@report');
                Route::post('/report_result', 'Transmittal\IncomingController@report_result');
                Route::get('/report_summary_json/{params}', 'Transmittal\IncomingController@report_summary_json');
                Route::get('/report_detail_json/{params}', 'Transmittal\IncomingController@report_detail_json');
                Route::get('/approve/{id}', 'Transmittal\IncomingController@approve');
                Route::post('/save_approve', 'Transmittal\IncomingController@save_approve');
                
                
                Route::get('/issue-status/document-status/{id}', [App\Http\Controllers\Transmittal\IncomingController::class, 'getDocumentStatusByIssue'])
        ->name('issue_status.document_status');
                

                Route::get('/issue_status/{id?}', ['as' => 'issue_status.document_status', 'uses' => 'Transmittal\IncomingController@get_document_status']);
                Route::get('/add_idc', 'Transmittal\IncomingController@add_idc');
                Route::post('/save_idc', 'Transmittal\IncomingController@save_idc');
                Route::post('/attach_item_idc', 'Transmittal\IncomingController@attach_item_idc');
                Route::get('/delete_item_idc/{id}', 'Transmittal\IncomingController@delete_item_idc');
            });
            Route::group(['prefix' => 'outgoing'], function () {
                Route::get('/index', 'Transmittal\OutgoingController@index');
                Route::post('/index', 'Transmittal\OutgoingController@index');
                Route::get('/unfilter', 'Transmittal\OutgoingController@unfilter');
                Route::get('/add', 'Transmittal\OutgoingController@add');
                Route::post('/save', 'Transmittal\OutgoingController@save');
                Route::get('/edit/{id}', 'Transmittal\OutgoingController@edit');
                Route::post('/update', 'Transmittal\OutgoingController@update');
                Route::get('/editdetail/{id}', 'Transmittal\OutgoingController@editdetail');
                Route::post('/updatedetail', 'Transmittal\OutgoingController@updatedetail');
                Route::post('/attachdocument', 'Transmittal\OutgoingController@attachdocument');
                Route::get('/deletedocument/{id}/{idheader}', 'Transmittal\OutgoingController@deletedocument');
                Route::get('/detail/{id}', 'Transmittal\OutgoingController@detail');
                Route::get('/resend/{id}', 'Transmittal\OutgoingController@resend');
                Route::post('/prosesresend', 'Transmittal\OutgoingController@prosesresend');
                Route::get('/vendorproject/{id?}', ['as' => 'vendor.project', 'uses' => 'Transmittal\OutgoingController@vendorproject']);

                Route::get('/report', 'Transmittal\OutgoingController@report');
                Route::post('/report_result', 'Transmittal\OutgoingController@report_result');
                Route::get('/report_summary_json/{params}', 'Transmittal\OutgoingController@report_summary_json');
                Route::get('/report_detail_json/{params}', 'Transmittal\OutgoingController@report_detail_json');
            });
            Route::group(['prefix' => 'vendor_incoming'], function () {
                Route::get('/index', 'Transmittal\OutgoingController@index');
                Route::post('/index', 'Transmittal\OutgoingController@index');
                Route::get('/unfilter', 'Transmittal\OutgoingController@unfilter');
                Route::get('/detail/{id}', 'Transmittal\OutgoingController@detail');
            });
            // Route::group(['prefix' => 'vendor_outgoing'], function () {
            //     Route::get('/index', 'Transmittal\IncomingController@index');
            //     Route::post('/index', 'Transmittal\IncomingController@index');
            //     Route::get('/unfilter', 'Transmittal\IncomingController@unfilter');
            //     Route::get('/detail/{id}', 'Transmittal\IncomingController@detail');

            //     Route::get('/add', 'Transmittal\IncomingController@add');
            //     Route::post('/save', 'Transmittal\IncomingController@save');
            //     Route::post('/attach_item', 'Transmittal\IncomingController@attach_item');
            //     Route::get('/delete_item/{id}', 'Transmittal\IncomingController@delete_item');

            //     Route::get('/edit/{id}', 'Transmittal\IncomingController@edit');
            //     Route::post('/update', 'Transmittal\IncomingController@update');
            //     Route::get('/project/{id?}', ['as' => 'project.vendor', 'uses' => 'Transmittal\IncomingController@vendorproject']);
            // });
            
            
Route::group(['prefix' => 'vendor_outgoing'], function () {

    Route::get('/index', 'Transmittal\IncomingController@index');
    Route::post('/index', 'Transmittal\IncomingController@index');

    Route::get('/unfilter', 'Transmittal\IncomingController@unfilter');

    Route::get('/detail/{id}', 'Transmittal\IncomingController@detail');

    // ADD + EDIT DRAFT
    Route::get('/add/{id?}', 'Transmittal\IncomingController@add');

    Route::post('/save', 'Transmittal\IncomingController@save');

    Route::post('/attach_item', 'Transmittal\IncomingController@attach_item');

    Route::get('/delete_item/{id}', 'Transmittal\IncomingController@delete_item');

    Route::get('/edit/{id}', 'Transmittal\IncomingController@edit');

    Route::post('/update', 'Transmittal\IncomingController@update');

    Route::get('/project/{id?}', [
        'as' => 'project.vendor',
        'uses' => 'Transmittal\IncomingController@vendorproject'
    ]);

});            
            
            
            Route::group(['prefix' => 'project'], function () {
                Route::get('/index', 'Project\ProjectController@index');
                Route::post('/index', 'Project\ProjectController@index');
                Route::get('/unfilter', 'Project\ProjectController@unfilter');
                Route::get('/add', 'Project\ProjectController@add');
                Route::post('/save', 'Project\ProjectController@save');
                Route::get('/edit/{id}', 'Project\ProjectController@edit');
                Route::post('/update', 'Project\ProjectController@update');
                Route::get('/delete/{id}', 'Project\ProjectController@delete');
                Route::post('/remove', 'Project\ProjectController@remove');
            });

            Route::group(['prefix' => 'document'], function () {
                Route::get('/index', 'Document\DocumentController@index');
                Route::post('/index', 'Document\DocumentController@index');
                Route::get('/unfilter', 'Document\DocumentController@unfilter');
                Route::get('/add', 'Document\DocumentController@add');
                Route::post('/save', 'Document\DocumentController@save');
                Route::get('/edit/{id}', 'Document\DocumentController@edit');
                Route::post('/update', 'Document\DocumentController@update');
                Route::get('/delete/{id}', 'Document\DocumentController@delete');
                Route::post('/remove', 'Document\DocumentController@remove');
                Route::post('/save_user_temp', 'Document\DocumentController@save_user_temp');
                Route::get('/delete_user_temp/{id}', 'Document\DocumentController@delete_user_temp');
                Route::get('/assignment/{id}', 'Document\DocumentController@assignment');
                Route::get('/reset_assignment/{doc_id}/{assingnment_id}/{user_id}', 'Document\DocumentController@reset_assignment');
                Route::post('/update_assignment', 'Document\DocumentController@update_assignment');
                Route::post('/save_clone_comment_temp', 'Document\DocumentController@save_clone_comment_temp');
                Route::get('/delete_clone_comment_temp/{id}', 'Document\DocumentController@delete_clone_comment_temp');
                Route::get('/detail/{id}', 'Document\DocumentController@detail');


                // BARU DITAMBAH DI SINI
                Route::get('/export-detail/{encodedId}', 'Document\DocumentController@exportDetail');
                
                Route::post('/export-multiple', 'Document\DocumentController@exportMultiple');

                
                
                Route::get('/change_approval/{id}', 'Document\DocumentController@change_approval');
                Route::post('/update_approval', 'Document\DocumentController@update_approval');
                Route::get('/change_deadline/{id}', 'Document\DocumentController@change_deadline');
                Route::post('/update_deadline', 'Document\DocumentController@update_deadline');

                Route::get('/upload_vdrl', 'Document\DocumentController@upload_vdrl');
                Route::post('/temp_vdrl', 'Document\DocumentController@temp_vdrl');
                Route::get('/download_current_vdrl', 'Document\DocumentController@download_current_vdrl');
                Route::get('/view_temp/{id}', 'Document\DocumentController@view_temp');

                Route::post('/save_vdrl', 'Document\DocumentController@save_vdrl');


                Route::get('/report', 'Document\DocumentController@report');
                Route::post('/report_result', 'Document\DocumentController@report_result');
                Route::get('/report_summary_json/{params}', 'Document\DocumentController@report_summary_json');
                Route::get('/report_detail_json/{params}', 'Document\DocumentController@report_detail_json');

                Route::get('/change_document_status/{id}', 'Document\DocumentController@change_document_status');
                Route::post('/update_document_status', 'Document\DocumentController@update_document_status');
            });
            Route::group(['prefix' => 'discipline'], function () {
                Route::get('/index', 'Reference\DisciplineController@index');
                Route::post('/index', 'Reference\DisciplineController@index');
                Route::get('/add', 'Reference\DisciplineController@add');
                Route::post('/save', 'Reference\DisciplineController@save');
                Route::get('/edit/{id}', 'Reference\DisciplineController@edit');
                Route::put('/update', 'Reference\DisciplineController@update');
                Route::get('/unfilter', 'Reference\DisciplineController@unfilter');
            });
            Route::group(['prefix' => 'document_type'], function () {
                Route::get('/index', 'Reference\DocumentTypeController@index');
                Route::post('/index', 'Reference\DocumentTypeController@index');
                Route::get('/add', 'Reference\DocumentTypeController@add');
                Route::post('/save', 'Reference\DocumentTypeController@save');
                Route::get('/edit/{id}', 'Reference\DocumentTypeController@edit');
                Route::put('/update', 'Reference\DocumentTypeController@update');
                Route::get('/unfilter', 'Reference\DocumentTypeController@unfilter');
            });
            Route::group(['prefix' => 'company'], function () {
                Route::get('/index', 'Reference\CompanyController@index');
                Route::post('/index', 'Reference\CompanyController@index');
                Route::get('/add', 'Reference\CompanyController@add');
                Route::post('/save', 'Reference\CompanyController@save');
                Route::get('/edit/{id}', 'Reference\CompanyController@edit');
                Route::put('/update', 'Reference\CompanyController@update');
                Route::get('/unfilter', 'Reference\CompanyController@unfilter');
            });
            Route::group(['prefix' => 'vendor'], function () {
                Route::get('/index', 'Reference\VendorController@index');
                Route::post('/index', 'Reference\VendorController@index');
                Route::get('/add', 'Reference\VendorController@add');
                Route::post('/save', 'Reference\VendorController@save');
                Route::get('/edit/{id}', 'Reference\VendorController@edit');
                Route::put('/update', 'Reference\VendorController@update');
                Route::get('/unfilter', 'Reference\VendorController@unfilter');
            });
            Route::group(['prefix' => 'document_status'], function () {
                Route::get('/index', 'Reference\DocumentStatusController@index');
                Route::post('/index', 'Reference\DocumentStatusController@index');
                Route::get('/add', 'Reference\DocumentStatusController@add');
                Route::post('/save', 'Reference\DocumentStatusController@save');
                Route::get('/edit/{id}', 'Reference\DocumentStatusController@edit');
                Route::put('/update', 'Reference\DocumentStatusController@update');
                Route::get('/unfilter', 'Reference\DocumentStatusController@unfilter');
            });
            Route::group(['prefix' => 'issue_status'], function () {
                Route::get('/index', 'Reference\IssueStatusController@index');
                Route::post('/index', 'Reference\IssueStatusController@index');
                Route::get('/add', 'Reference\IssueStatusController@add');
                Route::post('/save', 'Reference\IssueStatusController@save');
                Route::get('/edit/{id}', 'Reference\IssueStatusController@edit');
                Route::put('/update', 'Reference\IssueStatusController@update');
                Route::get('/unfilter', 'Reference\IssueStatusController@unfilter');
            });
            Route::group(['prefix' => 'return_status'], function () {
                Route::get('/index', 'Reference\ReturnStatusController@index');
                Route::post('/index', 'Reference\ReturnStatusController@index');
                Route::get('/add', 'Reference\ReturnStatusController@add');
                Route::post('/save', 'Reference\ReturnStatusController@save');
                Route::get('/edit/{id}', 'Reference\ReturnStatusController@edit');
                Route::put('/update', 'Reference\ReturnStatusController@update');
                Route::get('/unfilter', 'Reference\ReturnStatusController@unfilter');
            });
            Route::group(['prefix' => 'department'], function () {
                Route::get('/index', 'Reference\DepartmentController@index');
                Route::post('/index', 'Reference\DepartmentController@index');
                Route::get('/add', 'Reference\DepartmentController@add');
                Route::post('/save', 'Reference\DepartmentController@save');
                Route::get('/edit/{id}', 'Reference\DepartmentController@edit');
                Route::put('/update', 'Reference\DepartmentController@update');
                Route::get('/unfilter', 'Reference\DepartmentController@unfilter');
            });
            Route::group(['prefix' => 'position'], function () {
                Route::get('/index', 'Reference\PositionController@index');
                Route::post('/index', 'Reference\PositionController@index');
                Route::get('/add', 'Reference\PositionController@add');
                Route::post('/save', 'Reference\PositionController@save');
                Route::get('/edit/{id}', 'Reference\PositionController@edit');
                Route::put('/update', 'Reference\PositionController@update');
                Route::get('/unfilter', 'Reference\PositionController@unfilter');
            });

            Route::group(['prefix' => 'area'], function () {
                Route::get('/index', 'Reference\AreaController@index');
                Route::post('/index', 'Reference\AreaController@index');
                Route::get('/add', 'Reference\AreaController@add');
                Route::post('/save', 'Reference\AreaController@save');
                Route::get('/edit/{id}', 'Reference\AreaController@edit');
                Route::put('/update', 'Reference\AreaController@update');
                Route::get('/unfilter', 'Reference\AreaController@unfilter');
            });
            Route::group(['prefix' => 'extention'], function () {
                Route::get('/index', 'Reference\ExtentionController@index');
                Route::post('/index', 'Reference\ExtentionController@index');
                Route::get('/add', 'Reference\ExtentionController@add');
                Route::post('/save', 'Reference\ExtentionController@save');
                Route::get('/edit/{id}', 'Reference\ExtentionController@edit');
                Route::put('/update', 'Reference\ExtentionController@update');
                Route::get('/unfilter', 'Reference\ExtentionController@unfilter');
            });
            Route::group(['prefix' => 'country'], function () {
                Route::get('/index', 'Reference\CountryController@index');
                Route::post('/index', 'Reference\CountryController@index');
                Route::get('/add', 'Reference\CountryController@add');
                Route::post('/save', 'Reference\CountryController@save');
                Route::get('/edit/{id}', 'Reference\CountryController@edit');
                Route::put('/update', 'Reference\CountryController@update');
                Route::get('/unfilter', 'Reference\CountryController@unfilter');
            });

            Route::group(['prefix' => 'comments'], function () {
                Route::get('/index', 'Comments\CommentsController@index');
                Route::post('/index', 'Comments\CommentsController@index');
                Route::get('/unfilter', 'Comments\CommentsController@unfilter');
                Route::get('/addcomments/{id}', 'Comments\CommentsController@addcomments');
                Route::post('/save', 'Comments\CommentsController@save');
                Route::get('/download', 'Comments\CommentsController@download');
                Route::get('/download_attachment/{id}', 'Comments\CommentsController@download_attachment');
                Route::get('/activate/{params}', 'Comments\CommentsController@activate');
            });  

            Route::group(['prefix' => 'comments_idc'], function () {
                Route::get('/index', 'Comments\CommentsController@index_idc');
                Route::post('/index', 'Comments\CommentsController@index_idc');
                Route::get('/unfilter', 'Comments\CommentsController@unfilter_idc');
                Route::get('/addcomments/{id}', 'Comments\CommentsController@addcomments_idc');
                Route::post('/save', 'Comments\CommentsController@save');
                Route::get('/download', 'Comments\CommentsController@download');
                Route::get('/download_attachment/{id}', 'Comments\CommentsController@download_attachment');
                Route::get('/activate/{params}', 'Comments\CommentsController@activate');
            });

            Route::group(['prefix' => 'approvals'], function () {
                Route::get('/index', 'Approvals\ApprovalsController@index');
                Route::post('/index', 'Approvals\ApprovalsController@index');
                Route::get('/unfilter', 'Approvals\ApprovalsController@unfilter');
                Route::get('/addcomments/{id}', 'Approvals\ApprovalsController@addcomments');
                Route::post('/save', 'Approvals\ApprovalsController@save');
            });

            Route::group(['prefix' => 'migration'], function () {
                Route::get('/index', 'Migration\MigrationController@index');
                Route::post('/index', 'Migration\MigrationController@index');
                Route::get('/unfilter', 'Migration\MigrationController@unfilter');
                Route::get('/upload', 'Migration\MigrationController@upload_document');
                Route::post('/temp_document', 'Migration\MigrationController@temp_document');
                Route::get('/view_temp/{id}', 'Migration\MigrationController@view_temp');
                Route::post('/save_document', 'Migration\MigrationController@save_document');
                Route::get('/delete/{id}', 'Migration\MigrationController@delete');
            });

            Route::group(['prefix' => 'information'], function () {
                Route::get('/index', 'Document\InformationController@index');
                Route::post('/index', 'Document\InformationController@index');
                Route::get('/unfilter', 'Document\InformationController@unfilter');
                Route::get('/detail/{id}', 'Document\InformationController@detail');
                Route::get('/approve/{id}', 'Document\InformationController@approve');
                Route::post('/save_approve', 'Document\InformationController@save_approve');
            });

            Route::group(['prefix' => 'client_transmittal'], function () {
                Route::get('/index', 'Comments\CommentsController@index_client');
                Route::post('/index', 'Comments\CommentsController@index_client');
                Route::get('/unfilter', 'Comments\CommentsController@unfilter_client');
                Route::get('/download', 'Comments\CommentsController@download_client');
            });

            Route::group(['prefix' => 'idc'], function () {
                Route::get('/index', 'Document\IdcController@index');
                Route::post('/index', 'Document\IdcController@index');
                Route::get('/unfilter', 'Document\IdcController@unfilter');
                Route::get('/detail/{id}', 'Document\IdcController@detail');

                Route::get('/add', 'Document\IdcController@add');
                Route::post('/save', 'Document\IdcController@save');
                Route::post('/attach_item', 'Document\IdcController@attach_item');
                Route::get('/delete_item/{id}', 'Document\IdcController@delete_item');

                Route::get('/edit/{id}', 'Document\IdcController@edit');
                Route::post('/update', 'Document\IdcController@update');
            });

            Route::group(['prefix' => 'shopdrawing'], function () {
                Route::get('/index', 'Document\ShopDrawingController@index');
                Route::post('/index', 'Document\ShopDrawingController@index');
                Route::get('/unfilter', 'Document\ShopDrawingController@unfilter');
                Route::get('/detail/{id}', 'Document\ShopDrawingController@detail');
                Route::post('/update_status', 'Document\ShopDrawingController@update_status');
            });

            Route::group(['prefix' => 'interface_data'], function () {
                Route::get('/index', 'Shipyard\InterfaceController@index');
                Route::post('/index', 'Shipyard\InterfaceController@index');
                Route::get('/unfilter', 'Shipyard\InterfaceController@unfilter');
                Route::get('/detail/{id}', 'Shipyard\InterfaceController@detail');
                Route::get('/add', 'Shipyard\InterfaceController@add');
                Route::post('/save', 'Shipyard\InterfaceController@save');
                Route::get('/upload/{id}', 'Shipyard\InterfaceController@upload');
                Route::post('/attach_item', 'Shipyard\InterfaceController@attach_item');
                Route::get('/delete_item/{detail_id}/{id}', 'Shipyard\InterfaceController@delete_item');
                Route::get('/approve/{id}', 'Shipyard\InterfaceController@approve');
                Route::post('/save_approve', 'Shipyard\InterfaceController@save_approve');
                Route::get('/edit/{id}', 'Shipyard\InterfaceController@edit');
                Route::get('/add_subfolder/{id}', 'Shipyard\InterfaceController@add_subfolder');
                Route::post('/save_subfolder', 'Shipyard\InterfaceController@save_subfolder');
                Route::get('/detail_subfolder/{id}', 'Shipyard\InterfaceController@detail_subfolder');
                Route::get('/upload_subfolder/{id}', 'Shipyard\InterfaceController@upload_subfolder');
                Route::get('/delete_subfolder/{detail_id}/{id}', 'Shipyard\InterfaceController@delete_subfolder');
                Route::get('/get_subfolder_search/{id}/{textsearch}', 'Shipyard\InterfaceController@get_subfolder_search');
                Route::get('/get_subfolder_search/{id}', 'Shipyard\InterfaceController@get_subfolder_search');
                Route::get('/get_subfolder_search_detail/{id}/{textsearch}', 'Shipyard\InterfaceController@get_subfolder_search_detail');
                Route::get('/get_subfolder_search_detail/{id}', 'Shipyard\InterfaceController@get_subfolder_search_detail');
                Route::get('/delete/{id}', 'Shipyard\InterfaceController@delete');
                Route::post('/update_delete', 'Shipyard\InterfaceController@update_delete');
            });

            Route::group(['prefix' => 'chat'], function () {
                Route::get('/users', 'Chatting\ChattingController@users')->name('chat.users');
                Route::post('/open', 'Chatting\ChattingController@open')->name('chat.open');
                Route::post('/send', 'Chatting\ChattingController@send')->name('chat.send');
            });
        });

        Route::get('/maintenance_mode', 'Sys\SysController@maintenance_mode')->name('maintenance.mode');
        Route::get('/url', 'Sys\SysController@url_injection')->name('url.injection');

        Route::group(['prefix' => 'config'], function () {
            Route::get('/index', 'Sys\SysController@index')->middleware('url_injection');
            Route::post('/update', 'Sys\SysController@update');
        });

    });
    /* ----------
    ERROR PAGE
    ----------------------- */
    Route::get('/error/{id}', 'ErrorController@getError');
    Route::get('/error_page/{id}', 'ErrorController@getErrorPage');
    /* ----------
    PASSWORD EXPIRED
    ----------------------- */
    Route::get('/expired_password', 'Auth\ExpiredPasswordController@expired_password')->name('password.expired');
    Route::post('/change_expired_password', 'Auth\ExpiredPasswordController@change_expired_password');
    /* ----------
    REFERENCE
    ----------------------- */
    /* ----------
    Department
    ----------------------- */
    Route::get('/department/index/{page?}', 'Reference\DepartmentController@index');
    Route::post('/department/index', 'Reference\DepartmentController@index');
    Route::get('/department/add', 'Reference\DepartmentController@add');
    Route::post('/department/save', 'Reference\DepartmentController@save');
    Route::get('/department/edit/{id}', 'Reference\DepartmentController@edit');
    Route::put('/department/update', 'Reference\DepartmentController@update');
    Route::get('/department/delete/{id}', 'Reference\DepartmentController@delete');
    Route::put('/department/remove', 'Reference\DepartmentController@remove');
    Route::get('/department/unfilter', 'Reference\DepartmentController@unfilter');

    Route::get('/send_test_email', 'Sys\SysController@send_test_email');
});

Route::get('/forgot_password', 'Auth\ForgotPasswordController@forgot')->name('password.forgot');
Route::post('/send_forgot', 'Auth\ForgotPasswordController@send_forgot')->name('password.forgot-send');

Route::get('screenshot/index', [ScreenshotController::class, 'index']);
Route::get('screenshot', [ScreenshotController::class, 'sendDashboardController']);

// Route::fallback('Sys\ErrorController@pageNotFound');

// TRUNCATE TABLE `approval`;
// TRUNCATE TABLE `assignment`;
// TRUNCATE TABLE `comment`;
// TRUNCATE TABLE `comment_temp`;
// TRUNCATE TABLE `incoming_transmittal`;
// TRUNCATE TABLE `incoming_transmittal_detail`;
// TRUNCATE TABLE `incoming_transmittal_detail_temp`;
// TRUNCATE TABLE `outgoing_transmittal`;
// TRUNCATE TABLE `outgoing_transmittal_detail`;
// TRUNCATE TABLE `sys_logs`;
// TRUNCATE TABLE `sys_errors`;
// TRUNCATE TABLE `document`;
// TRUNCATE TABLE `document_change_log`;
// TRUNCATE TABLE `project`;

// TRUNCATE TABLE `approval`;
// TRUNCATE TABLE `assignment`;
// TRUNCATE TABLE `comment`;
// TRUNCATE TABLE `comment_temp`;
// TRUNCATE TABLE `incoming_transmittal`;
// TRUNCATE TABLE `incoming_transmittal_detail`;
// TRUNCATE TABLE `incoming_transmittal_detail_temp`;
// TRUNCATE TABLE `outgoing_transmittal`;
// TRUNCATE TABLE `outgoing_transmittal_detail`;
// TRUNCATE TABLE `sys_logs`;
// TRUNCATE TABLE `sys_errors`;
// UPDATE `document` SET STATUS=1, issue_status_id=0, document_Status_id=0, incoming_transmittal_detail_id=0, outgoing_transmittal_detail_id=0, deadline=NULL
//     , updated_by=0, updated_at=NULL, created_approved_by=0, created_approved_at=NULL, approved_by=0, approved_at=NULL, approved_comment=''
//     , created_design_by=0, created_design_at=NULL, approved_design_by=0, approved_design_at=NULL, approved_design_comment=NULL; 