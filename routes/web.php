<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('t', 'ApiController@index2');
Route::get('server', 'ApiController@ServerURL');

Route::get('/testLoanSet/{id}', 'UserController@testLoanSet');

//cache clear
Route::get('/clear-cache', function () {
    Artisan::call('config:cache');
    return "Cache is cleared";
});

//language switcher route
Route::get('/lang/{locale}', function ($locale) {
    session()->put('locale', $locale);
    return redirect()->back();
});
Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);
//oauth 2 test route
Route::get('/oauth', 'DashboardController@tokenVerify');
Route::get('/getErpDcsAddmissionList', 'DashboardController@getErpDcsAddmissionList');
Route::get('/insertPostedAddmissionList', 'DashboardController@insertPostedAddmissionList');
Route::get('/testing/{id}', 'DashboardController@test');
Route::get('/testing/loan/{id}', 'ApiController@loanDataProccessForErp');

//Login Routes
Route::get('/weblogin', 'LoginController@weblogin');
Route::get('/Logout', 'LoginController@Logout');

// DCS Live API Controller Create by Najmul
Route::post('/DcsDataSync', 'LiveApiController@DcsDataSync');
Route::post('/NID_Verification', 'LiveApiController@NID_Verification');
Route::post('/Sync', 'TestController@Sync');
/***************************Start Api******************************************/
Route::get('/DcsBufferStatusCheck', 'ApiController@ERPBufferCheck');
Route::get('/DcsBufferStatusCheck2', 'ApiController@ERP_Server_Status_Check');
Route::get('/DcsBufferStatusCheckAdmission', 'ApiController@ERPBufferCheckAdmission');
Route::get('/test', 'ApiController@Index');
Route::post('/PoSync', 'ApiController@PoSync');
Route::get('/DcsDataPulling', 'ApiController@DcsDataPulling');
Route::post('/ReportSync', 'ApiController@ReportSync');
Route::post('/OperationsDataSync', 'ApiController@OperationsDataSync');
Route::get('/GetErpPostedAdmissionData', 'ApiController@GetErpPostedAdmissionData');
Route::get('/erpVOList', 'ApiController@erpVOList');
Route::get('/erpMemberList', 'ApiController@erpMemberList');
Route::get('/erpSavingsInfo', 'ApiController@erpSavingsInfo');
Route::get('/LastOneCloseLoanBehavior', 'ApiController@LastOneCloseLoanBehavior');
Route::post('/dcsInstallmentCalculator', 'ApiController@dcsInstallmentCalculator');
Route::post('/dcsInsurancePremiumCalculation', 'ApiController@dcsInsurancePremiumCalculation');
Route::get('/GetErpPostedLoanData', 'ApiController@GetErpPostedLoanData');
Route::post('/BranchGrowthType', 'ApiController@BranchGrowthType');
Route::post('/CelingConfig', 'ApiController@CelingConfig');
Route::POST('/Authrization', 'ApiController@Auth');
Route::POST('/NIDVerification', 'ApiController@NIDVerification');
//Route::POST('/ImageUpload', 'ApiController@ImageUpload');
Route::POST('/ImageUpload', 'ImageUploadController@ImageUploadOthers');
Route::post('/SurveyDataStore', 'ApiController@SurveyStore');
Route::get('/AllSurveyData', 'ApiController@AllSurveyData');
Route::post('/AdmissionDataStore', 'ApiController@AdmissionStore');
Route::get('/AllAdmissionData', 'ApiController@AllAdmissionData');
Route::post('/LoanRcaDataStore', 'ApiController@LoanRcaDataStore');
Route::get('/AllLoanRcaData', 'ApiController@AllLoanRcaData');
Route::get('/NotificationManager', 'ApiController@NotificationManager');
Route::get('/DocumentManager', 'ApiController@DocumentManager');
Route::post('/AdmissionDataSync', 'ApiController@AdmissionDataSync');
Route::post('/LoanDataSync', 'ApiController@LoanDataSync');
Route::post('/BmDataModify', 'ApiController@BmDataModify');
Route::post('/BmAdmissionAssessment', 'ApiController@BmAdmissionAssessment');
Route::post('/BmLoanAssessment', 'ApiController@BmLoanAssessment');
Route::get('/NotificatioManager', 'ApiController@NotificatioManager');

Route::get('admissionsearch', 'UpdateAdmissionController@index');
Route::get('admissionview', 'UpdateAdmissionController@GetData');
Route::get('EditView', 'UpdateAdmissionController@Editpage');
Route::post('updateAdmissionStore', 'UpdateAdmissionController@UpdateAdmissionStore');
Route::get('/ShadhinLoan', 'DCSTODABI_ShadhinLoanController@Get_ShadhinLoan_Data');
Route::get('/BufferCheck1', 'ApiController@ERP_Server_Status_Check');
Route::get('/BufferCheck', 'DCS_ERP_BufferStatusCheck@ERP_Server_Status_Check');
Route::get('GetActivePo', 'POListController@Get_PO_From_SyncCloud');
Route::post('/AdmissionReject', 'UpdateAdmissionController@AdmissionReject');
Route::get('admissionRejectview', 'UpdateAdmissionController@AdmissionRejectView');
Route::post('/checkloan', 'UpdateAdmissionController@CheckLoan');

//sagor

Route::post('/roledata', 'DashboardController@getRollWiseData');
Route::post('/fetchdata', 'DashboardController@fetchData');


//Search Start
Route::post('/search', 'DashboardController@search');

Route::get('/alldiv', 'DashboardController@GetDivisionData');
Route::get('/allreg', 'DashboardController@GetRegionData');
Route::get('/allarea', 'DashboardController@GetAreaData');
Route::get('/allbra', 'DashboardController@GetBranchData');
Route::get('/allpo', 'DashboardController@GetProgramOrganizerData');
//Search End

Route::get('/allcount', 'DashboardController@allCount');

//End sagor


/***************************End Api******************************************/
Route::get('/loginpage', 'DashboardController@login');

// middleware route for login
Route::group(['middleware' => ['Logcheck']], function () {

    Route::get('/', function () {
        return view('Dashboard');
    });
    Route::get('/dashboardTable', 'DashboardController@dashboardTable');
    Route::get('/form', function () {
        return view('Form');
    });
    // Dashboard
    Route::get('/dashboard', 'DashboardController@index')->name('dashboard');
    // project selection
    Route::get('/user/{project}', 'DashboardController@project')->name('user_project');
    // share data
    // View::composer('backend.layouts.partials.sidebar', function ($view) {
    //     $projects = Project::all();
    //     $view->with('projects',$projects);
    // });

    Route::prefix('config')->group(function () {
        // admin configuration
        Route::get('/admin-config', 'UserController@index')->name('admin_config');
        Route::post('/admin-store', 'UserController@store')->name('admin_store');
        Route::get('/admin-delete/{id}', 'UserController@delete');
        Route::get('/admin-edit/{id}', 'UserController@edit');
        Route::post('/admin-update/{id}', 'UserController@update');

        // project creation
        Route::get('/project-create', 'ProjectController@index')->name('project');
        Route::post('/project-store', 'ProjectController@store')->name('create-project');
        // middleware route for project
        Route::group(['middleware' => ['Project']], function () {
            // Role Hierarchy
            Route::get('/role-hierarchy', 'RoleHierarchyController@index')->name('role-hierarchy_config');
            Route::post('/role-hierarchy-store', 'RoleHierarchyController@store')->name('role-hierarchy');
            Route::post('/role-hierarchy-update', 'RoleHierarchyController@update')->name('hierarchy-update');
            Route::get('/update-status', 'RoleHierarchyController@updateStatus');
            // Celling-config
            Route::get('/celling-config', 'CellingController@index')->name('celling-config');
            // auth config
            Route::get('/auth', 'AuthConfigController@index')->name('authorization');
            Route::post('/auth-store', 'AuthConfigController@store')->name('auth-config');
            Route::post('/auth-edit', 'AuthConfigController@edit');
            Route::post('/auth-update', 'AuthConfigController@update')->name('auth-update');
            // Notification configaration
            Route::get('/notification-view', 'NotificationController@index')->name('notification_config');
            Route::post('/notification-view', 'NotificationController@view');
            Route::post('/process-view', 'NotificationController@process');
            Route::get('/notification-delete/{id}', 'NotificationController@delete');
            Route::get('/notification-edit/{id}', 'NotificationController@edit');
            Route::post('/notification-store', 'NotificationController@store')->name('notification');
            Route::post('/notification-update/{id}', 'NotificationController@update')->name('notification-update');
            //form config && popup url
            Route::post('/Formconfig-popup', 'PopupController@store')->name("Formconfig-popup");
            Route::get('/form-config', 'FormConfigController@index')->name('form-config');
            Route::post('/form-config', 'FormConfigController@app_form')->name('application_form');
            Route::post('/form-config/store', 'FormConfigController@store')->name('formConfigstore');
            // dashboard configuration
            Route::get('dashboard-config', function () {
                return view('DashboardConfig');
            });
        });
    });

    // middleware route for project
    Route::group(['middleware' => ['Project']], function () {

        Route::get('/report', 'ReportController@report')->name('report');
        Route::post('/report', 'ReportController@search_report')->name('report.search');

        Route::prefix('operation')->group(function () {
            // branch filter ajax for admission & loan
            Route::get('/branch_filter', 'AdmissionController@branchFilter');
            //admission & loan
            Route::get('/loan', 'LoanController@index')->name("loan-request");
            Route::get('/admission', 'AdmissionController@index')->name("admission-request");
            //admission & loan ajax call
            Route::get('/admissionTable', 'AdmissionController@admissionTable');
            Route::get('/loanTable', 'LoanController@loanTable');
            // admission search
            Route::post('/admission-division', 'AdmissionController@division');
            Route::post('/admission-region', 'AdmissionController@region');
            Route::post('/admission-area', 'AdmissionController@area');
            Route::post('/admission-branch', 'AdmissionController@branch');
            Route::get('/admission-search', 'AdmissionController@search');
            Route::get('/loan-search', 'LoanController@search');
            // admission-aprroval
            Route::get('/admission-approval/{id}', 'AdmissionController@admission_approve');
            Route::post('/admission-action', 'AdmissionController@action_btn')->name('admission_action');
            Route::post('/admission-Approve', 'AdmissionController@approve_admission')->name('admission_approve');

            // loan-aprroval
            Route::get('/loan-approval/{id}', 'LoanController@loan_approve');
            Route::post('/loan-action', 'LoanController@action_btn')->name('action_btn');
            Route::post('/loan-Approve', 'LoanController@approve_loan')->name('loan_approve');
            Route::get('/closeLoan', 'LoanController@closeLoan');
            Route::get('/savingsTransaction', 'LiveApiController@Savings_Transaction');
            Route::get('/Savings_Behaviours', 'LoanController@Savings_Behaviours');

            //dashboard
            Route::post('/statusSession', 'DashboardController@statusSession');
        });
        Route::prefix('report')->group(function () {
            // form
            Route::get('/survey', 'SurveyController@index');
            Route::post('/survey', 'SurveyController@store')->name('store');
            Route::get('/posted-admission', 'postedAdmissionController@index')->name('postedAdmission');
            Route::get('/postedAdmissionData', 'postedAdmissionController@postedAdmissionData');
            Route::get('/posted-admission-details/{id}', 'postedAdmissionController@postedAdmissionDetails');
            Route::get('/posted-loan', 'postedLoanController@index')->name('postedLoan');
            Route::get('/postedLoanData', 'postedLoanController@postedLoanData');
            Route::get('/posted-loan-details/{id}', 'postedLoanController@postedLoanDetails');
            Route::post('/posted-loan', 'AdmissionController@store')->name('sotre');
            Route::get('/s_show', 'FormConfigController@s_show');
            Route::get('/loan-proposal', 'LoanProposalContoller@index');
            Route::get('/disbursment-status', 'DusbursementController@index')->name("disbursment-status");
            // Admin config
            Route::post('/user-store', 'UserController@store')->name('user');
            // Action List
            Route::get('/action-list', 'ActionlistContoller@index')->name('action');
            Route::post('/action-list-store', 'ActionlistContoller@store')->name('action-list');
            // Message Que
            Route::get('/message-que', 'MessageQueController@index')->name('message-que_config');
            Route::post('/message-que-store', 'MessageQueController@store')->name('message-que');
            // process
            Route::get('/process', 'ProcessController@index')->name('process-create');
            Route::post('/process-store', 'ProcessController@store')->name('process');
            // Route::post('/process-store', 'ProcessController@store')->name('process');
            Route::get('/survey-table', 'SurveyTableController@index');
            Route::get('/survey-info', 'SurveyTableController@survey_info');
            // admission api
            Route::get('/admission-api', 'AdmissionTableController@admission_api');
            Route::get('/UserLoad', 'MainController@userListLoad');
        });
        /********************************Start Notification*****************************************/
        Route::get('Notification', 'NotificationController@Notification');
        //ajax call
        Route::get('getNotification', 'NotificationController@getNotification');
        Route::post('readNotification', 'NotificationController@readNotification');
        /********************************End Notification*****************************************/
    });
});
