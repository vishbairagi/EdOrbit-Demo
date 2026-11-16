<?php

use App\Http\Controllers\Api\Staff\AcademicApiController;
use App\Http\Controllers\Api\Staff\AccountingApiController;
use App\Http\Controllers\Api\Staff\AdministratorApiController;
use App\Http\Controllers\Api\Staff\AuthController;
use App\Http\Controllers\Api\Staff\ClassGroupApiController;
use App\Http\Controllers\Api\Staff\DashboardController;
use App\Http\Controllers\Api\Staff\ExaminationApiController;
use App\Http\Controllers\Api\Staff\LibraryApiController;
use App\Http\Controllers\Api\Staff\SchoolClassApiController;
use App\Http\Controllers\Api\Staff\SchoolSettingApiController;
use App\Http\Controllers\Api\Staff\StudentApiController;
use App\Http\Controllers\Api\Staff\TransportApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// =========================================================================
// STUDENT API (v1 & wp-json compatibility)
// =========================================================================
Route::prefix('v1/student')->group(function () {
    // Public Auth
    Route::post('/auth/login', [\App\Http\Controllers\Api\Student\StudentAuthController::class, 'login']);

    // Protected Student Routes (Requires JWT Bearer Token)
    Route::middleware('jwt.student')->group(function () {
        // 00_Auth
        Route::get('/profile', [\App\Http\Controllers\Api\Student\StudentAuthController::class, 'profile']);
        Route::post('/profile', [\App\Http\Controllers\Api\Student\StudentAuthController::class, 'updateProfile']);
        Route::post('/account-settings', [\App\Http\Controllers\Api\Student\StudentAuthController::class, 'accountSettings']);
        Route::post('/switch-session', [\App\Http\Controllers\Api\Student\StudentAuthController::class, 'switchSession']);

        // 01_Dashboard
        Route::get('/dashboard', [\App\Http\Controllers\Api\Student\StudentDashboardController::class, 'dashboard']);
        Route::get('/dashboard-settings', [\App\Http\Controllers\Api\Student\StudentDashboardController::class, 'dashboardSettings']);
        Route::get('/notifications/tokens', [\App\Http\Controllers\Api\Student\StudentDashboardController::class, 'getNotificationTokens']);
        Route::post('/notifications/tokens', [\App\Http\Controllers\Api\Student\StudentDashboardController::class, 'saveNotificationToken']);

        // 02_School
        Route::get('/about-school', [\App\Http\Controllers\Api\Student\StudentAcademicController::class, 'aboutSchool']);

        // 04_Academic
        Route::get('/subjects', [\App\Http\Controllers\Api\Student\StudentAcademicController::class, 'subjects']);
        Route::get('/class-time-table', [\App\Http\Controllers\Api\Student\StudentAcademicController::class, 'classTimeTable']);
        Route::post('/submit-staff-rating', [\App\Http\Controllers\Api\Student\StudentAcademicController::class, 'submitStaffRating']);
        Route::get('/id-card', [\App\Http\Controllers\Api\Student\StudentAcademicController::class, 'idCard']);
        Route::get('/live-classes', [\App\Http\Controllers\Api\Student\StudentAcademicController::class, 'liveClasses']);
        Route::get('/stationary-issued', [\App\Http\Controllers\Api\Student\StudentAcademicController::class, 'stationary']);

        // 06_Accounting
        Route::get('/fee-structure', [\App\Http\Controllers\Api\Student\StudentAccountingController::class, 'feeStructure']);
        Route::get('/session-fee-summary', [\App\Http\Controllers\Api\Student\StudentAccountingController::class, 'sessionFeeSummary']);
        Route::get('/fee-invoices', [\App\Http\Controllers\Api\Student\StudentAccountingController::class, 'feeInvoices']);
        Route::get('/fee-invoices/{id}', [\App\Http\Controllers\Api\Student\StudentAccountingController::class, 'feeInvoiceDetails']);
        Route::post('/payments', [\App\Http\Controllers\Api\Student\StudentAccountingController::class, 'payInvoice']);
        Route::get('/payments', [\App\Http\Controllers\Api\Student\StudentAccountingController::class, 'paymentHistory']);
        Route::get('/payments/{id}', [\App\Http\Controllers\Api\Student\StudentAccountingController::class, 'paymentReceipt']);

        // 08_Attendance
        Route::get('/attendance', [\App\Http\Controllers\Api\Student\StudentAttendanceController::class, 'attendance']);

        // 09_Examination
        Route::get('/exam-time-table', [\App\Http\Controllers\Api\Student\StudentExaminationController::class, 'examTimeTable']);
        Route::get('/exam-time-table/{id}', [\App\Http\Controllers\Api\Student\StudentExaminationController::class, 'examTimeTableDetails']);
        Route::get('/admit-cards', [\App\Http\Controllers\Api\Student\StudentExaminationController::class, 'admitCards']);
        Route::get('/admit-cards/{id}', [\App\Http\Controllers\Api\Student\StudentExaminationController::class, 'admitCardDetails']);
        Route::get('/exam-results', [\App\Http\Controllers\Api\Student\StudentExaminationController::class, 'examResults']);
        Route::get('/exam-results/{id}', [\App\Http\Controllers\Api\Student\StudentExaminationController::class, 'examResultDetails']);
        Route::get('/overall-result', [\App\Http\Controllers\Api\Student\StudentExaminationController::class, 'overallResult']);

        // 10_Certificates
        Route::get('/certificates', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'certificates']);
        Route::get('/certificates/{id}/generate', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'generateCertificate']);

        // 12_Homework
        Route::get('/homeworks', [\App\Http\Controllers\Api\Student\StudentHomeworkController::class, 'homeworks']);
        Route::get('/homeworks/{id}', [\App\Http\Controllers\Api\Student\StudentHomeworkController::class, 'homeworkDetails']);
        Route::post('/homeworks/{id}/submissions', [\App\Http\Controllers\Api\Student\StudentHomeworkController::class, 'submitHomework']);

        // 14_Leave Request
        Route::get('/leave-requests', [\App\Http\Controllers\Api\Student\StudentLeaveController::class, 'leaveRequests']);
        Route::post('/leave-requests', [\App\Http\Controllers\Api\Student\StudentLeaveController::class, 'submitLeaveRequest']);

        // 15_Lessons
        Route::get('/lessons', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'lessons']);
        Route::get('/chapters', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'chapters']);
        Route::get('/lessons/{id}', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'lessonDetails']);

        // 16_Library
        Route::get('/books-issued', [\App\Http\Controllers\Api\Student\StudentLibraryController::class, 'booksIssued']);
        Route::get('/books-issued/{id}', [\App\Http\Controllers\Api\Student\StudentLibraryController::class, 'bookIssuedDetails']);

        // 19_Study Materials
        Route::get('/study-materials', [\App\Http\Controllers\Api\Student\StudentAcademicController::class, 'studyMaterials']);
        Route::get('/study-materials/{id}', [\App\Http\Controllers\Api\Student\StudentAcademicController::class, 'studyMaterialDetails']);

        // 20_Tickets
        Route::get('/tickets', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'tickets']);
        Route::post('/tickets', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'submitTicket']);
        Route::get('/tickets/{id}/history', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'ticketHistory']);

        // 21_Transport
        Route::get('/transport', [\App\Http\Controllers\Api\Student\StudentTransportController::class, 'transport']);

        // 22_SM Chat
        Route::get('/chat/teachers', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'chatTeachers']);
        Route::get('/chat/conversations', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'chatConversations']);
        Route::get('/chat/conversations/{id}/messages', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'chatMessages']);
        Route::post('/chat/conversations/{id}/messages', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'sendChatMessage']);
        Route::post('/chat/conversations', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'createChatConversation']);
        Route::put('/chat/messages/{id}', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'editChatMessage']);
        Route::delete('/chat/messages/{id}', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'deleteChatMessage']);

        // 23_Communications
        Route::get('/noticeboard', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'noticeboard']);
        Route::get('/events', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'events']);
        Route::get('/events/{id}', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'eventDetails']);
        Route::post('/events/{id}/join', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'joinEvent']);
        Route::post('/events/{id}/unjoin', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'unjoinEvent']);
        Route::get('/calendar', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'calendar']);
    });
});

// =========================================================================
// TEACHER & ADMIN API (v1)
// =========================================================================

Route::prefix('v1')->group(function () {

    // -------------------------------------------------------------
    // Public Authentication Endpoints
    // -------------------------------------------------------------
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/staff/login', [AuthController::class, 'login']);
    Route::post('/admin/login', [AuthController::class, 'login']);

    // -------------------------------------------------------------
    // Protected Routes (Requires JWT Bearer Token)
    // -------------------------------------------------------------
    Route::middleware('jwt.staff')->group(function () {

        // Profile & Session Management
        Route::get('/auth/profile', [AuthController::class, 'profile']);
        Route::get('/sessions', [AuthController::class, 'sessionList']);
        Route::get('/school-session', [AuthController::class, 'schoolSession']);
        Route::post('/sessions/switch', [AuthController::class, 'switchSession']);
        Route::post('/switch-school', [AuthController::class, 'switchSchool']);
        Route::get('/currency-list', [AuthController::class, 'currencyList']);
        Route::get('/permissions', [DashboardController::class, 'allPermissions']);

        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index']);

        // ---------------------------------------------------------
        // 1. Class Groups (sm-school.class-groups)
        // ---------------------------------------------------------
        Route::middleware('jwt.permission:sm-school.class-groups')->group(function () {
            Route::get('/class-groups', [ClassGroupApiController::class, 'index']);
            Route::post('/class-groups', [ClassGroupApiController::class, 'store']);
            Route::get('/class-groups/{id}', [ClassGroupApiController::class, 'show']);
            Route::put('/class-groups/{id}', [ClassGroupApiController::class, 'update']);
            Route::delete('/class-groups/{id}', [ClassGroupApiController::class, 'destroy']);
        });

        // ---------------------------------------------------------
        // 2. Classes & Sections (sm-school.classes)
        // ---------------------------------------------------------
        Route::middleware('jwt.permission:sm-school.classes')->group(function () {
            Route::get('/classes', [SchoolClassApiController::class, 'index']);
            Route::post('/classes', [SchoolClassApiController::class, 'store']);
            Route::get('/classes/{id}', [SchoolClassApiController::class, 'show']);
            Route::put('/classes/{id}', [SchoolClassApiController::class, 'update']);
            Route::delete('/classes/{id}', [SchoolClassApiController::class, 'destroy']);

            Route::get('/sections', [SchoolClassApiController::class, 'listSections']);
            Route::post('/sections', [SchoolClassApiController::class, 'storeSection']);
            Route::get('/sections/{id}', [SchoolClassApiController::class, 'showSection']);
            Route::put('/sections/{id}', [SchoolClassApiController::class, 'updateSection']);
            Route::delete('/sections/{id}', [SchoolClassApiController::class, 'destroySection']);
            Route::get('/classes/{class_id}/sections', [SchoolClassApiController::class, 'listSections']);
        });

        // ---------------------------------------------------------
        // 3. School Settings (sm-school.settings)
        // ---------------------------------------------------------
        Route::middleware('jwt.permission:sm-school.settings')->group(function () {
            Route::get('/school-settings', [SchoolSettingApiController::class, 'index']);
            Route::post('/school-settings', [SchoolSettingApiController::class, 'update']);
            Route::put('/school-settings', [SchoolSettingApiController::class, 'update']);

            Route::get('/school-settings/general', [SchoolSettingApiController::class, 'viewGeneralSettings']);
            Route::post('/school-settings/general', [SchoolSettingApiController::class, 'saveGeneralSettings']);
            Route::get('/school-settings/payment-methods', [SchoolSettingApiController::class, 'viewPaymentMethodsSettings']);
            Route::post('/school-settings/payment-methods', [SchoolSettingApiController::class, 'savePaymentMethodsSettings']);
            Route::get('/school-settings/live-classes', [SchoolSettingApiController::class, 'viewLiveClassesSettings']);
            Route::post('/school-settings/live-classes', [SchoolSettingApiController::class, 'saveLiveClassesSettings']);
            Route::get('/school-settings/card-backgrounds', [SchoolSettingApiController::class, 'viewCardBackgroundsSettings']);
            Route::post('/school-settings/card-backgrounds', [SchoolSettingApiController::class, 'saveCardBackgroundsSettings']);
            Route::get('/school-settings/examination', [SchoolSettingApiController::class, 'viewExaminationSettings']);
            Route::post('/school-settings/examination', [SchoolSettingApiController::class, 'saveExaminationSettings']);
        });

        // ---------------------------------------------------------
        // 4. Academic APIs
        // ---------------------------------------------------------
        Route::prefix('academic')->group(function () {
            // Class Teacher (academic.class-teachers)
            Route::middleware('jwt.permission:academic.class-teachers')->group(function () {
                Route::get('/class-teachers', [AcademicApiController::class, 'classTeachersIndex']);
                Route::post('/class-teachers', [AcademicApiController::class, 'classTeacherStore']);
                Route::get('/class-teachers/{id}', [AcademicApiController::class, 'classTeacherShow']);
                Route::put('/class-teachers/{id}', [AcademicApiController::class, 'classTeacherUpdate']);
                Route::delete('/class-teachers/{id}', [AcademicApiController::class, 'classTeacherDestroy']);
            });

            // Class Section (academic.class-sections)
            Route::middleware('jwt.permission:academic.class-sections')->group(function () {
                Route::get('/class-sections', [SchoolClassApiController::class, 'listSections']);
                Route::post('/class-sections', [SchoolClassApiController::class, 'storeSection']);
                Route::get('/class-sections/{id}', [SchoolClassApiController::class, 'showSection']);
                Route::put('/class-sections/{id}', [SchoolClassApiController::class, 'updateSection']);
                Route::delete('/class-sections/{id}', [SchoolClassApiController::class, 'destroySection']);
            });

            // Medium (academic.mediums)
            Route::middleware('jwt.permission:academic.mediums')->group(function () {
                Route::get('/mediums', [AcademicApiController::class, 'mediumsIndex']);
                Route::post('/mediums', [AcademicApiController::class, 'mediumStore']);
                Route::get('/mediums/{id}', [AcademicApiController::class, 'mediumShow']);
                Route::put('/mediums/{id}', [AcademicApiController::class, 'mediumUpdate']);
                Route::delete('/mediums/{id}', [AcademicApiController::class, 'mediumDestroy']);
            });

            // Subject (academic.subjects)
            Route::middleware('jwt.permission:academic.subjects')->group(function () {
                Route::get('/subjects', [AcademicApiController::class, 'subjectsIndex']);
                Route::post('/subjects', [AcademicApiController::class, 'subjectStore']);
                Route::get('/subjects/{id}', [AcademicApiController::class, 'subjectShow']);
                Route::put('/subjects/{id}', [AcademicApiController::class, 'subjectUpdate']);
                Route::delete('/subjects/{id}', [AcademicApiController::class, 'subjectDestroy']);
                Route::post('/assign-staff', [AcademicApiController::class, 'assignStaffToSubject']);
            });

            // Subject Type (academic.subject-types)
            Route::middleware('jwt.permission:academic.subject-types')->group(function () {
                Route::get('/subject-types', [AcademicApiController::class, 'subjectTypesIndex']);
                Route::post('/subject-types', [AcademicApiController::class, 'subjectTypeStore']);
                Route::get('/subject-types/{id}', [AcademicApiController::class, 'subjectTypeShow']);
                Route::put('/subject-types/{id}', [AcademicApiController::class, 'subjectTypeUpdate']);
                Route::delete('/subject-types/{id}', [AcademicApiController::class, 'subjectTypeDestroy']);
            });

            // Study Material (academic.study-materials)
            Route::middleware('jwt.permission:academic.study-materials')->group(function () {
                Route::get('/study-materials', [AcademicApiController::class, 'studyMaterialsIndex']);
                Route::post('/study-materials', [AcademicApiController::class, 'studyMaterialStore']);
                Route::get('/study-materials/{id}', [AcademicApiController::class, 'studyMaterialShow']);
                Route::put('/study-materials/{id}', [AcademicApiController::class, 'studyMaterialUpdate']);
                Route::delete('/study-materials/{id}', [AcademicApiController::class, 'studyMaterialDestroy']);
            });

            // Noticeboard (academic.noticeboards)
            Route::middleware('jwt.permission:academic.noticeboards')->group(function () {
                Route::get('/noticeboards', [AcademicApiController::class, 'noticeboardsIndex']);
                Route::post('/noticeboards', [AcademicApiController::class, 'noticeboardStore']);
                Route::get('/noticeboards/{id}', [AcademicApiController::class, 'noticeboardShow']);
                Route::put('/noticeboards/{id}', [AcademicApiController::class, 'noticeboardUpdate']);
                Route::delete('/noticeboards/{id}', [AcademicApiController::class, 'noticeboardDestroy']);
            });
        });

        // ---------------------------------------------------------
        // 5. Examination APIs
        // ---------------------------------------------------------
        Route::prefix('examination')->group(function () {
            // Manage Exams (examination.exams)
            Route::middleware('jwt.permission:examination.exams')->group(function () {
                Route::get('/exams', [ExaminationApiController::class, 'index']);
                Route::post('/exams', [ExaminationApiController::class, 'store']);
                Route::get('/exams/{id}', [ExaminationApiController::class, 'show']);
                Route::put('/exams/{id}', [ExaminationApiController::class, 'update']);
                Route::delete('/exams/{id}', [ExaminationApiController::class, 'destroy']);
            });

            // Exam Groups (examination.groups)
            Route::middleware('jwt.permission:examination.groups')->group(function () {
                Route::get('/groups', [ExaminationApiController::class, 'groupsIndex']);
                Route::post('/groups', [ExaminationApiController::class, 'groupStore']);
                Route::get('/groups/{id}', [ExaminationApiController::class, 'groupShow']);
                Route::put('/groups/{id}', [ExaminationApiController::class, 'groupUpdate']);
                Route::delete('/groups/{id}', [ExaminationApiController::class, 'groupDestroy']);
            });

            // Admit Cards (examination.admit-cards)
            Route::middleware('jwt.permission:examination.admit-cards')->group(function () {
                Route::get('/admit-cards', [ExaminationApiController::class, 'admitCardsIndex']);
                Route::post('/admit-cards/generate', [ExaminationApiController::class, 'generateAdmitCards']);
                Route::get('/admit-cards/{id}', [ExaminationApiController::class, 'admitCardShow']);
                Route::put('/admit-cards/{id}', [ExaminationApiController::class, 'admitCardUpdate']);
                Route::delete('/admit-cards/{id}', [ExaminationApiController::class, 'admitCardDestroy']);
            });

            // Exam Results (examination.results)
            Route::middleware('jwt.permission:examination.results')->group(function () {
                Route::get('/results', [ExaminationApiController::class, 'resultsIndex']);
                Route::post('/results', [ExaminationApiController::class, 'resultStore']);
                Route::get('/results/{id}', [ExaminationApiController::class, 'resultShow']);
                Route::put('/results/{id}', [ExaminationApiController::class, 'resultUpdate']);
                Route::delete('/results/{id}', [ExaminationApiController::class, 'resultDestroy']);
            });
        });

        // ---------------------------------------------------------
        // 6. Student APIs
        // ---------------------------------------------------------
        Route::prefix('students')->group(function () {
            // Students CRUD (sm-student.students)
            Route::middleware('jwt.permission:sm-student.students')->group(function () {
                Route::get('/', [StudentApiController::class, 'index']);
                Route::post('/', [StudentApiController::class, 'store']);
                Route::get('/{id}', [StudentApiController::class, 'show'])->where('id', '[0-9]+');
                Route::put('/{id}', [StudentApiController::class, 'update'])->where('id', '[0-9]+');
                Route::delete('/{id}', [StudentApiController::class, 'destroy'])->where('id', '[0-9]+');
            });

            // ID Cards (sm-student.print-id-cards)
            Route::middleware('jwt.permission:sm-student.print-id-cards')->group(function () {
                Route::get('/id-cards', [StudentApiController::class, 'idCards']);
            });

            // Promote (sm-student.promote)
            Route::middleware('jwt.permission:sm-student.promote')->group(function () {
                Route::post('/promote', [StudentApiController::class, 'promote']);
            });

            // Transfer (sm-student.transfer)
            Route::middleware('jwt.permission:sm-student.transfer')->group(function () {
                Route::post('/transfer', [StudentApiController::class, 'transfer']);
                Route::get('/transfers', [StudentApiController::class, 'transferList']);
            });
        });

        // ---------------------------------------------------------
        // 7. Administrator APIs
        // ---------------------------------------------------------
        Route::prefix('administrator')->group(function () {
            // Admins (sm-administrator.admins)
            Route::middleware('jwt.permission:sm-administrator.admins')->group(function () {
                Route::get('/admins', [AdministratorApiController::class, 'adminsIndex']);
                Route::post('/admins', [AdministratorApiController::class, 'adminStore']);
                Route::get('/admins/{id}', [AdministratorApiController::class, 'adminShow']);
                Route::put('/admins/{id}', [AdministratorApiController::class, 'adminUpdate']);
                Route::delete('/admins/{id}', [AdministratorApiController::class, 'adminDestroy']);
            });

            // Roles (sm-administrator.roles)
            Route::middleware('jwt.permission:sm-administrator.roles')->group(function () {
                Route::get('/roles', [AdministratorApiController::class, 'rolesIndex']);
                Route::post('/roles', [AdministratorApiController::class, 'roleStore']);
                Route::get('/roles/{id}', [AdministratorApiController::class, 'roleShow']);
                Route::put('/roles/{id}', [AdministratorApiController::class, 'roleUpdate']);
                Route::delete('/roles/{id}', [AdministratorApiController::class, 'roleDestroy']);
            });

            // Staff (sm-administrator.staff)
            Route::middleware('jwt.permission:sm-administrator.staff')->group(function () {
                Route::get('/staff', [AdministratorApiController::class, 'staffIndex']);
                Route::post('/staff', [AdministratorApiController::class, 'staffStore']);
                Route::get('/staff/{id}', [AdministratorApiController::class, 'staffShow']);
                Route::put('/staff/{id}', [AdministratorApiController::class, 'staffUpdate']);
                Route::delete('/staff/{id}', [AdministratorApiController::class, 'staffDestroy']);
            });

            // Attendance (sm-administrator.attendance)
            Route::middleware('jwt.permission:sm-administrator.attendance')->group(function () {
                Route::get('/attendance', [AdministratorApiController::class, 'staffAttendanceIndex']);
                Route::post('/attendance', [AdministratorApiController::class, 'staffAttendanceStore']);
            });

            // Leaves (sm-administrator.leaves)
            Route::middleware('jwt.permission:sm-administrator.leaves')->group(function () {
                Route::get('/leaves', [AdministratorApiController::class, 'staffLeavesIndex']);
                Route::post('/leaves', [AdministratorApiController::class, 'staffLeaveStore']);
                Route::put('/leaves/{id}/status', [AdministratorApiController::class, 'staffLeaveUpdateStatus']);
            });
        });

        // ---------------------------------------------------------
        // 8. Accounting APIs
        // ---------------------------------------------------------
        Route::prefix('accounting')->group(function () {
            // Fee Types (accounting.fee-types)
            Route::middleware('jwt.permission:accounting.fee-types')->group(function () {
                Route::get('/fee-types', [AccountingApiController::class, 'feeTypesIndex']);
                Route::post('/fee-types', [AccountingApiController::class, 'feeTypeStore']);
                Route::get('/fee-types/{id}', [AccountingApiController::class, 'feeTypeShow']);
                Route::put('/fee-types/{id}', [AccountingApiController::class, 'feeTypeUpdate']);
                Route::delete('/fee-types/{id}', [AccountingApiController::class, 'feeTypeDestroy']);
            });

            // Fee Invoices (accounting.fee-invoices)
            Route::middleware('jwt.permission:accounting.fee-invoices')->group(function () {
                Route::get('/invoices', [AccountingApiController::class, 'invoicesIndex']);
                Route::post('/invoices', [AccountingApiController::class, 'invoiceStore']);
                Route::get('/invoices/{id}', [AccountingApiController::class, 'invoiceShow']);
                Route::put('/invoices/{id}', [AccountingApiController::class, 'invoiceUpdate']);
                Route::delete('/invoices/{id}', [AccountingApiController::class, 'invoiceDestroy']);
            });

            // Payments (accounting.collect-payment)
            Route::middleware('jwt.permission:accounting.collect-payment')->group(function () {
                Route::post('/payments', [AccountingApiController::class, 'collectPayment']);
                Route::get('/payments/history', [AccountingApiController::class, 'paymentHistory']);
            });
        });

        // ---------------------------------------------------------
        // 9. Transport APIs
        // ---------------------------------------------------------
        Route::prefix('transport')->group(function () {
            // Vehicles (transport.vehicles)
            Route::middleware('jwt.permission:transport.vehicles')->group(function () {
                Route::get('/vehicles', [TransportApiController::class, 'vehiclesIndex']);
                Route::post('/vehicles', [TransportApiController::class, 'vehicleStore']);
                Route::get('/vehicles/{id}', [TransportApiController::class, 'vehicleShow']);
                Route::put('/vehicles/{id}', [TransportApiController::class, 'vehicleUpdate']);
                Route::delete('/vehicles/{id}', [TransportApiController::class, 'vehicleDestroy']);
            });

            // Routes (transport.routes)
            Route::middleware('jwt.permission:transport.routes')->group(function () {
                Route::get('/routes', [TransportApiController::class, 'routesIndex']);
                Route::post('/routes', [TransportApiController::class, 'routeStore']);
                Route::get('/routes/{id}', [TransportApiController::class, 'routeShow']);
                Route::put('/routes/{id}', [TransportApiController::class, 'routeUpdate']);
                Route::delete('/routes/{id}', [TransportApiController::class, 'routeDestroy']);
            });

            // Assign Students (transport.assign-students)
            Route::middleware('jwt.permission:transport.assign-students')->group(function () {
                Route::post('/assign-student', [TransportApiController::class, 'assignStudentToRoute']);
                Route::delete('/remove-student', [TransportApiController::class, 'removeStudentFromRoute']);
            });
        });

        // ---------------------------------------------------------
        // 10. Library APIs
        // ---------------------------------------------------------
        Route::prefix('library')->group(function () {
            // Books (library.books)
            Route::middleware('jwt.permission:library.books')->group(function () {
                Route::get('/books', [LibraryApiController::class, 'booksIndex']);
                Route::post('/books', [LibraryApiController::class, 'bookStore']);
                Route::get('/books/{id}', [LibraryApiController::class, 'bookShow']);
                Route::put('/books/{id}', [LibraryApiController::class, 'bookUpdate']);
                Route::delete('/books/{id}', [LibraryApiController::class, 'bookDestroy']);
            });

            // Book Issue (library.book-issue)
            Route::middleware('jwt.permission:library.book-issue')->group(function () {
                Route::get('/book-issues', [LibraryApiController::class, 'bookIssuesIndex']);
                Route::post('/book-issues', [LibraryApiController::class, 'bookIssueStore']);
                Route::put('/book-issues/{id}/return', [LibraryApiController::class, 'bookIssueReturn']);
                Route::delete('/book-issues/{id}', [LibraryApiController::class, 'bookIssueDestroy']);
            });

            // Library Cards (library.library-cards)
            Route::middleware('jwt.permission:library.library-cards')->group(function () {
                Route::get('/cards', [LibraryApiController::class, 'libraryCardsIndex']);
                Route::post('/cards', [LibraryApiController::class, 'libraryCardStore']);
                Route::delete('/cards/{id}', [LibraryApiController::class, 'libraryCardDestroy']);
            });
        });
    });
});

// Load Postman / WordPress Plugin Compatibility Routes
require __DIR__ . '/wp_json_api.php';
