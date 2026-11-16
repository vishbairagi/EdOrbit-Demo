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
use Illuminate\Support\Facades\Route;

// =========================================================================
// WORDPRESS / SCHOOL PLUGIN COMPATIBILITY ROUTES (wp-json)
// =========================================================================

// Public Token Auth
Route::post('wp-json/jwt-auth/v1/token', [AuthController::class, 'login']);

Route::middleware('jwt.staff')->prefix('wp-json/sm/staff')->group(function () {

    // 00_Auth / 01_Dashboard
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/school-session', [AuthController::class, 'schoolSession']);
    Route::get('/session-list', [AuthController::class, 'sessionList']);
    Route::get('/all-permission-list', [DashboardController::class, 'allPermissions']);
    Route::get('/currency-list', [AuthController::class, 'currencyList']);
    Route::post('/switch-school', [AuthController::class, 'switchSchool']);
    Route::post('/switch-session', [AuthController::class, 'switchSession']);

    // 02_School Settings
    Route::get('/view-school-general-settings', [SchoolSettingApiController::class, 'viewGeneralSettings']);
    Route::post('/save-school-general-settings', [SchoolSettingApiController::class, 'saveGeneralSettings']);
    Route::get('/view-school-payment-methods-settings', [SchoolSettingApiController::class, 'viewPaymentMethodsSettings']);
    Route::post('/save-school-payment-methods-settings', [SchoolSettingApiController::class, 'savePaymentMethodsSettings']);
    Route::get('/view-school-live-classes-settings', [SchoolSettingApiController::class, 'viewLiveClassesSettings']);
    Route::post('/save-school-live-classes-settings', [SchoolSettingApiController::class, 'saveLiveClassesSettings']);
    Route::get('/view-school-card-backgrounds-settings', [SchoolSettingApiController::class, 'viewCardBackgroundsSettings']);
    Route::post('/save-school-cards-backgrounds-settings', [SchoolSettingApiController::class, 'saveCardBackgroundsSettings']);
    Route::get('/view-school-examination-settings', [SchoolSettingApiController::class, 'viewExaminationSettings']);
    Route::post('/save-school-examination-settings', [SchoolSettingApiController::class, 'saveExaminationSettings']);

    // 03_Staff (Administrator)
    Route::get('/admins', [AdministratorApiController::class, 'adminsIndex']);
    Route::post('/admins', [AdministratorApiController::class, 'adminStore']);
    Route::put('/admins/{id}', [AdministratorApiController::class, 'adminUpdate']);
    Route::delete('/admins/{id}', [AdministratorApiController::class, 'adminDestroy']);

    Route::get('/roles', [AdministratorApiController::class, 'rolesIndex']);
    Route::post('/roles', [AdministratorApiController::class, 'roleStore']);
    Route::put('/roles/{id}', [AdministratorApiController::class, 'roleUpdate']);
    Route::delete('/roles/{id}', [AdministratorApiController::class, 'roleDestroy']);

    Route::get('/staff', [AdministratorApiController::class, 'staffIndex']);
    Route::post('/staff', [AdministratorApiController::class, 'staffStore']);
    Route::get('/staff/{id}', [AdministratorApiController::class, 'staffShow']);
    Route::put('/staff/{id}', [AdministratorApiController::class, 'staffUpdate']);
    Route::delete('/staff/{id}', [AdministratorApiController::class, 'staffDestroy']);
    Route::get('/staff-list', [AdministratorApiController::class, 'staffIndex']);

    Route::get('/staff/attendance', [AdministratorApiController::class, 'staffAttendanceIndex']);
    Route::post('/take-staff-attendance', [AdministratorApiController::class, 'staffAttendanceStore']);

    // 04_Academics
    // Class Groups
    Route::get('/class-groups', [ClassGroupApiController::class, 'index']);
    Route::post('/class-groups', [ClassGroupApiController::class, 'store']);
    Route::get('/class-groups/{id}', [ClassGroupApiController::class, 'show']);
    Route::put('/class-groups/{id}', [ClassGroupApiController::class, 'update']);
    Route::delete('/class-groups/{id}', [ClassGroupApiController::class, 'destroy']);

    // Classes & Sections
    Route::get('/classes', [SchoolClassApiController::class, 'index']);
    Route::get('/class-list', [SchoolClassApiController::class, 'index']);
    Route::get('/classes/{class_id}/sections', [SchoolClassApiController::class, 'listSections']);
    Route::get('/sections/{class_id}', [SchoolClassApiController::class, 'listSections']);
    Route::post('/sections', [SchoolClassApiController::class, 'storeSection']);
    Route::put('/sections/{id}', [SchoolClassApiController::class, 'updateSection']);
    Route::delete('/sections/{id}', [SchoolClassApiController::class, 'destroySection']);

    // Mediums
    Route::get('/mediums', [AcademicApiController::class, 'mediumsIndex']);
    Route::post('/mediums', [AcademicApiController::class, 'mediumStore']);
    Route::delete('/mediums/{id}', [AcademicApiController::class, 'mediumDestroy']);

    // Subjects
    Route::get('/subjects', [AcademicApiController::class, 'subjectsIndex']);
    Route::post('/subjects', [AcademicApiController::class, 'subjectStore']);
    Route::get('/subjects/{id}', [AcademicApiController::class, 'subjectShow']);
    Route::put('/subjects/{id}', [AcademicApiController::class, 'subjectUpdate']);
    Route::delete('/subjects/{id}', [AcademicApiController::class, 'subjectDestroy']);
    Route::post('/assign-staff-to-subject', [AcademicApiController::class, 'assignStaffToSubject']);

    // Subject Types
    Route::get('/subject-types', [AcademicApiController::class, 'subjectTypesIndex']);
    Route::post('/subject-types', [AcademicApiController::class, 'subjectTypeStore']);
    Route::delete('/subject-types/{id}', [AcademicApiController::class, 'subjectTypeDestroy']);

    // Study Materials
    Route::get('/study-materials', [AcademicApiController::class, 'studyMaterialsIndex']);
    Route::post('/study-materials', [AcademicApiController::class, 'studyMaterialStore']);
    Route::get('/study-materials/{id}', [AcademicApiController::class, 'studyMaterialShow']);
    Route::put('/study-materials/{id}', [AcademicApiController::class, 'studyMaterialUpdate']);
    Route::delete('/study-materials/{id}', [AcademicApiController::class, 'studyMaterialDestroy']);

    // Noticeboard
    Route::get('/notices', [AcademicApiController::class, 'noticeboardsIndex']);
    Route::post('/notices', [AcademicApiController::class, 'noticeboardStore']);
    Route::get('/notices/{id}', [AcademicApiController::class, 'noticeboardShow']);
    Route::put('/notices/{id}', [AcademicApiController::class, 'noticeboardUpdate']);
    Route::delete('/notices/{id}', [AcademicApiController::class, 'noticeboardDestroy']);

    // 05_Students
    Route::get('/student-list', [StudentApiController::class, 'index']);
    Route::get('/students-data', [StudentApiController::class, 'index']);
    Route::post('/save-student', [StudentApiController::class, 'store']);
    Route::get('/students/{id}', [StudentApiController::class, 'show']);
    Route::post('/promote-students', [StudentApiController::class, 'promote']);
    Route::post('/transfer-student', [StudentApiController::class, 'transfer']);
    Route::get('/students-transferred-to-other-school', [StudentApiController::class, 'transferList']);

    // 06_Accounting
    Route::get('/fee-types', [AccountingApiController::class, 'feeTypesIndex']);
    Route::post('/fee-types', [AccountingApiController::class, 'feeTypeStore']);
    Route::put('/fee-types/{id}', [AccountingApiController::class, 'feeTypeUpdate']);
    Route::delete('/fee-types/{id}', [AccountingApiController::class, 'feeTypeDestroy']);

    Route::get('/invoices', [AccountingApiController::class, 'invoicesIndex']);
    Route::post('/invoices', [AccountingApiController::class, 'invoiceStore']);
    Route::get('/invoices/{id}', [AccountingApiController::class, 'invoiceShow']);
    Route::put('/invoices/{id}', [AccountingApiController::class, 'invoiceUpdate']);
    Route::delete('/invoices/{id}', [AccountingApiController::class, 'invoiceDestroy']);

    Route::post('/payments', [AccountingApiController::class, 'collectPayment']);
    Route::get('/payments/history', [AccountingApiController::class, 'paymentHistory']);

    // 09_Exams
    Route::get('/exams', [ExaminationApiController::class, 'index']);
    Route::post('/exams', [ExaminationApiController::class, 'store']);
    Route::get('/exams/{id}', [ExaminationApiController::class, 'show']);
    Route::put('/exams/{id}', [ExaminationApiController::class, 'update']);
    Route::delete('/exams/{id}', [ExaminationApiController::class, 'destroy']);

    Route::get('/exam-groups', [ExaminationApiController::class, 'groupsIndex']);
    Route::post('/exam-groups', [ExaminationApiController::class, 'groupStore']);
    Route::put('/exam-groups/{id}', [ExaminationApiController::class, 'groupUpdate']);
    Route::delete('/exam-groups/{id}', [ExaminationApiController::class, 'groupDestroy']);

    Route::get('/view-admit-cards/{exam_id}', [ExaminationApiController::class, 'admitCardsIndex']);
    Route::post('/generate-admit-cards', [ExaminationApiController::class, 'generateAdmitCards']);
    Route::delete('/admit-cards/{id}', [ExaminationApiController::class, 'admitCardDestroy']);

    Route::get('/view-results', [ExaminationApiController::class, 'resultsIndex']);
    Route::post('/exam-results', [ExaminationApiController::class, 'resultStore']);
    Route::delete('/exam-results/{id}', [ExaminationApiController::class, 'resultDestroy']);

    // 16_Library
    Route::get('/books', [LibraryApiController::class, 'booksIndex']);
    Route::post('/books', [LibraryApiController::class, 'bookStore']);
    Route::put('/books/{id}', [LibraryApiController::class, 'bookUpdate']);
    Route::delete('/books/{id}', [LibraryApiController::class, 'bookDestroy']);

    Route::get('/books/issued', [LibraryApiController::class, 'bookIssuesIndex']);
    Route::post('/issue-book', [LibraryApiController::class, 'bookIssueStore']);
    Route::put('/books/issued/{id}', [LibraryApiController::class, 'bookIssueReturn']);
    Route::delete('/books/issued/{id}', [LibraryApiController::class, 'bookIssueDestroy']);

    Route::get('/library-cards', [LibraryApiController::class, 'libraryCardsIndex']);
    Route::post('/issue-library-card', [LibraryApiController::class, 'libraryCardStore']);
    Route::delete('/library-cards/{id}', [LibraryApiController::class, 'libraryCardDestroy']);

    // 21_Transport
    Route::get('/vehicles', [TransportApiController::class, 'vehiclesIndex']);
    Route::post('/vehicles', [TransportApiController::class, 'vehicleStore']);
    Route::put('/vehicles/{id}', [TransportApiController::class, 'vehicleUpdate']);
    Route::delete('/vehicles/{id}', [TransportApiController::class, 'vehicleDestroy']);

    Route::get('/transport-routes', [TransportApiController::class, 'routesIndex']);
    Route::post('/transport-routes', [TransportApiController::class, 'routeStore']);
    Route::put('/transport-routes/{id}', [TransportApiController::class, 'routeUpdate']);
    Route::delete('/transport-routes/{id}', [TransportApiController::class, 'routeDestroy']);
    Route::put('/assign-route-to-student', [TransportApiController::class, 'assignStudentToRoute']);
});

// =========================================================================
// STUDENT ROUTES (wp-json/sm/student)
// =========================================================================
Route::middleware('jwt.student')->group(function () {
    // 00_Auth
    Route::get('wp-json/sm/student/profile', [\App\Http\Controllers\Api\Student\StudentAuthController::class, 'profile']);
    Route::post('wp-json/sm/student/profile', [\App\Http\Controllers\Api\Student\StudentAuthController::class, 'updateProfile']);
    Route::post('wp-json/sm/account-settings', [\App\Http\Controllers\Api\Student\StudentAuthController::class, 'accountSettings']);
    Route::post('wp-json/sm/student/switch-session', [\App\Http\Controllers\Api\Student\StudentAuthController::class, 'switchSession']);

    // 01_Dashboard
    Route::get('wp-json/sm/student/dashboard', [\App\Http\Controllers\Api\Student\StudentDashboardController::class, 'dashboard']);
    Route::get('wp-json/sm/student/dashboard-settings', [\App\Http\Controllers\Api\Student\StudentDashboardController::class, 'dashboardSettings']);
    Route::get('wp-json/sm/notifications/tokens', [\App\Http\Controllers\Api\Student\StudentDashboardController::class, 'getNotificationTokens']);
    Route::post('wp-json/sm/notifications/tokens', [\App\Http\Controllers\Api\Student\StudentDashboardController::class, 'saveNotificationToken']);

    // 02_School
    Route::get('wp-json/sm/student/about-school', [\App\Http\Controllers\Api\Student\StudentAcademicController::class, 'aboutSchool']);

    // 04_Academic
    Route::get('wp-json/sm/student/subjects', [\App\Http\Controllers\Api\Student\StudentAcademicController::class, 'subjects']);
    Route::get('wp-json/sm/student/class-time-table', [\App\Http\Controllers\Api\Student\StudentAcademicController::class, 'classTimeTable']);
    Route::post('wp-json/sm/student/submit-staff-rating', [\App\Http\Controllers\Api\Student\StudentAcademicController::class, 'submitStaffRating']);
    Route::get('wp-json/sm/student/id-card', [\App\Http\Controllers\Api\Student\StudentAcademicController::class, 'idCard']);
    Route::get('wp-json/sm/student/live-classes', [\App\Http\Controllers\Api\Student\StudentAcademicController::class, 'liveClasses']);
    Route::get('wp-json/sm/student/stationary-issued', [\App\Http\Controllers\Api\Student\StudentAcademicController::class, 'stationary']);

    // 06_Accounting
    Route::get('wp-json/sm/student/fee-structure', [\App\Http\Controllers\Api\Student\StudentAccountingController::class, 'feeStructure']);
    Route::get('wp-json/sm/student/session-fee-summary', [\App\Http\Controllers\Api\Student\StudentAccountingController::class, 'sessionFeeSummary']);
    Route::get('wp-json/sm/student/fee-invoices', [\App\Http\Controllers\Api\Student\StudentAccountingController::class, 'feeInvoices']);
    Route::get('wp-json/sm/student/fee-invoices/{id}', [\App\Http\Controllers\Api\Student\StudentAccountingController::class, 'feeInvoiceDetails']);
    Route::post('wp-json/sm/student/payments', [\App\Http\Controllers\Api\Student\StudentAccountingController::class, 'payInvoice']);
    Route::get('wp-json/sm/student/payments', [\App\Http\Controllers\Api\Student\StudentAccountingController::class, 'paymentHistory']);
    Route::get('wp-json/sm/student/payments/{id}', [\App\Http\Controllers\Api\Student\StudentAccountingController::class, 'paymentReceipt']);

    // 08_Attendance
    Route::get('wp-json/sm/student/attendance', [\App\Http\Controllers\Api\Student\StudentAttendanceController::class, 'attendance']);

    // 09_Examination
    Route::get('wp-json/sm/student/exam-time-table', [\App\Http\Controllers\Api\Student\StudentExaminationController::class, 'examTimeTable']);
    Route::get('wp-json/sm/student/exam-time-table/{id}', [\App\Http\Controllers\Api\Student\StudentExaminationController::class, 'examTimeTableDetails']);
    Route::get('wp-json/sm/student/admit-cards', [\App\Http\Controllers\Api\Student\StudentExaminationController::class, 'admitCards']);
    Route::get('wp-json/sm/student/admit-cards/{id}', [\App\Http\Controllers\Api\Student\StudentExaminationController::class, 'admitCardDetails']);
    Route::get('wp-json/sm/student/exam-results', [\App\Http\Controllers\Api\Student\StudentExaminationController::class, 'examResults']);
    Route::get('wp-json/sm/student/exam-results/{id}', [\App\Http\Controllers\Api\Student\StudentExaminationController::class, 'examResultDetails']);
    Route::get('wp-json/sm/student/overall-result', [\App\Http\Controllers\Api\Student\StudentExaminationController::class, 'overallResult']);

    // 10_Certificates
    Route::get('wp-json/sm/student/certificates', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'certificates']);
    Route::get('wp-json/sm/student/certificates/{id}/generate', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'generateCertificate']);

    // 12_Homework
    Route::get('wp-json/sm/student/homeworks', [\App\Http\Controllers\Api\Student\StudentHomeworkController::class, 'homeworks']);
    Route::get('wp-json/sm/student/homeworks/{id}', [\App\Http\Controllers\Api\Student\StudentHomeworkController::class, 'homeworkDetails']);
    Route::post('wp-json/sm/student/homeworks/{id}/submissions', [\App\Http\Controllers\Api\Student\StudentHomeworkController::class, 'submitHomework']);

    // 14_Leave Request
    Route::get('wp-json/sm/student/leave-requests', [\App\Http\Controllers\Api\Student\StudentLeaveController::class, 'leaveRequests']);
    Route::post('wp-json/sm/student/leave-requests', [\App\Http\Controllers\Api\Student\StudentLeaveController::class, 'submitLeaveRequest']);

    // 15_Lessons
    Route::get('wp-json/sm/student/lessons', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'lessons']);
    Route::get('wp-json/sm/student/chapters', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'chapters']);
    Route::get('wp-json/sm/student/lessons/{id}', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'lessonDetails']);

    // 16_Library
    Route::get('wp-json/sm/student/books-issued', [\App\Http\Controllers\Api\Student\StudentLibraryController::class, 'booksIssued']);
    Route::get('wp-json/sm/student/books-issued/{id}', [\App\Http\Controllers\Api\Student\StudentLibraryController::class, 'bookIssuedDetails']);

    // 19_Study Materials
    Route::get('wp-json/sm/student/study-materials', [\App\Http\Controllers\Api\Student\StudentAcademicController::class, 'studyMaterials']);
    Route::get('wp-json/sm/student/study-materials/{id}', [\App\Http\Controllers\Api\Student\StudentAcademicController::class, 'studyMaterialDetails']);

    // 20_Tickets
    Route::get('wp-json/sm/student/tickets', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'tickets']);
    Route::post('wp-json/sm/student/tickets', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'submitTicket']);
    Route::get('wp-json/sm/student/tickets/{id}/history', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'ticketHistory']);

    // 21_Transport
    Route::get('wp-json/sm/student/transport', [\App\Http\Controllers\Api\Student\StudentTransportController::class, 'transport']);

    // 22_SM Chat
    Route::get('wp-json/sm/student/chat/teachers', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'chatTeachers']);
    Route::get('wp-json/sm/student/chat/conversations', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'chatConversations']);
    Route::get('wp-json/sm/student/chat/conversations/{id}/messages', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'chatMessages']);
    Route::post('wp-json/sm/student/chat/conversations/{id}/messages', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'sendChatMessage']);
    Route::post('wp-json/sm/student/chat/conversations', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'createChatConversation']);
    Route::put('wp-json/sm/student/chat/messages/{id}', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'editChatMessage']);
    Route::delete('wp-json/sm/student/chat/messages/{id}', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'deleteChatMessage']);

    // 23_Communications
    Route::get('wp-json/sm/student/noticeboard', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'noticeboard']);
    Route::get('wp-json/sm/student/events', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'events']);
    Route::get('wp-json/sm/student/events/{id}', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'eventDetails']);
    Route::post('wp-json/sm/student/events/{id}/join', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'joinEvent']);
    Route::post('wp-json/sm/student/events/{id}/unjoin', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'unjoinEvent']);
    Route::get('wp-json/sm/student/calendar', [\App\Http\Controllers\Api\Student\StudentCommunicationController::class, 'calendar']);
});
