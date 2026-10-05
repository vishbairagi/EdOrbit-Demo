<?php

use App\Http\Controllers\AcademicSessionController;
use App\Http\Controllers\AccountingDashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassGroupController;
use App\Http\Controllers\ExaminationController;
use App\Http\Controllers\ExaminationWorkflowController;
use App\Http\Controllers\FeeInvoiceController;
use App\Http\Controllers\FeeTypeController;
use App\Http\Controllers\MediumController;
use App\Http\Controllers\PrintIdCardController;
use App\Http\Controllers\PromoteStudentController;
use App\Http\Controllers\SchoolAccountingSettingsController;
use App\Http\Controllers\SchoolClassController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\SchoolManagementDashboardController;
use App\Http\Controllers\SchoolManagementSettingsController;
use App\Http\Controllers\SmAcademicClassSectionController;
use App\Http\Controllers\SmAcademicClassTeacherController;
use App\Http\Controllers\SmAcademicDashboardController;
use App\Http\Controllers\SmAdminController;
use App\Http\Controllers\SmAdministratorDashboardController;
use App\Http\Controllers\SmRoleController;
use App\Http\Controllers\SMSchoolController;
use App\Http\Controllers\SmStaffAttendanceController;
use App\Http\Controllers\SmStaffController;
use App\Http\Controllers\SmStaffIdCardController;
use App\Http\Controllers\SmStaffLeaveController;
use App\Http\Controllers\SmStaffPayrollController;
use App\Http\Controllers\SmUsersController;
use App\Http\Controllers\StaffDashboardController;
use App\Http\Controllers\StudentCategoryController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentTypeController;
use App\Http\Controllers\StudyMaterialController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\SubjectTypeController;
use App\Http\Controllers\AssignSubjectController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\TransferStudentController;
use App\Models\School;
use Illuminate\Support\Facades\Route;

// ── Auth ──
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return redirect()->route('login');
});

Route::prefix('school-management')->name('school-management.')->group(function () {
    Route::get('/dashboard', [SchoolManagementDashboardController::class, 'index'])->name('dashboard');

    Route::resource('schools', SchoolController::class);
    Route::post('/schools/bulk-delete', [SchoolController::class, 'bulkDestroy'])->name('schools.bulk-destroy');
    Route::resource('sessions', AcademicSessionController::class)->parameters([
        'sessions' => 'academic_session',
    ]);
    Route::post('/sessions/bulk-delete', [AcademicSessionController::class, 'bulkDestroy'])->name('sessions.bulk-destroy');

    Route::get('/settings', [SchoolManagementSettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SchoolManagementSettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/reset', [SchoolManagementSettingsController::class, 'resetSystem'])->name('settings.reset');
    Route::post('/settings/demo-data', [SchoolManagementSettingsController::class, 'generateDemoData'])->name('settings.demo-data');
    Route::post('/settings/clean-demo', [SchoolManagementSettingsController::class, 'cleanDemoData'])->name('settings.clean-demo');

    Route::get('/schools/{school}/switch', function (School $school) {
        session(['selected_school_id' => $school->id]);

        return redirect()->route('sm-school.dashboard');
    })->name('schools.switch');
});

Route::prefix('sm-school')->name('sm-school.')->middleware('permission')->group(function () {
    Route::get('/dashboard', [SMSchoolController::class, 'dashboard'])->name('dashboard');

    // Class Groups
    Route::get('/class-groups/export', [ClassGroupController::class, 'export'])->name('class-groups.export');
    Route::get('/class-groups/import/demo', [ClassGroupController::class, 'importDemo'])->name('class-groups.import-demo');
    Route::post('/class-groups/import', [ClassGroupController::class, 'import'])->name('class-groups.import');
    Route::get('/class-groups', [ClassGroupController::class, 'index'])->name('class-groups.index');
    Route::post('/class-groups', [ClassGroupController::class, 'store'])->name('class-groups.store');
    Route::put('/class-groups/{classGroup}', [ClassGroupController::class, 'update'])->name('class-groups.update');
    Route::patch('/class-groups/{classGroup}/status', [ClassGroupController::class, 'updateStatus'])->name('class-groups.update-status');
    Route::delete('/class-groups/{classGroup}', [ClassGroupController::class, 'destroy'])->name('class-groups.destroy');
    Route::post('/class-groups/bulk-delete', [ClassGroupController::class, 'bulkDestroy'])->name('class-groups.bulk-destroy');

    // Classes
    Route::get('/classes/export', [SchoolClassController::class, 'export'])->name('classes.export');
    Route::get('/classes/import/demo', [SchoolClassController::class, 'importDemo'])->name('classes.import-demo');
    Route::post('/classes/import', [SchoolClassController::class, 'import'])->name('classes.import');
    Route::get('/classes', [SchoolClassController::class, 'index'])->name('classes.index');
    Route::post('/classes', [SchoolClassController::class, 'store'])->name('classes.store');
    Route::put('/classes/{schoolClass}', [SchoolClassController::class, 'update'])->name('classes.update');
    Route::delete('/classes/{schoolClass}', [SchoolClassController::class, 'destroy'])->name('classes.destroy');
    Route::post('/classes/bulk-delete', [SchoolClassController::class, 'bulkDestroy'])->name('classes.bulk-destroy');

    // Sections
    Route::post('/classes/{schoolClass}/sections', [SchoolClassController::class, 'addSection'])->name('classes.sections.store');
    Route::delete('/sections/{section}', [SchoolClassController::class, 'destroySection'])->name('sections.destroy');

    // School Settings
    Route::get('/settings', [StudentCategoryController::class, 'index'])->name('settings.index');
    Route::post('/settings/categories', [StudentCategoryController::class, 'store'])->name('settings.categories.store');
    Route::put('/settings/categories/{studentCategory}', [StudentCategoryController::class, 'update'])->name('settings.categories.update');
    Route::delete('/settings/categories/{studentCategory}', [StudentCategoryController::class, 'destroy'])->name('settings.categories.destroy');
    Route::post('/settings/student-types', [StudentTypeController::class, 'store'])->name('settings.student-types.store');
    Route::put('/settings/student-types/{studentType}', [StudentTypeController::class, 'update'])->name('settings.student-types.update');
    Route::delete('/settings/student-types/{studentType}', [StudentTypeController::class, 'destroy'])->name('settings.student-types.destroy');
    Route::post('/settings/fee-periods', [SchoolAccountingSettingsController::class, 'storePeriod'])->name('settings.fee-periods.store');
    Route::put('/settings/fee-periods/{feePeriod}', [SchoolAccountingSettingsController::class, 'updatePeriod'])->name('settings.fee-periods.update');
    Route::delete('/settings/fee-periods/{feePeriod}', [SchoolAccountingSettingsController::class, 'destroyPeriod'])->name('settings.fee-periods.destroy');
    Route::post('/settings/payment-methods', [SchoolAccountingSettingsController::class, 'storePaymentMethod'])->name('settings.payment-methods.store');
    Route::put('/settings/payment-methods/{paymentMethod}', [SchoolAccountingSettingsController::class, 'updatePaymentMethod'])->name('settings.payment-methods.update');
    Route::delete('/settings/payment-methods/{paymentMethod}', [SchoolAccountingSettingsController::class, 'destroyPaymentMethod'])->name('settings.payment-methods.destroy');
    Route::post('/settings/update', [\App\Http\Controllers\SchoolSettingController::class, 'update'])->name('settings.update');
    Route::post('/settings/test-email', [\App\Http\Controllers\SchoolSettingController::class, 'testEmail'])->name('settings.test-email');
});

Route::prefix('accounting')->name('accounting.')->middleware('permission')->group(function () {
    Route::get('/dashboard', [AccountingDashboardController::class, 'index'])->name('dashboard');

    Route::get('/fee-types/export', [FeeTypeController::class, 'export'])->name('fee-types.export');
    Route::get('/fee-types/sample', [FeeTypeController::class, 'downloadSample'])->name('fee-types.sample');
    Route::post('/fee-types/import', [FeeTypeController::class, 'import'])->name('fee-types.import');
    Route::get('/fee-types', [FeeTypeController::class, 'index'])->name('fee-types.index');
    Route::get('/fee-types/create', [FeeTypeController::class, 'create'])->name('fee-types.create');
    Route::get('/fee-types/sessions/{academicSession}/classes', [FeeTypeController::class, 'classes'])->name('fee-types.classes');
    Route::get('/fee-types/classes/{schoolClass}', [FeeTypeController::class, 'forClass'])->name('fee-types.for-class');
    Route::get('/fee-types/{feeType}/edit', [FeeTypeController::class, 'edit'])->name('fee-types.edit');
    Route::post('/fee-types', [FeeTypeController::class, 'store'])->name('fee-types.store');
    Route::put('/fee-types/{feeType}', [FeeTypeController::class, 'update'])->name('fee-types.update');
    Route::delete('/fee-types/{feeType}', [FeeTypeController::class, 'destroy'])->name('fee-types.destroy');
    Route::post('/fee-types/bulk-delete', [FeeTypeController::class, 'bulkDestroy'])->name('fee-types.bulk-destroy');

    Route::get('/fee-invoices/export', [FeeInvoiceController::class, 'export'])->name('fee-invoices.export');
    Route::get('/fee-invoices/sample', [FeeInvoiceController::class, 'downloadSample'])->name('fee-invoices.sample');
    Route::post('/fee-invoices/import', [FeeInvoiceController::class, 'import'])->name('fee-invoices.import');
    Route::get('/fee-invoices', [FeeInvoiceController::class, 'index'])->name('fee-invoices.index');
    Route::get('/fee-invoices/create', [FeeInvoiceController::class, 'create'])->name('fee-invoices.create');
    Route::post('/fee-invoices', [FeeInvoiceController::class, 'store'])->name('fee-invoices.store');
    Route::get('/fee-invoices/classes/{schoolClass}/sections', [FeeInvoiceController::class, 'sections'])->name('fee-invoices.sections');
    Route::get('/fee-invoices/classes/{schoolClass}/students', [FeeInvoiceController::class, 'students'])->name('fee-invoices.students');
    Route::get('/fee-invoices/{feeInvoice}', [FeeInvoiceController::class, 'show'])->name('fee-invoices.show');
    Route::get('/fee-invoices/{feeInvoice}/edit', [FeeInvoiceController::class, 'edit'])->name('fee-invoices.edit');
    Route::put('/fee-invoices/{feeInvoice}', [FeeInvoiceController::class, 'update'])->name('fee-invoices.update');
    Route::delete('/fee-invoices/{feeInvoice}', [FeeInvoiceController::class, 'destroy'])->name('fee-invoices.destroy');
    Route::post('/fee-invoices/bulk-delete', [FeeInvoiceController::class, 'bulkDestroy'])->name('fee-invoices.bulk-destroy');
    Route::get('/fee-invoices/{feeInvoice}/print', [FeeInvoiceController::class, 'print'])->name('fee-invoices.print');
    Route::post('/fee-invoices/{feeInvoice}/payments', [FeeInvoiceController::class, 'collect'])->name('fee-invoices.payments.store');
    Route::get('/collect-payment', [FeeInvoiceController::class, 'collectPaymentIndex'])->name('collect-payment.index');
    Route::post('/collect-payment/bulk-print', [FeeInvoiceController::class, 'collectPaymentBulkPrint'])->name('collect-payment.bulk-print');
    Route::post('/collect-payment/bulk-delete', [FeeInvoiceController::class, 'collectPaymentBulkDestroy'])->name('collect-payment.bulk-destroy');
    Route::get('/collect-payment/payments/{feePayment}/receipt', [FeeInvoiceController::class, 'printPaymentReceipt'])->name('collect-payment.receipt');

    Route::post('/bulk-invoice-print/print', [FeeInvoiceController::class, 'printSelected'])->name('bulk-invoice-print.print');

    // Transport Invoices
    Route::post('/transport-invoices/bulk-delete', [\App\Http\Controllers\TransportInvoiceController::class, 'bulkDestroy'])->name('transport-invoices.bulk-delete');
    Route::post('/transport-invoices/bulk-print', [\App\Http\Controllers\TransportInvoiceController::class, 'printSelected'])->name('transport-invoices.bulk-print');
    Route::get('/transport-invoices', [\App\Http\Controllers\TransportInvoiceController::class, 'index'])->name('transport-invoices.index');
    Route::get('/transport-invoices/create', [\App\Http\Controllers\TransportInvoiceController::class, 'create'])->name('transport-invoices.create');
    Route::post('/transport-invoices', [\App\Http\Controllers\TransportInvoiceController::class, 'store'])->name('transport-invoices.store');
    Route::get('/transport-invoices/{transportInvoice}/print', [\App\Http\Controllers\TransportInvoiceController::class, 'print'])->name('transport-invoices.print');
    Route::get('/transport-invoices/{transportInvoice}', [\App\Http\Controllers\TransportInvoiceController::class, 'show'])->name('transport-invoices.show');
    Route::post('/transport-invoices/{transportInvoice}/payments', [\App\Http\Controllers\TransportInvoiceController::class, 'collect'])->name('transport-invoices.payments.store');
});

// ── Transport ──
Route::prefix('transport')->name('transport.')->middleware('permission')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\TransportController::class, 'dashboard'])->name('dashboard');
    Route::resource('vehicles', \App\Http\Controllers\VehicleController::class);
    Route::post('/vehicles/bulk-delete', [\App\Http\Controllers\VehicleController::class, 'bulkDestroy'])->name('vehicles.bulk-destroy');
    Route::resource('routes', \App\Http\Controllers\TransportRouteController::class);
    Route::post('/routes/bulk-delete', [\App\Http\Controllers\TransportRouteController::class, 'bulkDestroy'])->name('routes.bulk-destroy');
    Route::get('/assign-students', [\App\Http\Controllers\TransportAssignController::class, 'index'])->name('assign-students.index');
    Route::post('/assign-students/{student}', [\App\Http\Controllers\TransportAssignController::class, 'assign'])->name('assign-students.assign');
    Route::get('/report', [\App\Http\Controllers\TransportController::class, 'report'])->name('report');
});

// ── Academic ──
Route::prefix('academic')->name('academic.')->middleware('permission')->group(function () {

    Route::get('/dashboard', [SmAcademicDashboardController::class, 'index'])->name('dashboard');

    // Class Teachers
    Route::get('/class-teachers/export', [SmAcademicClassTeacherController::class, 'export'])->name('class-teachers.export');
    Route::get('/class-teachers/import/demo', [SmAcademicClassTeacherController::class, 'importDemo'])->name('class-teachers.import-demo');
    Route::post('/class-teachers/import', [SmAcademicClassTeacherController::class, 'import'])->name('class-teachers.import');
    Route::get('/class-teachers', [SmAcademicClassTeacherController::class, 'index'])->name('class-teachers.index');
    Route::post('/class-teachers', [SmAcademicClassTeacherController::class, 'store'])->name('class-teachers.store');
    Route::put('/class-teachers/{classTeacher}', [SmAcademicClassTeacherController::class, 'update'])->name('class-teachers.update');
    Route::delete('/class-teachers/{classTeacher}', [SmAcademicClassTeacherController::class, 'destroy'])->name('class-teachers.destroy');
    Route::post('/class-teachers/bulk-delete', [SmAcademicClassTeacherController::class, 'bulkDestroy'])->name('class-teachers.bulk-destroy');

    // Class Sections (academic view)
    Route::get('/class-sections/export', [SmAcademicClassSectionController::class, 'export'])->name('class-sections.export');
    Route::get('/class-sections/import/demo', [SmAcademicClassSectionController::class, 'importDemo'])->name('class-sections.import-demo');
    Route::post('/class-sections/import', [SmAcademicClassSectionController::class, 'import'])->name('class-sections.import');
    Route::get('/class-sections', [SmAcademicClassSectionController::class, 'index'])->name('class-sections.index');
    Route::post('/class-sections/{schoolClass}/sections', [SmAcademicClassSectionController::class, 'addSection'])->name('class-sections.sections.store');
    Route::delete('/class-sections/sections/{section}', [SmAcademicClassSectionController::class, 'destroySection'])->name('class-sections.sections.destroy');

    // Mediums
    Route::get('/mediums/export', [MediumController::class, 'export'])->name('mediums.export');
    Route::get('/mediums/import/demo', [MediumController::class, 'importDemo'])->name('mediums.import-demo');
    Route::post('/mediums/import', [MediumController::class, 'import'])->name('mediums.import');
    Route::get('/mediums', [MediumController::class, 'index'])->name('mediums.index');
    Route::post('/mediums', [MediumController::class, 'store'])->name('mediums.store');
    Route::put('/mediums/{medium}', [MediumController::class, 'update'])->name('mediums.update');
    Route::patch('/mediums/{medium}/status', [MediumController::class, 'updateStatus'])->name('mediums.update-status');
    Route::delete('/mediums/{medium}', [MediumController::class, 'destroy'])->name('mediums.destroy');
    Route::post('/mediums/bulk-delete', [MediumController::class, 'bulkDestroy'])->name('mediums.bulk-destroy');

    // Subject Types
    Route::get('/subject-types/export', [SubjectTypeController::class, 'export'])->name('subject-types.export');
    Route::get('/subject-types/import/demo', [SubjectTypeController::class, 'importDemo'])->name('subject-types.import-demo');
    Route::post('/subject-types/import', [SubjectTypeController::class, 'import'])->name('subject-types.import');
    Route::get('/subject-types', [SubjectTypeController::class, 'index'])->name('subject-types.index');
    Route::post('/subject-types', [SubjectTypeController::class, 'store'])->name('subject-types.store');
    Route::put('/subject-types/{subjectType}', [SubjectTypeController::class, 'update'])->name('subject-types.update');
    Route::patch('/subject-types/{subjectType}/status', [SubjectTypeController::class, 'updateStatus'])->name('subject-types.update-status');
    Route::delete('/subject-types/{subjectType}', [SubjectTypeController::class, 'destroy'])->name('subject-types.destroy');
    Route::post('/subject-types/bulk-delete', [SubjectTypeController::class, 'bulkDestroy'])->name('subject-types.bulk-destroy');

    // Subjects
    Route::get('/subjects/export', [SubjectController::class, 'export'])->name('subjects.export');
    Route::get('/subjects/import/demo', [SubjectController::class, 'importDemo'])->name('subjects.import-demo');
    Route::post('/subjects/import', [SubjectController::class, 'import'])->name('subjects.import');
    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
    Route::post('/subjects', [SubjectController::class, 'store'])->name('subjects.store');
    Route::put('/subjects/{subject}', [SubjectController::class, 'update'])->name('subjects.update');
    Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy'])->name('subjects.destroy');
    Route::post('/subjects/bulk-delete', [SubjectController::class, 'bulkDestroy'])->name('subjects.bulk-destroy');

    // Assign Subjects
    Route::get('/assign-subjects/export', [AssignSubjectController::class, 'export'])->name('assign-subjects.export');
    Route::get('/assign-subjects/import/demo', [AssignSubjectController::class, 'importDemo'])->name('assign-subjects.import-demo');
    Route::post('/assign-subjects/import', [AssignSubjectController::class, 'import'])->name('assign-subjects.import');
    Route::get('/assign-subjects', [AssignSubjectController::class, 'index'])->name('assign-subjects.index');
    Route::get('/assign-subjects/{subject}/teachers', [AssignSubjectController::class, 'teachers'])->name('assign-subjects.teachers');
    Route::post('/assign-subjects/{subject}/teachers', [AssignSubjectController::class, 'assign'])->name('assign-subjects.assign');
    Route::delete('/assign-subjects/{subject}/teachers/{staff}', [AssignSubjectController::class, 'remove'])->name('assign-subjects.remove');

    // Student Leaves
    Route::get('/student-leaves/export', [\App\Http\Controllers\SmAcademicStudentLeaveController::class, 'export'])->name('student-leaves.export');
    Route::get('/student-leaves/import/demo', [\App\Http\Controllers\SmAcademicStudentLeaveController::class, 'importDemo'])->name('student-leaves.import-demo');
    Route::post('/student-leaves/import', [\App\Http\Controllers\SmAcademicStudentLeaveController::class, 'import'])->name('student-leaves.import');
    Route::get('/student-leaves/students', [\App\Http\Controllers\SmAcademicStudentLeaveController::class, 'getStudents'])->name('student-leaves.students');
    Route::resource('student-leaves', \App\Http\Controllers\SmAcademicStudentLeaveController::class)->parameters(['student-leaves' => 'studentLeave'])->except(['show', 'edit']);
    Route::post('/student-leaves/bulk-delete', [\App\Http\Controllers\SmAcademicStudentLeaveController::class, 'bulkDestroy'])->name('student-leaves.bulk-destroy');

    // Study Materials
    Route::get('/study-materials/export', [StudyMaterialController::class, 'export'])->name('study-materials.export');
    Route::get('/study-materials/import/demo', [StudyMaterialController::class, 'importDemo'])->name('study-materials.import-demo');
    Route::post('/study-materials/import', [StudyMaterialController::class, 'import'])->name('study-materials.import');
    Route::get('/study-materials', [StudyMaterialController::class, 'index'])->name('study-materials.index');
    Route::get('/study-materials/create', [StudyMaterialController::class, 'create'])->name('study-materials.create');
    Route::post('/study-materials', [StudyMaterialController::class, 'store'])->name('study-materials.store');
    Route::put('/study-materials/{studyMaterial}', [StudyMaterialController::class, 'update'])->name('study-materials.update');
    Route::delete('/study-materials/{studyMaterial}', [StudyMaterialController::class, 'destroy'])->name('study-materials.destroy');
    Route::post('/study-materials/bulk-delete', [StudyMaterialController::class, 'bulkDestroy'])->name('study-materials.bulk-destroy');
    Route::get('/study-materials/sections/{schoolClass}', [StudyMaterialController::class, 'getSections'])->name('study-materials.sections');
});

// ── Examination ──
Route::prefix('examination')->name('examination.')->middleware('permission')->group(function () {
    Route::get('/dashboard', [ExaminationController::class, 'dashboard'])->name('dashboard');

    Route::post('/exams/bulk-delete', [ExaminationController::class, 'bulkDestroyExam'])->name('exams.bulk-destroy');
    Route::get('/exams', [ExaminationController::class, 'index'])->name('exams.index');
    Route::get('/exams/create', [ExaminationController::class, 'create'])->name('exams.create');
    Route::post('/exams', [ExaminationController::class, 'store'])->name('exams.store');
    Route::get('/exams/{exam}/edit', [ExaminationController::class, 'edit'])->name('exams.edit');
    Route::put('/exams/{exam}', [ExaminationController::class, 'update'])->name('exams.update');
    Route::get('/classes/{schoolClass}/subjects', [ExaminationController::class, 'subjects'])->name('classes.subjects');

    Route::post('/groups/bulk-delete', [ExaminationController::class, 'bulkDestroyGroup'])->name('groups.bulk-destroy');
    Route::get('/groups', [ExaminationController::class, 'groups'])->name('groups.index');
    Route::post('/groups', [ExaminationController::class, 'storeGroup'])->name('groups.store');
    Route::put('/groups/{examGroup}', [ExaminationController::class, 'updateGroup'])->name('groups.update');
    Route::delete('/groups/{examGroup}', [ExaminationController::class, 'destroyGroup'])->name('groups.destroy');

    Route::get('/admit-cards', [ExaminationWorkflowController::class, 'admitCards'])->name('admit-cards.index');
    Route::get('/admit-cards/{exam}', [ExaminationWorkflowController::class, 'viewAdmitCards'])->name('admit-cards.view');
    Route::post('/admit-cards/{exam}/bulk-delete', [ExaminationWorkflowController::class, 'bulkDeleteAdmitCards'])->name('admit-cards.bulk-delete');
    Route::put('/admit-cards/{exam}/cards/{admitCard}', [ExaminationWorkflowController::class, 'updateAdmitCard'])->name('admit-cards.update');
    Route::delete('/admit-cards/{exam}/cards/{admitCard}', [ExaminationWorkflowController::class, 'deleteAdmitCard'])->name('admit-cards.delete');
    Route::get('/admit-cards/{exam}/generate', [ExaminationWorkflowController::class, 'generateAdmitCardsForm'])->name('admit-cards.generate-form');
    Route::post('/admit-cards/{exam}/generate', [ExaminationWorkflowController::class, 'generateAdmitCards'])->name('admit-cards.generate');
    Route::get('/admit-cards/{exam}/print', [ExaminationWorkflowController::class, 'printAdmitCards'])->name('admit-cards.print');

    Route::get('/results', [ExaminationWorkflowController::class, 'results'])->name('results.index');
    Route::get('/results/{exam}/generate', [ExaminationWorkflowController::class, 'generateResultsForm'])->name('results.generate-form');
    Route::get('/results/{exam}', [ExaminationWorkflowController::class, 'viewResults'])->name('results.view');
    Route::post('/results', [ExaminationWorkflowController::class, 'saveResults'])->name('results.store');
    Route::post('/results/{exam}/bulk-delete', [ExaminationWorkflowController::class, 'bulkDeleteResults'])->name('results.bulk-destroy');
    Route::delete('/results/{exam}/{result}', [ExaminationWorkflowController::class, 'deleteResult'])->name('results.destroy');
    Route::get('/reports/academic', [ExaminationWorkflowController::class, 'academicReport'])->name('reports.academic');
});

// ── SM Student ──
Route::prefix('sm-student')->name('sm-student.')->middleware('permission')->group(function () {

    Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('dashboard');

    // Students CRUD
    Route::get('/students/export', [StudentController::class, 'export'])->name('students.export');
    Route::get('/students/import/demo', [StudentController::class, 'importDemo'])->name('students.import-demo');
    Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');
    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
    Route::post('/students', [StudentController::class, 'store'])->name('students.store');
    Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
    Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
    Route::patch('/students/{student}/status', [StudentController::class, 'updateStatus'])->name('students.update-status');
    Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
    Route::post('/students/bulk-delete', [StudentController::class, 'bulkDestroy'])->name('students.bulk-destroy');

    // AJAX helpers
    Route::get('/classes/{schoolClass}/sections', [StudentController::class, 'getSections'])->name('students.sections');
    Route::get('/classes/{schoolClass}/subjects', [StudentController::class, 'getSubjects'])->name('students.subjects');
    Route::get('/classes/{schoolClass}/fee-types', [FeeTypeController::class, 'forClass'])->name('students.fee-types');

    // Print ID Cards
    Route::get('/print-id-cards', [PrintIdCardController::class, 'index'])->name('print-id-cards.index');
    Route::get('/print-id-cards/selected', [PrintIdCardController::class, 'printSelected'])->name('print-id-cards.selected');
    Route::get('/print-id-cards/staff/{staff}/print', [PrintIdCardController::class, 'printStaff'])->name('print-id-cards.staff.print');
    Route::get('/print-id-cards/{student}/print', [PrintIdCardController::class, 'print'])->name('print-id-cards.print');

    // Promote Students
    Route::get('/promote', [PromoteStudentController::class, 'index'])->name('promote.index');
    Route::get('/promote/classes/{academicSession}', [PromoteStudentController::class, 'getClassesBySession'])->name('promote.classes');
    Route::post('/promote/students', [PromoteStudentController::class, 'getStudents'])->name('promote.students');
    Route::post('/promote', [PromoteStudentController::class, 'promote'])->name('promote.store');

    // Transfer Students & Certificate
    Route::get('/transfer', [TransferStudentController::class, 'index'])->name('transfer.index');
    Route::post('/transfer/{student}', [TransferStudentController::class, 'transfer'])->name('transfer.store');
    Route::get('/transfer/{student}/certificate', [TransferStudentController::class, 'showCertificate'])->name('transfer.certificate');
});

// ── SM Administrator ──
Route::prefix('sm-administrator')->name('sm-administrator.')->middleware('permission')->group(function () {

    Route::get('/dashboard', [SmAdministratorDashboardController::class, 'index'])->name('dashboard');

    // Admins (Admin/Principal)
    Route::get('/admins/export', [SmAdminController::class, 'export'])->name('admins.export');
    Route::get('/admins/import/demo', [SmAdminController::class, 'importDemo'])->name('admins.import-demo');
    Route::post('/admins/import', [SmAdminController::class, 'import'])->name('admins.import');
    Route::get('/admins', [SmAdminController::class, 'index'])->name('admins.index');
    Route::get('/admins/create', [SmAdminController::class, 'create'])->name('admins.create');
    Route::post('/admins', [SmAdminController::class, 'store'])->name('admins.store');
    Route::get('/admins/{admin}/edit', [SmAdminController::class, 'edit'])->name('admins.edit');
    Route::put('/admins/{admin}', [SmAdminController::class, 'update'])->name('admins.update');
    Route::patch('/admins/{admin}/status', [SmAdminController::class, 'updateStatus'])->name('admins.update-status');
    Route::delete('/admins/{admin}', [SmAdminController::class, 'destroy'])->name('admins.destroy');
    Route::post('/admins/bulk-delete', [SmAdminController::class, 'bulkDestroy'])->name('admins.bulk-destroy');

    // Roles
    Route::get('/roles', [SmRoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [SmRoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [SmRoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}/edit', [SmRoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{role}', [SmRoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [SmRoleController::class, 'destroy'])->name('roles.destroy');
    Route::post('/roles/bulk-delete', [SmRoleController::class, 'bulkDestroy'])->name('roles.bulk-destroy');
    Route::get('/roles/{role}/permissions', [SmRoleController::class, 'getPermissions'])->name('roles.permissions');

    // Staff
    Route::get('/staff/export', [SmStaffController::class, 'export'])->name('staff.export');
    Route::get('/staff/import/demo', [SmStaffController::class, 'importDemo'])->name('staff.import-demo');
    Route::post('/staff/import', [SmStaffController::class, 'import'])->name('staff.import');
    Route::get('/staff', [SmStaffController::class, 'index'])->name('staff.index');
    Route::get('/staff/create', [SmStaffController::class, 'create'])->name('staff.create');
    Route::post('/staff', [SmStaffController::class, 'store'])->name('staff.store');
    Route::get('/staff/{staff}/edit', [SmStaffController::class, 'edit'])->name('staff.edit');
    Route::put('/staff/{staff}', [SmStaffController::class, 'update'])->name('staff.update');
    Route::delete('/staff/{staff}', [SmStaffController::class, 'destroy'])->name('staff.destroy');
    Route::post('/staff/bulk-delete', [SmStaffController::class, 'bulkDestroy'])->name('staff.bulk-destroy');
    Route::get('/staff/check-username', [SmStaffController::class, 'checkUsername'])->name('staff.check-username');
    Route::get('/staff/role-permissions/{role}', [SmStaffController::class, 'getRolePermissions'])->name('staff.role-permissions');

    // Staff Attendance
    Route::get('/attendance', [SmStaffAttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/bulk', [SmStaffAttendanceController::class, 'storeBulk'])->name('attendance.bulk');
    Route::get('/attendance/report', [SmStaffAttendanceController::class, 'report'])->name('attendance.report');

    // Staff Leaves
    Route::get('/leaves', [SmStaffLeaveController::class, 'index'])->name('leaves.index');
    Route::post('/leaves', [SmStaffLeaveController::class, 'store'])->name('leaves.store');
    Route::patch('/leaves/{leave}', [SmStaffLeaveController::class, 'update'])->name('leaves.update');
    Route::post('/leaves/apply', [SmStaffLeaveController::class, 'apply'])->name('leaves.apply');

    // Staff ID Cards
    Route::get('/id-cards', [SmStaffIdCardController::class, 'index'])->name('id-cards.index');
    Route::get('/id-cards/print', [SmStaffIdCardController::class, 'print'])->name('id-cards.print');

    // Staff Payroll
    Route::get('/payroll', [SmStaffPayrollController::class, 'index'])->name('payroll.index');
    Route::post('/payroll/generate', [SmStaffPayrollController::class, 'generate'])->name('payroll.generate');
    Route::get('/payroll/{staff}/{month}/slip', [SmStaffPayrollController::class, 'slip'])->name('payroll.slip');

    // All Users
    Route::get('/users', [SmUsersController::class, 'index'])->name('users.index');
});

// ── Library ──
Route::prefix('library')->name('library.')->middleware('permission')->group(function () {
    Route::get('/dashboard', [LibraryController::class, 'dashboard'])->name('dashboard');

    // Books
    Route::get('/books', [LibraryController::class, 'books'])->name('books');
    Route::get('/books/create', [LibraryController::class, 'createBook'])->name('books.create');
    Route::post('/books', [LibraryController::class, 'storeBook'])->name('books.store');
    Route::get('/books/{book}/edit', [LibraryController::class, 'editBook'])->name('books.edit');
    Route::put('/books/{book}', [LibraryController::class, 'updateBook'])->name('books.update');
    Route::delete('/books/{book}', [LibraryController::class, 'destroyBook'])->name('books.destroy');

    // Book Issue
    Route::get('/book-issue', [LibraryController::class, 'bookIssue'])->name('book-issue');
    Route::post('/book-issue', [LibraryController::class, 'storeIssue'])->name('book-issue.store');
    Route::post('/book-issue/{issue}/return', [LibraryController::class, 'returnBook'])->name('book-issue.return');

    // AJAX helpers
    Route::get('/classes/{schoolClass}/sections', [LibraryController::class, 'getSections'])->name('sections');
    Route::get('/subjects', [LibraryController::class, 'getSubjectsForClasses'])->name('subjects');
    Route::get('/students', [LibraryController::class, 'getStudents'])->name('students');

    // Library Cards
    Route::get('/library-cards', [LibraryController::class, 'libraryCards'])->name('library-cards');
    Route::post('/library-cards/generate', [LibraryController::class, 'generateCards'])->name('library-cards.generate');
    Route::delete('/library-cards/{card}', [LibraryController::class, 'destroyCard'])->name('library-cards.destroy');
});

// ── Staff / Admin Personal Dashboard ──
Route::prefix('staff-portal')->name('staff.')->group(function () {
    Route::get('/dashboard', [StaffDashboardController::class, 'index'])->name('dashboard');
    Route::get('/id-card', [StaffDashboardController::class, 'idCard'])->name('id-card');
    Route::post('/leave/apply', [StaffDashboardController::class, 'applyLeave'])->name('leave.apply');
});
