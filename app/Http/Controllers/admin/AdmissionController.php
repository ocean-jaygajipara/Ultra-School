<?php

namespace App\Http\Controllers\admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Imports\StudentImport;
use Illuminate\Http\Request;
use App\Models\Admission;
use App\Models\AdmissionClassHistory;
use App\Models\FeeReceiptEntry;
use App\Models\Master\FeeMaster;
use App\Models\Master\MasterAcademicYear;
use App\Models\Master\MasterStandard;
use App\Models\EducationDetails;
use Maatwebsite\Excel\Facades\Excel;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use App\Http\Requests\AdmissionRequest;
use App\Models\CourceRegistration;
use App\Models\Master\MasterCourse;
use App\Traits\BioMetricTrait;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Yaza\LaravelGoogleDriveStorage\Gdrive;

use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use Google_Client;
use Illuminate\Container\Attributes\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class AdmissionController extends Controller
{
    use BioMetricTrait;

    public $modules = [];
    protected array $exportableColumns = [
        'id' => 'REG No.',
        'roll_no' => 'Roll No.',
        'gr_no' => 'GR No.',
        'bus_route_village' => 'Bus Route Village',
        'admission_date' => 'Admission Date',
        'admission_std' => 'Admission Std',
        'current_std' => 'Current Std',
        'division' => 'Division',
        // 'biometric_id' => 'Biometric ID',
        'aadhar_card_no' => 'Aadhaar No',
        'pen_no' => 'PEN No.',
        'first_name' => 'First Name',
        'last_name' => 'Last Name',
        'father_name' => "Father's Name",
        'mother_name' => "Mother's Name",
        'full_name' => 'Full Name',
        'mother_occupation' => "Mother's Occupation",
        'religion' => 'Religion',
        'cast' => 'Cast',
        'gender' => 'Gender',
        'birth_place' => 'Birth Place',
        'category' => 'Category',
        'house' => 'House',
        'stream' => 'Stream',
        'date_of_birth' => 'Date of Birth',
        'occupation' => "Father's Occupation",
        'temporary_address' => 'Temporary Address',
        'permanent_address' => 'Permanent Address',
        'mobile_no' => 'Mobile No',
        'parent_mobile_no' => "Parent's Mobile No",
        'other_mobile_no' => 'Other Mobile No',
        'whatsapp_no' => 'Whatsapp No',
        'apaar_id_abc_id' => 'APAAR ID / ABC ID',
        'udise' => 'UDISE No',
        'spid' => 'SPID',
        'bank_name' => 'Bank Name',
        'bank_account_no' => 'Bank Account No.',
        'gdrivefolderurl' => 'Google Drive Folder URL',
        'status' => 'Status',
        'created_at' => 'Created At',
        'course_names' => 'Course',
    ];

    protected array $defaultExportColumns = [
        'id',
        'roll_no',
        'gr_no',
        'bus_route_village',
        'current_std',
        'division',
        // 'biometric_id',
        'aadhar_card_no',
        'first_name',
        'last_name',
        'father_name',
        'mother_name',
        'full_name',
        'date_of_birth',
        'gender',
        'cast',
        'category',
        'occupation',
        'temporary_address',
        'permanent_address',
        'mobile_no',
        'parent_mobile_no',
        'other_mobile_no',
        'whatsapp_no',
        'apaar_id_abc_id',
        'udise',
        'spid',
        'gdrivefolderurl',
        'status',
        'created_at',
        'course_names',
    ];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Admission',
            'folder_path' => 'software.module.admission',
            'route' => 'admission',
            'table_name' => (new Admission())->getTable(),
            'permission_prefix' => 'admission',
            // 'api_ip' => 'http://192.168.31.5:98/',
        ];
    }


    public function index(Request $request)
    {

        /*
        return $apiResponse = Http::withHeaders([
            'Content-Type' => 'application/json',
            // 'Authorization' => 'Bearer ' . $token,
        ])->get('http://192.168.1.88:88/api/Employees');
        */

        $modules = $this->modules;

        $modules['login_user_role'] = Helper::getLoginUserRole();

        $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list');
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');

        $modules['course-register_add'] = Helper::directCan('course-register-create');

        // $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');

        if (!$modules['permission_list']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        // return $modules;
        View::share('modules', $modules);

        try {
            $columns = [
                (object) [
                    'data' => 'roll_no',
                    'name' => 'roll_no',
                    'td_label' => 'Roll No.',
                    'className' => 'w-5 text-center',
                    'orderable' => true,
                    'searchable' => true,
                ],
                // (object) ['data' => "admission_name", 'name' => 'first_name', 'td_label' => 'Name'],
                (object) [
                    'data' => 'admission_name',
                    'name' => 'first_name',
                    'td_label' => 'Name'
                ],

                (object) ['data' => "view_button", 'name' => 'view_button', 'td_label' => 'Stu <i class="bx bx-info-circle"></i>'],
                (object) ['data' => "attendance_button", 'name' => 'attendance_button', 'td_label' => 'Att.', 'className' => 'w-5 text-center'],
                (object) ['data' => "test_button", 'name' => 'test_button', 'td_label' => 'Test', 'className' => 'w-5 text-center'],
                (object) ['data' => "gdrivefolderurl", 'name' => 'gdrivefolderurl', 'td_label' => '<i class="fab fa-google-drive" style="font-size:18px;color:#4285F4;"></i>', 'className' => 'w-5 text-center'],
                (object) ['data' => "book_button", 'name' => 'book_button', 'td_label' => 'Book'],
                // (object) ['data' => "bonafide_button", 'name' => 'bonafide_button', 'td_label' => '<i class="ti ti-certificate" title="Bonafide Certificate"></i>', 'className' => 'w-5 text-center'],
                // (object) ['data' => "transfer_button", 'name' => 'transfer_button', 'td_label' => '<i class="ti ti-transfer-out" title="Transfer Certificate"></i>', 'className' => 'w-5 text-center'],
                (object) ['data' => "action", 'name' => 'action', 'td_label' => 'Update', 'orderable' => false, 'searchable' => false, 'className' => 'w-10 text-center'],
            ];
            if (!$modules['permission_edit'] && !$modules['permission_delete']) {
                $columns = array_filter($columns, function ($col) {
                    return $col->name !== 'action';
                });
            }
            View::share("columns", $columns);

            $data = [];

            if ($request->ajax()) {
                $data = Admission::withTrashed();

                if ($request->filled('course_id')) {
                    $courseId = $request->input('course_id');
                    $data->whereHas('courses', function ($q) use ($courseId) {
                        $q->where('course_id', $courseId);
                    });
                }

                $this->applyAdmissionListFilters($data, $request);

                if (empty($request->input('order'))) {
                    $data->orderByRaw("CASE WHEN roll_no IS NULL OR roll_no = 0 THEN 1 ELSE 0 END, CAST(roll_no AS UNSIGNED) ASC, id ASC");
                }

                $editPermisstion = $modules['permission_edit'];
                // $deletePermisstion = $modules['permission_delete'];
                $deletePermisstion = false;

                return Datatables::of($data)
                    ->addIndexColumn()
                    ->orderColumn('roll_no', function ($query, $order) {
                        $query->orderByRaw("CASE WHEN roll_no IS NULL OR roll_no = 0 THEN 1 ELSE 0 END, CAST(roll_no AS UNSIGNED) {$order}, id ASC");
                    })
                    ->editColumn('roll_no', function ($row) {
                        if (!empty($row->roll_no)) {
                            return '<span class="badge bg-label-primary fs-6 fw-bold px-2 py-1">' . $row->roll_no . '</span>';
                        }
                        return '<span class="text-muted small">—</span>';
                    })

                    ->filter(function ($query) use ($request) {
                        $search = $request->input('search');
                        $searchValue = is_array($search) ? ($search['value'] ?? '') : $search;
                        $trimmedSearch = trim((string) $searchValue);

                        if ($trimmedSearch !== '') {
                            $query->where(function ($q) use ($trimmedSearch) {
                                if (is_numeric($trimmedSearch)) {
                                    $q->where('id', $trimmedSearch)
                                      ->orWhere('roll_no', $trimmedSearch)
                                      ->orWhere('gr_no', $trimmedSearch)
                                      ->orWhere('biometric_id', $trimmedSearch);
                                } else {
                                    $words = array_filter(explode(' ', $trimmedSearch));
                                    foreach ($words as $word) {
                                        $q->orWhere('first_name', 'like', "%" . $word . "%")
                                          ->orWhere('last_name', 'like', "%" . $word . "%")
                                          ->orWhere('father_name', 'like', "%" . $word . "%");
                                    }
                                }
                            });
                        }
                    })
                    ->editColumn('admission_name', function ($row) {
                        $first  = trim($row->first_name ?? '');
                        $last   = trim($row->last_name ?? '');
                        $father = trim($row->father_name ?? '');

                        if ($first !== '' && $father !== '') {
                            $cleanFather = trim(preg_replace('/^' . preg_quote($first, '/') . '\s+/i', '', $father));
                        } else {
                            $cleanFather = $father;
                        }

                        $fullName = trim("{$first} {$last} {$cleanFather}");
                        return $fullName ?: '____________';
                    })
                    // ->editColumn('gdrivefolderurl', function ($row) {
                    //     $folderName = $row->id . '-' . $row->first_name . '-' . $row->father_name . '-' . $row->last_name;
                    //     return '<a href="javascript:void(0)" data-id="' . $row->id . '" data-foldername="' . $folderName . '" data-folderurl="' . $row->gdrivefolderurl . '" class="btn btn-warning btn-sm open_google_drive_folder"><i class="bx bx-copy-alt"></i> Open Doucuments</a>';
                    // })

                    ->editColumn('gdrivefolderurl', function ($row) {
                        if (!empty($row->gdrivefolderurl) && !empty($row->gdrivefolderid) && !empty($row->gdrivefoldername)) {
                            $folderName = trim($row->first_name . ' ' . $row->last_name . ' ' . $row->father_name);
                            return '<a href="' . $row->gdrivefolderurl . '" target="_blank" data-id="' . $row->id . '" data-foldername="' . $folderName . '" data-folderurl="' . $row->gdrivefolderurl . '" class="adm-act adm-act-icon adm-act-drive open_google_drive_folder" title="Open Google Drive Folder"><i class="fab fa-google-drive"></i></a>';
                        } else {
                            return '<a href="javascript:void(0)" data-id="' . $row->id . '" class="adm-act adm-act-icon adm-act-folder create_google_drive_folder" title="Create Google Drive Folder"><i class="fa fa-folder-plus"></i></a>';
                        }
                    })

                    // ->editColumn('status', function ($row) use ($modules) {
                    //     $dropdown = '<ul class="dropdown-menu">';
                    //     $button = '';

                    //     if ($row->status == "created") {
                    //         $button = '<button type="button" class="btn btn-success btn-sm w-100 dropdown-toggle" data-bs-toggle="dropdown">' . ucfirst($row->status) . '</button>';
                    //         $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="rejected">Reject</a></li>';
                    //     } elseif ($row->status == "rejected") {
                    //         $button = '<button type="button" class="btn btn-danger btn-sm w-100 dropdown-toggle" data-bs-toggle="dropdown">' . ucfirst($row->status) . '</button>';
                    //         $dropdown .= '<li><a href="javascript:void(0)" class="dropdown-item update-status" data-url="' . route($modules["route"] . '.status-update') . '" data-id="' . $row->id . '" data-update_status="created">Re-Create</a></li>';
                    //     }

                    //     $dropdown .= '</ul>';
                    //     return $button . $dropdown;
                    // })

                    ->addColumn('course_button', function ($row) use ($modules) {
                        // if ($modules['course-register_add']) {
                        return '<button class="btn btn-primary btn-sm course-btn" data-id="' . $row->id . '" data-name="' . $row->first_name . '">Course</button>';
                        // }
                        return "-";
                    })
                    ->addColumn('book_button', function ($row) {
                        return '<button class="adm-act adm-act-book book-btn" data-id="' . $row->id . '" data-name="' . $row->first_name . '"><i class="ti ti-book"></i> Book</button>';
                    })

                    // ->addColumn('bonafide_button', function ($row) {
                    //     return '<a href="' . route('admission.bonafide-certificate', $row->id) . '" target="_blank" class="btn btn-secondary btn-sm" title="Bonafide Certificate"> <i class="ti ti-certificate"></i> </a>';
                    // })

                    // ->addColumn('transfer_button', function ($row) {
                    //     return '<a href="' . route('admission.transfer-certificate', $row->id) . '" target="_blank" class="btn btn-dark btn-sm" title="Transfer Certificate"> <i class="ti ti-transfer-out"></i> </a>';
                    // })

                    ->addColumn('view_button', function ($row) {
                        return '<button class="adm-act adm-act-view view-btn" data-id="' . $row->id . '" data-name="' . $row->first_name . '" data-mobile="' . $row->mobile_no . '"><i class="ti ti-eye"></i> View</button>';
                    })
                    // Replace the existing attendance_button code in your controller (around line 130)
                    ->addColumn('attendance_button', function ($row) {
                        return '<a href="' . url('software/attedance') . '" class="adm-act adm-act-icon adm-act-att open-attendance" data-admission-id="' . $row->id . '" target="_blank" title="View Attendance"><i class="ti ti-calendar-check"></i></a>';
                    })

                    ->addColumn('test_button', function ($row) {
                        return '<a href="' . url('software/test_report') . '" class="adm-act adm-act-icon adm-act-test open-test" data-admission-id="' . $row->id . '" title="View Test Report"><i class="ti ti-file-text"></i></a>';
                    })

                    ->addColumn('action', function ($row) use ($modules, $editPermisstion, $deletePermisstion) {
                        $btn = '';
                        if (!$row->deleted_at) {
                            if ($editPermisstion) {
                                $btn .= '<a href="' . route($modules["route"] . ".edit", [$row->id]) . '" class="adm-act adm-act-update mx-1"><i class="ti ti-edit"></i> Update</a>';
                            }

                            if ($deletePermisstion) {
                                $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row->id]) . '" class="adm-act adm-act-icon adm-act-delete deletebutton mx-1" title="Delete"><i class="ti ti-trash"></i></a>';
                            }
                        } else {
                            $btn .= '<a href="javascript:void(0)" data-restore="' . route($modules["route"] . ".restore", ['id' => $row->id]) . '" class="adm-act adm-act-restore mx-1 record-restore"><i class="ti ti-history"></i> Restore</a>';
                        }

                        return $btn ?: '-';
                    })

                    ->rawColumns(['status', 'action', 'gdrivefolderurl', 'admission_name', 'course_button', 'view_button', 'book_button', 'attendance_button', 'test_button', 'roll_no'])
                    ->make(true);
            }

            $availableExportColumns = $this->exportableColumns;
            $defaultExportColumns = $this->defaultExportColumns;
            $courses = \App\Models\Master\MasterCourse::where('status', 'active')->orderBy('course_name')->get();
            $batches = \App\Models\Master\MasterBatch::where('status', 'active')->orderBy('batch_name')->get();
            $standards = $this->getStandardList();
            $divisions = \App\Models\Master\MasterDivision::where('status', 'active')->orderBy('name')->pluck('name')->filter()->values()->all();
            $academicYears = MasterAcademicYear::where('status', 'active')->orderByDesc('start_date')->get(['id', 'name']);

            return view($modules['folder_path'] . '.index', compact('data', 'availableExportColumns', 'defaultExportColumns', 'courses', 'batches', 'standards', 'divisions', 'academicYears'));
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function create()
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        if (!$modules['permission_add']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);
        $categories = \App\Models\Master\MasterCast::where('status', 'active')->orderBy('id')->get();
        $religions = \App\Models\Master\MasterReligion::where('status', 'active')->orderBy('id')->get();
        $houses = \App\Models\Master\MasterHouse::where('status', 'active')->orderBy('id')->get();
        $villages = \App\Models\Master\MasterBusRouteVillage::where('status', 'active')->orderBy('name')->get();
        $classes = \App\Models\Master\MasterClass::where('status', 'active')->orderBy('class')->get();
        $divisions = \App\Models\Master\MasterDivision::where('status', 'active')->orderBy('name')->get();
        $schoolStandards = $this->getStandardList();
        $academicYears = MasterAcademicYear::where('status', 'active')->orderByDesc('start_date')->get();
        return view($modules['folder_path'] . '.form', compact('modules', 'categories', 'religions', 'houses', 'villages', 'classes', 'divisions', 'schoolStandards', 'academicYears'));
    }


    public function store(AdmissionRequest $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        if (!$modules['permission_add']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);
        $validated = $request->validated();

        $academicYear = !empty($validated['academic_year_id'])
            ? MasterAcademicYear::find($validated['academic_year_id'])
            : (MasterAcademicYear::forDate($validated['admission_date'] ?? null) ?? MasterAcademicYear::current());
        if (!$academicYear) {
            return Redirect::back()->withInput()
                ->withErrors('No academic year found. Please select Academic Year or add it in Academic Year Master first.');
        }
        $validated['academic_year_id'] = $academicYear->id;

        if ($request->hasFile('profile_pic')) {
            $file = $request->file('profile_pic');

            $firstName = $request->input('first_name');
            $profileName = $request->input('last_name'); // or another field if needed
            $folderName = $firstName . '_' . $profileName;

            $destinationPath = public_path('uploads/profile_pics/' . $folderName);

            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move($destinationPath, $filename);

            $validated['profile_pic'] = 'uploads/profile_pics/' . $folderName . '/' . $filename;
        }

        $validated['first_name']  = strtoupper($validated['first_name']);
        $validated['last_name']   = strtoupper($validated['last_name']);
        $validated['father_name'] = strtoupper($validated['father_name']);
        $validated['mother_name'] = strtoupper($validated['mother_name']);


        // dd($request->all(), $validated);
        $token = Session::get('external_api_token');

        try {

            $loginUserId = Auth::user()->id;
            $validated['created_by'] = $loginUserId;

            $validated = $this->syncFormMasters($validated, $loginUserId);

            $admission = Admission::create($validated);
            AdmissionClassHistory::record($admission, $loginUserId, $academicYear);

            $admission_id = $admission->id;
            try {
                $folderName = trim($admission->first_name . ' ' . $admission->last_name . ' ' . $admission->father_name);

                $parentFolderId = \App\Services\SchoolDatabaseManager::getDriveFolderId() ?: '1cogAtOqCv8C8yfwPum2WzDTUekOi3J1U'; // Main Student Folder ID

                $folderData = \App\Helpers\GoogleDriveHelper::createFolder($folderName, $parentFolderId);

                $admission->update([
                    'gdrivefolderid'   => $folderData['id'] ?? null,
                    'gdrivefoldername' => $folderData['name'] ?? $folderName,
                    'gdrivefolderurl'  => $folderData['link'] ?? null,
                ]);
            } catch (\Exception $e) {
                \Log::error("Google Drive folder creation failed: " . $e->getMessage());
            }

            /*
            $gender = $admission?->gender;
            if ($gender == "Male") {
                $gender = 0;
            } else if ($gender == "Female") {
                $gender = 1;
            } else if ($gender == "Other") {
                $gender = 2;
            }

            $student_name = $admission?->first_name . " " . $admission?->last_name;
            $apiResponse = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ])->post($modules['api_ip'] . 'api/Employees', [
                "Id" => 0,
                "Code" => (string) 'SA' . $admission_id,
                "DeviceRegisterId" => (string) $admission->aadhar_card_no ?? '',
                "Name" => $student_name ?? '',
                "Gender" => $gender ?? "",
                "DepartmentId" => 0,
                "Department" => "",
                "LocationId" => 0,
                "Location" => $admission->permanent_address ?? "",
                "ImagePath" => "",
                "CreatedBy" => (string) $loginUserId,
                "CreatedOn" => now()->toIso8601String(),
                "UpdatedBy" => "",
                "UpdatedOn" => now()->toIso8601String()
            ]);

            if ($apiResponse->successful()) {

                $responseBody = $apiResponse->json();
                $biometricId = $responseBody['Id'] ?? null;

                if ($biometricId) {
                    $admission->update([
                        'biometric_id' => $biometricId
                    ]);
                }

                $deviceResponse = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $token,
                ])->get($modules['api_ip'] . 'api/Device');

                if (!$deviceResponse->successful()) {
                    // dd("ln-246" . $deviceResponse->body());
                    return Redirect::route($modules['route'] . '.create')
                        ->withErrors('Device fetch failed : ' . $deviceResponse->body());
                }

                $deviceData = $deviceResponse->json();

                $device_id = $deviceData[0]['DeviceKey'] ?? null;

                $deviceKey = $device_id;

                $uploadUserPayload = [
                    [
                        "UserId" => (string) $biometricId,
                        "UserName" => $student_name ?? '',
                        "CardNumber" => (string) ($admission->aadhar_card_no ?? ''),
                        "Password" => $admission->mobile_no ?? '',
                        "AccessLevel" => "USER",
                        "DeviceKeys" => [
                            $deviceKey
                        ],
                        "OnlineEnrollment" => false,
                        "UploadBiometricDataIfAvailable" => true,
                        "FingerPrintUpload" => false,
                        "FaceUpload" => false,
                        "CardUpload" => false,
                        "PasswordUpload" => false
                    ]
                ];

                $uploadUserResponse = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ])->post($modules['api_ip'] . 'api/DeviceCommand/UploadUser', $uploadUserPayload);

                // dd($uploadUserResponse->body(), $uploadUserResponse->successful());


                if (!$uploadUserResponse->successful()) {
                    return Redirect::route($modules['route'] . '.index')
                        ->withErrors('Admission created and synced, but device upload failed: ' . $uploadUserResponse->body());
                }

                return Redirect::route('cource-registration.create', ['admission_id' => $admission_id])
                    ->with('success', $admission->name . ' has been successfully admitted and synced with the external system.');
            } else {
                return Redirect::route($modules['route'] . '.index')
                    ->withErrors('Admission created, but API sync failed: ' . $apiResponse->body());
            }
                    */
            if (Helper::directCan($modules['permission_prefix'] . '-edit')) {
                return Redirect::route($modules['route'] . '.roll-number', $admission_id)->with('success', $admission->first_name . ' has been successfully admitted.');
            }
            return Redirect::route($modules['route'] . '.index')->with('success', $admission->first_name . ' has been successfully admitted.');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function show(Request $request)
    {
        $student = Admission::with(['academicYear'])->withTrashed()->find($request->id);
        $student?->append(['admission_std', 'current_std']);

        $studentCourses = CourceRegistration::with(['course', 'class', 'shift', 'batch'])
            ->withTrashed()
            ->where('register_id', $request->id)
            ->get();

        if (!$student && $studentCourses->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Student and Course not found.']);
        }

        $classHistory = [];
        if ($student) {
            // Always keep Class History filled (current class + previous std if promoted)
            AdmissionClassHistory::ensureForStudent($student);

            $classHistory = AdmissionClassHistory::with(['academicYear', 'standard'])
                ->where('admission_id', $student->id)
                ->get()
                ->sortBy(fn ($row) => $row->academicYear?->start_date?->timestamp ?? PHP_INT_MAX)
                ->values()
                ->map(function ($row) {
                    return [
                        'academic_year' => $row->academicYear?->name ?? '-',
                        'standard' => $row->standard?->name ?? '-',
                        'division' => $row->division ?: '-',
                        'roll_no' => $row->roll_no ?: '-',
                    ];
                })
                ->all();
        }

        return response()->json([
            'success' => true,
            'student' => $student,
            'courses' => $studentCourses,
            'class_history' => $classHistory,
            'fee_details' => $student ? $this->studentFeeDetails($student) : [],
        ]);
    }

    protected function studentFeeDetails(Admission $student): array
    {
        $histories = AdmissionClassHistory::with(['academicYear', 'standard'])
            ->where('admission_id', $student->id)
            ->get();

        $yearByStandard = [];
        foreach ($histories as $history) {
            $yearByStandard[$history->standard_id] = $history;
        }
        if ($student->current_std_id && !isset($yearByStandard[$student->current_std_id])) {
            $yearByStandard[$student->current_std_id] = null;
        }
        if ($yearByStandard === []) {
            return [];
        }

        $standards = MasterStandard::query()
            ->whereIn('id', array_keys($yearByStandard))
            ->orderBy('id')
            ->get();
        $masters = FeeMaster::query()->where('status', 'active')->get();
        $receipts = FeeReceiptEntry::query()
            ->where(function ($query) use ($student) {
                $query->where('admission_id', $student->id);
                if (trim((string) $student->gr_no) !== '') {
                    $query->orWhere('gr_no', $student->gr_no);
                }
            })
            ->get();
        $years = MasterAcademicYear::query()->get()->keyBy('id');
        $terms = ['FIRST' => 'First Term', 'SECOND' => 'Second Term'];

        $rows = [];
        $open = [];
        foreach ($standards as $standard) {
            $history = $yearByStandard[$standard->id] ?? null;
            $yearId = (int) ($history->academic_year_id ?? $student->academic_year_id);
            $yearName = $history?->academicYear?->name ?? ($years->get($yearId)?->name ?? '—');

            foreach ($terms as $term => $label) {
                $master = $masters->first(fn ($row) => (int) $row->standard_id === (int) $standard->id
                    && (string) $row->term === $term
                    && (int) $row->academic_year_id === $yearId)
                    ?: $masters->first(fn ($row) => (int) $row->standard_id === (int) $standard->id
                        && (string) $row->term === $term);
                $amount = $master ? (float) $master->total : 0.0;
                $paid = $receipts->contains(fn ($receipt) => (int) $receipt->standard_id === (int) $standard->id
                    && (string) $receipt->term === $term);

                $rows[] = [
                    'standard' => $standard->name,
                    'academic_year' => $yearName,
                    'term' => $label,
                    'amount' => $amount,
                    'paid' => $paid,
                ];
                if (!$paid && $amount > 0) {
                    $open[] = count($rows) - 1;
                }
            }
        }

        $pool = (float) $receipts->sum('last_yr_pending');
        foreach ($open as $index) {
            $due = $rows[$index]['amount'];
            if (($pool + 0.009) < $due) {
                continue;
            }
            $rows[$index]['paid'] = true;
            $pool = round($pool - $due, 2);
        }

        return array_map(fn ($row) => [
            'standard' => $row['standard'],
            'academic_year' => $row['academic_year'],
            'term' => $row['term'],
            'amount' => number_format($row['amount'], 2),
            'status' => $row['paid'] ? 'Paid' : ($row['amount'] > 0 ? 'Unpaid' : '—'),
        ], $rows);
    }

    public function edit(string $id)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        if (!$modules['permission_edit']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);
        try {


            $edit = Admission::findOrFail($id);

            $categories = \App\Models\Master\MasterCast::where('status', 'active')->orderBy('id')->get();
            $religions = \App\Models\Master\MasterReligion::where('status', 'active')->orderBy('id')->get();
            $houses = \App\Models\Master\MasterHouse::where('status', 'active')->orderBy('id')->get();
            $villages = \App\Models\Master\MasterBusRouteVillage::where('status', 'active')->orderBy('name')->get();
            $classes = \App\Models\Master\MasterClass::where('status', 'active')->orderBy('class')->get();
            $divisions = \App\Models\Master\MasterDivision::where('status', 'active')->orderBy('name')->get();
            $schoolStandards = $this->getStandardList();
            $academicYears = MasterAcademicYear::where('status', 'active')->orderByDesc('start_date')->get();
            return view($modules['folder_path'] . '.form', compact('modules', 'edit', 'categories', 'religions', 'houses', 'villages', 'classes', 'divisions', 'schoolStandards', 'academicYears'));
        } catch (\Exception $e) {

            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function update(AdmissionRequest $request, string $id)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        if (!$modules['permission_edit']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        $validated = $request->validated();
        $validated['first_name']  = strtoupper($validated['first_name']);
        $validated['last_name']   = strtoupper($validated['last_name']);
        $validated['father_name'] = strtoupper($validated['father_name']);
        $validated['mother_name'] = strtoupper($validated['mother_name']);

        $academicYear = !empty($validated['academic_year_id'])
            ? MasterAcademicYear::find($validated['academic_year_id'])
            : MasterAcademicYear::current();
        if (!$academicYear) {
            return Redirect::back()->withInput()
                ->withErrors('Please select Academic Year, or set Current in Academic Year Master first.');
        }
        $validated['academic_year_id'] = $academicYear->id;

        // dd($validated,$request->all());
        $token = Session::get('external_api_token');
        try {
            $loginUserId = Auth::user()->id;
            $validated['updated_by'] = $loginUserId;

            $validated = $this->syncFormMasters($validated, $loginUserId);

            $admission = Admission::where('id', $id)->firstOrFail();
            $admission_id = $admission->id;
            if ($request->hasFile('profile_pic')) {
                $file = $request->file('profile_pic');

                $firstName = $request->input('first_name');
                $profileName = $request->input('last_name');
                $folderName = $firstName . '_' . $profileName;

                $destinationPath = public_path('uploads/profile_pics/' . $folderName);

                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0777, true);
                }

                $filename = time() . '_' . $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $filename = time() . '_' . pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME) . '.' . $extension;
                $file->move($destinationPath, $filename);

                $validated['profile_pic'] = 'uploads/profile_pics/' . $folderName . '/' . $filename;
            }

            $classChanged = (int) ($validated['current_std_id'] ?? $admission->current_std_id) !== (int) $admission->current_std_id
                || ($validated['division'] ?? $admission->division) !== $admission->division;
            if ($classChanged) {
                $validated['roll_no'] = null;
            }

            $admission->update($validated);
            $admission->refresh();
            AdmissionClassHistory::record($admission, $loginUserId, $academicYear);

            return Redirect::route($modules['route'] . '.index')
                ->withSuccess($modules['title'] . ' updated successfully');

            /*
            if (!$admission?->biometric_id && false) {
                //     $response = $this?->storeBioMetricEmployee($loginUserId, $admission);
                //     return $response;
                //     if ($response['status'] && $response['data']) {
                //         $responseData = (object)$response['data'];
                //         $responseData?->root?->Id;
                //         $admission = Admission::where('id', $CourceRegistration->register_id)->firstOrFail();
                //         $admission->biometric_id = $responseData?->root?->Id;
                //         $admission->save();
                //     }
                $gender
                    = $admission?->gender;
                if ($gender == "Male") {
                    $gender = 0;
                } else if ($gender == "Female") {
                    $gender = 1;
                } else if ($gender == "Other") {
                    $gender = 2;
                }

                $student_name = $admission?->first_name . " " . $admission?->last_name;
                $apiResponse = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token,
                ])->post($modules['api_ip'] . 'api/Employees', [
                    "Id" => 0,
                    "Code" => (string) 'SA' . $admission_id,
                    "DeviceRegisterId" => (string) $admission->aadhar_card_no ?? '',
                    "Name" => $student_name ?? '',
                    "Gender" => $gender ?? "",
                    "DepartmentId" => 0,
                    "Department" => "",
                    "LocationId" => 0,
                    "Location" => $admission->permanent_address ?? "",
                    "ImagePath" => "",
                    "CreatedBy" => (string) $loginUserId,
                    "CreatedOn" => now()->toIso8601String(),
                    "UpdatedBy" => "",
                    "UpdatedOn" => now()->toIso8601String()
                ]);

                if ($apiResponse->successful()) {

                    $responseBody = $apiResponse->json();
                    $biometricId = $responseBody['Id'] ?? null;

                    if ($biometricId) {
                        $admission->update([
                            'biometric_id' => $biometricId
                        ]);
                    }

                    $deviceResponse = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $token,
                    ])->get($modules['api_ip'] . 'api/Device');

                    if (!$deviceResponse->successful()) {
                        // dd("ln-246" . $deviceResponse->body());
                        return Redirect::route($modules['route'] . '.create')
                            ->withErrors('Device fetch failed : ' . $deviceResponse->body());
                    }

                    $deviceData = $deviceResponse->json();

                    $device_id = $deviceData[0]['DeviceKey'] ?? null;

                    $deviceKey = $device_id;

                    $uploadUserPayload = [
                        [
                            "UserId" => (string) $biometricId,
                            "UserName" => $student_name ?? '',
                            "CardNumber" => (string) ($admission->aadhar_card_no ?? ''),
                            "Password" => $admission->mobile_no ?? '',
                            "AccessLevel" => "USER",
                            "DeviceKeys" => [
                                $deviceKey
                            ],
                            "OnlineEnrollment" => false,
                            "UploadBiometricDataIfAvailable" => true,
                            "FingerPrintUpload" => false,
                            "FaceUpload" => false,
                            "CardUpload" => false,
                            "PasswordUpload" => false
                        ]
                    ];

                    $uploadUserResponse = Http::withHeaders([
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $token,
                    ])->post($modules['api_ip'] . 'api/DeviceCommand/UploadUser', $uploadUserPayload);

                    // dd($uploadUserResponse->body(), $uploadUserResponse->successful());


                    if (!$uploadUserResponse->successful()) {
                        return Redirect::route($modules['route'] . '.index')
                            ->withErrors('Admission created and synced, but device upload failed: ' . $uploadUserResponse->body());
                    }
                    if (Helper::directCan('course-register-edit')) {
                        return Redirect::route('cource-registration.edit', [ 'register_id' => $CourceRegistration->id])->withSuccess($modules['title'] . ' updated successfully');
                    }
                    return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' updated successfully');
                } else {
                    return Redirect::back()
                        ->withErrors('Admission updated, but API sync failed: ' . $apiResponse->body());
                }
            }
                */
        } catch (\Exception $e) {
            return $e->getMessage();
            return Redirect::back()->withErrors($e->getMessage())->withInput();
        }
    }


    public function rollNumber(string $id)
    {
        $modules = $this->modules;
        if (!Helper::directCan($modules['permission_prefix'] . '-edit')) {
            abort(403, 'User does not have the right permissions.');
        }
        View::share('modules', $modules);

        $admission = Admission::findOrFail($id);
        $suggestedRollNo = $admission->roll_no ?: $this->nextRollNumber($admission);

        $history = AdmissionClassHistory::with(['academicYear', 'standard'])
            ->where('admission_id', $admission->id)
            ->get()
            ->sortBy(fn ($row) => $row->academicYear?->start_date?->timestamp ?? PHP_INT_MAX)
            ->values();
        $currentYear = MasterAcademicYear::current();

        return view($modules['folder_path'] . '.roll-number', compact('modules', 'admission', 'suggestedRollNo', 'history', 'currentYear'));
    }

    public function rollNumberStore(Request $request, string $id)
    {
        $modules = $this->modules;
        if (!Helper::directCan($modules['permission_prefix'] . '-edit')) {
            abort(403, 'User does not have the right permissions.');
        }

        $admission = Admission::findOrFail($id);

        if (empty($admission->current_std_id) || empty($admission->division)) {
            return Redirect::back()->withErrors('Please set Current Std and Division for this student before generating a roll number.');
        }

        $request->validate([
            'roll_no' => [
                'required',
                'integer',
                'min:1',
                Rule::unique($admission->getTable(), 'roll_no')
                    ->where('current_std_id', $admission->current_std_id)
                    ->where('division', $admission->division)
                    ->whereNull('deleted_at')
                    ->ignore($admission->id),
            ],
        ], [
            'roll_no.unique' => 'This roll number is already given to another student of ' . $admission->current_std . ' - ' . $admission->division . '.',
        ]);

        $admission->update([
            'roll_no' => (int) $request->roll_no,
            'updated_by' => Auth::id(),
        ]);
        AdmissionClassHistory::recordRoll($admission, Auth::id());

        return Redirect::route($modules['route'] . '.index')
            ->with('success', 'Roll No. ' . $admission->roll_no . ' saved for ' . trim($admission->first_name . ' ' . $admission->last_name) . ' (' . $admission->current_std . ' - ' . $admission->division . ').');
    }

    protected function nextRollNumber(Admission $admission): ?int
    {
        if (empty($admission->current_std_id) || empty($admission->division)) {
            return null;
        }

        $max = Admission::where('current_std_id', $admission->current_std_id)
            ->where('division', $admission->division)
            ->where('id', '!=', $admission->id)
            ->max('roll_no');

        return ((int) $max) + 1;
    }

    public function education_detail_delete(Request $request)
    {
        return $this->sendResponse([], 'Education Details removed successfully!');
    }

    public function destroy(Request $request, string $id)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');
        if (!$modules['permission_delete']) {
            if (isset($request) && $request->ajax()) {
                return $this->sendError('User does not have the right permissions.', [], [], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }
        $isAjax = $request->ajax();

        try {
            $dataDelete = Admission::findOrFail($id);

            // Soft delete logic
            $dataDelete->deleted_by = Auth::user()?->id;
            $dataDelete->save();

            if ($dataDelete->delete()) {
                if ($isAjax) {
                    return response()->json([
                        'success' => true,
                        'message' => $modules['title'] . ' deleted successfully.',
                    ]);
                }

                return redirect()->route($modules['route'] . '.index')
                    ->withSuccess($modules['title'] . ' deleted successfully.');
            }

            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => 'Something went wrong, please try again later.',
                ]);
            }

            return redirect()->route($modules['route'] . '.index')
                ->withErrors('Something went wrong, please try again later.');
        } catch (\Exception $e) {
            if ($isAjax) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ]);
            }

            return redirect()->route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function status_update(Request $request)
    {
        $isAjax = ($request->ajax()) ? true : false;

        $modules = $this->modules;
        $validator = Validator::make($request->all(), [
            'id' => ['required', Rule::exists($modules['table_name'], 'id')],
            'update_status' => ['required', 'in:running,completed,cancel']
        ]);

        if ($validator->fails() && $isAjax) {
            return $this->sendError($validator->messages()->first(), $validator->messages(), [], 401);
        }

        try {
            $designation = Admission::withTrashed()->findOrFail($request?->id);

            if ($designation) {
                $designation->status = $request->update_status;
                $designation->save();
                if ($isAjax) {
                    return $this->sendResponse($designation, $modules['title'] . ' status update successfully.');
                }
                return Redirect::route($modules['route'] . '.index')->withSuccess($modules['title'] . ' status update successfully.');
            }
            if ($isAjax) {
                return $this->sendError('something went wrong please try again later');
            }
            return Redirect::back()->withErrors('something went wrong please try again later')->withInput();
        } catch (\Exception $e) {
            if ($isAjax) {
                return $this->sendError($e->getMessage(), $e->getMessage(), [], 401);
            }
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function restore($id)
    {

        $modules = $this->modules;

        try {

            $restore_data = Admission::withTrashed()->findOrFail($id);
            $restore_data->restore();

            return Redirect::back()->withSuccess($modules['title'] . ' restored successfully!');
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function createFolder(Request $request)
    {
        $admission = Admission::findOrFail($request->id);

        try {
            $folderName = trim($admission->first_name . ' ' . $admission->last_name . ' ' . $admission->father_name);

            $parentFolderId = \App\Services\SchoolDatabaseManager::getDriveFolderId() ?: '1cogAtOqCv8C8yfwPum2WzDTUekOi3J1U'; // Main Student Folder ID

            $folderData = \App\Helpers\GoogleDriveHelper::createFolder($folderName, $parentFolderId);

            $admission->update([
                'gdrivefolderid'   => $folderData['id'] ?? null,
                'gdrivefoldername' => $folderData['name'] ?? $folderName,
                'gdrivefolderurl'  => $folderData['link'] ?? null,
            ]);

            return response()->json(['success' => true, 'message' => 'Folder created successfully']);
        } catch (\Exception $e) {
            \Log::error("Google Drive folder creation failed: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // private function getOrCreateFolder(Drive $service, string $folderName): string
    // {
    //     $query = "mimeType='application/vnd.google-apps.folder' and name='$folderName' and trashed=false";
    //     $results = $service->files->listFiles([
    //         'q' => $query,
    //         'fields' => 'files(id, name)',
    //     ]);

    //     $files = $results->getFiles();

    //     if (empty($files)) {
    //         $fileMetadata = new DriveFile([
    //             'name' => $folderName,
    //             'mimeType' => 'application/vnd.google-apps.folder',
    //         ]);

    //         $folder = $service->files->create($fileMetadata, [
    //             'fields' => 'id',
    //         ]);

    //         return $folder->id;
    //     } else {
    //         return $files[0]->id;
    //     }
    // }

    // ========================= line no - 545 lagin che Folder crate thay che ane te folder  ni ander multipal Folder create thay code End ==============================


    public function renameFolder(Request $request)
    {
        $modules = $this->modules;
        $validator = Validator::make($request->all(), [
            'id' => ['required', Rule::exists($modules['table_name'], 'id')],
            'foldername' => ['required'],
            'OldfolderName' => ['required'],
            'OldfolderId' => ['required']
        ]);

        $folderName = $request->input('name', $request->foldername);
        $OldfolderName = $request->OldfolderName;
        $OldfolderId = $request->OldfolderId;

        $client = new Google_Client();
        $client->setClientId(config('filesystems.disks.google.clientId'));
        $client->setClientSecret(config('filesystems.disks.google.clientSecret'));
        $client->refreshToken(config('filesystems.disks.google.refreshToken'));


        $service = new Drive($client);

        $fileMetadata = new DriveFile([
            'name' => $folderName
        ]);

        try {

            $folder = $service->files->update($OldfolderId, $fileMetadata, [
                'fields' => 'id, name'
            ]);

            $folderId = $folder->id;
            $folderUrl = "https://drive.google.com/drive/folders/{$folderId}";

            $createfolder = Admission::withTrashed()->findOrFail($request?->id);
            $createfolder->gdrivefolderurl = $folderUrl;
            $createfolder->gdrivefolderid = $folderId;
            $createfolder->gdrivefoldername = $folder->name;
            $createfolder->save();

            return response()->json([
                'folder_id' => $folderId,
                'folder_name' => $folder->name,
                'folder_url' => $folderUrl
            ]);
        } catch (\Exception $e) {
            return Redirect::route($modules['route'] . '.index')->withErrors($e->getMessage());
        }
    }

    public function getAdmissionDetails($id)
    {
        $admission = Admission::find($id);

        if ($admission) {
            return response()->json([
                'first_name' => $admission->first_name,
                'last_name' => $admission->last_name,

            ]);
        }

        return response()->json(['error' => 'Admission not found'], 404);
    }

    public function aadharCardNoToData(Request $request)
    {
        $aadhar = $request->aadhar;
        $user = Admission::where('aadhar_card_no', $aadhar)->first();

        // If no admission found, no redirect, just say no user
        if (!$user) {
            return response()->json([
                'redirect' => false,
                'message' => 'No admission found with this Aadhaar number.'
            ]);
        }

        // Check if course registration exists
        $hasCourseRegistration = CourceRegistration::where('register_id', $user->id)->exists();

        if ($hasCourseRegistration) {
            // Course registration exists
            return response()->json([
                'redirect' => true,
                'redirect_url' => route('cource-registration.create', ['admission_id' => $user->id]),
                'message' => "<div style='text-align:left; font-size:16px; line-height:1.7em;'>
                                <strong>Name:</strong> {$user->first_name} {$user->last_name}<br>
                                <strong>Father Name:</strong> {$user->father_name}<br>
                                <strong>Aadhaar:</strong> {$user->aadhar_card_no}<br>
                                <strong>ID:</strong> {$user->id}<br>
                                You already have a course registration.
                              </div>",
            ]);
        } else {
            return response()->json([
                'redirect' => true,
                'redirect_url' => route('cource-registration.create', ['admission_id' => $user->id]),
                'message' => "<div style='text-align:left; font-size:16px; line-height:1.7em;'>
                                <strong>Name:</strong> {$user->first_name} {$user->last_name}<br>
                                <strong>Father Name:</strong> {$user->father_name}<br>
                                <strong>Aadhaar:</strong> {$user->aadhar_card_no}<br>
                                <strong>ID:</strong> {$user->id}<br>
                                Admission form complete but course registration not done yet.
                              </div>",
            ]);
        }
    }




    public function courseFeesGet(Request $request)
    {
        $data = CourceRegistration::join('master_course', 'cource_registration.course_id', '=', 'master_course.id')
            ->where('cource_registration.register_id', $request->register_id)
            ->where('cource_registration.course_id', $request->course_id)
            ->select('master_course.course_name as course_name', 'cource_registration.fee')
            ->first();

        return response()->json($data);
    }

    public function courseFeesUpdate(Request $request)
    {
        $request->validate([
            'register_id' => 'required|integer',
            'course_id' => 'required|integer',
            'fee' => 'required|numeric'
        ]);

        $registration = CourceRegistration::where('register_id', $request->register_id)
            ->where('course_id', $request->course_id)
            ->first();

        if ($registration) {
            $registration->fee = $request->fee;
            $registration->save();

            return response()->json(['message' => 'Course Fee Updated successfully.']);
        } else {
            return response()->json(['message' => 'Record not found.'], 404);
        }
    }

    public function updateCourseStatus(Request $request)
    {
        $user = Auth::user();
        if (!$user || !(method_exists($user, 'hasRole') && ($user->hasRole('super-admin') || $user->hasRole('developer')))) {
            return $this->sendError('Only Super-admin can update the status.', [], [], 403);
        }

        $request->validate([
            'register_id' => 'required|integer',
            'course_id'   => 'required|integer',
            'status'      => 'required|in:Running,Completed,Cancel',
        ]);

        $course = CourceRegistration::where('register_id', $request->register_id)
            ->where('course_id', $request->course_id)
            ->first();

        if (!$course) {
            return $this->sendError('Course not found.');
        }

        $course->status = $request->status;
        $course->updated_at = now();
        $course->save();

        return $this->sendResponse($course, 'Status updated successfully.');
    }
    public function bonafideCertificate($history)
    {
        return $this->showCertificate('bonafide', 'bonafide-certificate', $history, ['course']);
    }

    public function transferCertificate($history)
    {
        return $this->showCertificate('tc', 'transfer-certificate', $history, ['course', 'class']);
    }

    public function englishMediumCertificate($history)
    {
        return $this->showCertificate('english-medium', 'english-medium-certificate', $history, ['course']);
    }

    public function letterOfRecommendation($history)
    {
        return $this->showCertificate('letter-recommendation', 'letter-of-recommendation', $history, ['course']);
    }

    /**
     * Certificates open only from a generated history row; the student and the
     * details printed on the certificate come from that row.
     */
    protected function showCertificate(string $type, string $view, $historyId, array $courseWith)
    {
        $history = $type === 'letter-recommendation'
            ? \App\Models\IssueCertificateLetterRecommendation::findOrFail($historyId)
            : \App\Models\IssueCertificateHistory::where('certificate_type', $type)->findOrFail($historyId);

        $id = $history->admission_id ?? $history->register_id;
        $admission = Admission::findOrFail($id);
        $courseRegistration = CourceRegistration::where('register_id', $id)
            ->with($courseWith)
            ->latest('id')
            ->first();

        $certificateData = $this->resolveCertificateRequestData($history);

        return view($this->modules['folder_path'] . '.' . $view, compact('admission', 'courseRegistration', 'certificateData'));
    }

    public function view($register_id, $course_id)
    {
        $modules = $this->modules;
        $admission = Admission::findOrFail($register_id);

        $course = CourceRegistration::where('register_id', $register_id)
            ->where('course_id', $course_id)
            ->with('course')
            ->firstOrFail();

        $mother_letters = str_split(strtoupper($admission->mother_name));

        $full_name = strtoupper($admission->first_name . ' ' . $admission->last_name . ' ' . $admission->father_name);
        $full_name_letters = str_split($full_name);

        $permanent_address_letters = str_split(strtoupper($admission->permanent_address ?? ''));


        View::share('modules', $modules);
        View::share('mother_letters', $mother_letters);
        View::share('full_name_letters', $full_name_letters);
        View::share('permanent_address_letters', $permanent_address_letters);

        // dd($course);
        return view($modules['folder_path'] . '.view', compact('admission', 'course'));
    }
    public function checkAadharDuplicate(Request $request)
    {
        $aadharCardNo = $request->input('aadhar_card_no');
        $editId = $request->input('edit_id');

        // Remove spaces
        $aadharCardNo = str_replace(' ', '', $aadharCardNo);

        $query = Admission::where('aadhar_card_no', $aadharCardNo);

        if ($editId) {
            $query->where('id', '!=', $editId);
        }

        $exists = $query->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'Aadhaar card number already exists' : ''
        ]);
    }

    public function checkMobileDuplicate(Request $request)
    {
        $mobileNo = $request->input('mobile_no');
        $editId = $request->input('edit_id');

        $query = Admission::where('mobile_no', $mobileNo);

        if ($editId) {
            $query->where('id', '!=', $editId);
        }

        $exists = $query->exists();

        return response()->json([
            'exists' => $exists,
            'message' => $exists ? 'Mobile number already exists' : ''
        ]);
    }
    public function import()
    {
        try {
            $modules = $this->modules;
            $standards = $this->getStandardList();
            return view($modules['folder_path'] . '.import', compact('standards'));
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }

    public function downloadSample()
    {
        $filePath = public_path('samples/import_student_sample.xlsx');

        if (!file_exists($filePath)) {
            $filePath = public_path('uploads/import_student/import_student_sample.xlsx');
        }

        if (file_exists($filePath)) {
            return response()->download($filePath, 'import_student_sample.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]);
        }

        return redirect()->back()->with('error', 'Sample file not found.');
    }

    public function importStore(Request $request)
    {
        $request->validate([
            'import_file' => 'required|mimes:xlsx,xls,csv'
        ]);

        $currentYear = MasterAcademicYear::current()
            ?? MasterAcademicYear::where('status', 'active')->orderByDesc('start_date')->first();

        if (!$currentYear) {
            return back()->withErrors('Please set Current Academic Year in Academic Year Master before importing students.');
        }

        try {
            $import = new StudentImport();
            Excel::import($import, $request->file('import_file'));

            // Fix any older rows still missing academic_year_id
            Admission::whereNull('academic_year_id')->update(['academic_year_id' => $currentYear->id]);

            // Get report from import class
            $report = $import->report;

            // Save report in session so blade can show it
            Session::flash('import_report', $report);


            $message = "Import finished — Inserted: " . count($report['inserted']) .
                ", Skipped: " . count($report['skipped']) .
                ", Errors: " . count($report['errors']);

            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    /**
     * Export admissions to Excel.
     */
    public function exportExcel(Request $request)
    {
        $this->authorizeAdmissionList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);

        $admissions = $this->buildAdmissionExportQuery($request, $scope)->with('courses.course')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        foreach (array_values($columnLabels) as $index => $label) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $label);
        }

        $rowNumber = 2;
        foreach ($admissions as $admission) {
            foreach ($selectedColumns as $columnIndex => $columnKey) {
                $sheet->setCellValueByColumnAndRow(
                    $columnIndex + 1,
                    $rowNumber,
                    $this->formatColumnValueForExcel($admission, $columnKey)
                );
            }
            $rowNumber++;
        }

        if (!empty($selectedColumns)) {
            for ($i = 1; $i <= count($selectedColumns); $i++) {
                $columnLetter = Coordinate::stringFromColumnIndex($i);
                $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
            }
        }

        $fileName = 'admission-' . now()->format('Ymd_His') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Print view for admissions.
     */
    public function printView(Request $request)
    {
        $this->authorizeAdmissionList();

        $scope = strtolower($request->get('scope', 'filtered')) === 'all' ? 'all' : 'filtered';
        $selectedColumns = $this->resolveSelectedColumns($request->input('columns', []));
        $columnLabels = $this->mapColumnLabels($selectedColumns);
        $searchTerm = $scope === 'filtered' ? trim((string) $request->get('search', '')) : null;

        $admissions = $this->buildAdmissionExportQuery($request, $scope)->with('courses.course')->get();

        return view($this->modules['folder_path'] . '.print', [
            'admissions' => $admissions,
            'columnLabels' => $columnLabels,
            'columnKeys' => $selectedColumns,
            'scope' => $scope,
            'searchTerm' => $searchTerm,
            'generatedAt' => now(),
        ]);
    }

    /**
     * Permission check for admission list.
     */
    protected function authorizeAdmissionList(): void
    {
        $canList = Helper::directCan($this->modules['permission_prefix'] . '-list');
        if (!$canList) {
            abort(403, 'User does not have the right permissions.');
        }
    }

    protected function getStandardList(): array
    {
        return \App\Models\Master\MasterStandard::where('status', 'active')->orderBy('id')->pluck('name')->filter()->values()->all();
    }

    /**
     * Values typed directly in the admission form (not picked from the list)
     * are added to their master, so they show up in the dropdown next time.
     */
    protected function syncFormMasters(array $validated, int $userId): array
    {
        $masters = [
            'bus_route_village' => [\App\Models\Master\MasterBusRouteVillage::class, false],
            'cast'              => [\App\Models\Master\MasterCast::class, true],
            'house'             => [\App\Models\Master\MasterHouse::class, false],
            'division'          => [\App\Models\Master\MasterDivision::class, true],
        ];

        foreach ($masters as $field => [$model, $upper]) {
            $value = trim((string) ($validated[$field] ?? ''));
            if ($value === '') {
                continue;
            }
            if ($upper) {
                $value = mb_strtoupper($value);
            }

            $master = $model::where('name', $value)->first();
            if (!$master) {
                $master = $model::create(['name' => $value, 'status' => 'active', 'created_by' => $userId]);
            } elseif ($master->status !== 'active') {
                $master->update(['status' => 'active', 'updated_by' => $userId]);
            }

            $validated[$field] = $master->name;
        }

        return $validated;
    }

    protected function resolveCertificateRequestData($history): array
    {
        $today = now();

        return [
            'issue_date' => $this->formatCertificateDate($history->issue_date ?? null, $today->format('j-m-Y')),
            'academic_year' => trim((string) ($history->academic_year ?? '')) !== ''
                ? trim((string) $history->academic_year)
                : $today->format('Y'),
            'period_from' => '',
            'period_to' => '',
            'recommender_name' => $history->recommender_name ?? 'Hitesh Vadalia',
            'recommender_designation' => $history->recommender_designation ?? 'I/C. Principal',
            'recommender_type' => $history->recommender_type ?? 'principal',
            'faculty_id' => $history->faculty_id ?? null,
        ];
    }

    protected function formatCertificateDate($value, string $default = ''): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return $default;
        }

        $formats = ['d-m-Y', 'j-m-Y', 'Y-m-d', 'd/m/Y', 'j/n/Y'];

        foreach ($formats as $format) {
            try {
                return \Carbon\Carbon::createFromFormat($format, $value)->format('j-m-Y');
            } catch (\Exception $e) {
                continue;
            }
        }

        try {
            return \Carbon\Carbon::parse($value)->format('j-m-Y');
        } catch (\Exception $e) {
            return $default;
        }
    }

    protected function applyAdmissionListFilters($query, Request $request): void
    {
        $yearId = (int) $request->input('filter_academic_year');
        $standard = trim((string) $request->input('filter_standard', ''));
        $division = trim((string) $request->input('filter_division', ''));

        if ($yearId) {
            $query->where(function ($outer) use ($yearId, $standard, $division) {
                $outer->where(function ($current) use ($yearId, $standard, $division) {
                    $current->where('academic_year_id', $yearId);
                    if ($standard !== '') {
                        $current->whereHas('currentStandard', fn ($q) => $q->where('name', $standard));
                    }
                    if ($division !== '') {
                        $current->where('division', $division);
                    }
                })->orWhereHas('classHistories', function ($history) use ($yearId, $standard, $division) {
                    $history->where('academic_year_id', $yearId);
                    if ($standard !== '') {
                        $history->whereHas('standard', fn ($q) => $q->where('name', $standard));
                    }
                    if ($division !== '') {
                        $history->where('division', $division);
                    }
                });
            });

            return;
        }

        if ($standard !== '') {
            $query->whereHas('currentStandard', fn ($q) => $q->where('name', $standard));
        }

        if ($division !== '') {
            $query->where('division', $division);
        }
    }

    /**
     * Build filtered query for export/print.
     */
    protected function buildAdmissionExportQuery(Request $request, string $scope)
    {
        $query = Admission::query()->withTrashed()->orderByDesc('id');

        if ($request->filled('course_id')) {
            $courseId = $request->input('course_id');
            $query->whereHas('courses', function ($q) use ($courseId) {
                $q->where('course_id', $courseId);
            });
        }

        if ($request->filled('batch_id')) {
            $batchId = $request->input('batch_id');
            $query->whereHas('courses', function ($q) use ($batchId) {
                $q->where('batch_id', $batchId);
            });
        }

        if ($request->filled('class_id')) {
            $classId = $request->input('class_id');
            $query->whereHas('courses', function ($q) use ($classId) {
                $q->where('class_id', $classId);
            });
        }

        if ($scope === 'all') {
            return $query;
        }

        $this->applyAdmissionListFilters($query, $request);

        $search = trim((string) $request->get('search', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('father_name', 'like', "%{$search}%")
                    ->orWhere('mobile_no', 'like', "%{$search}%")
                    ->orWhere('aadhar_card_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query;
    }

    protected function resolveSelectedColumns($columns): array
    {
        if (!is_array($columns) || empty($columns)) {
            return $this->defaultExportColumns;
        }

        $validColumns = array_keys($this->exportableColumns);
        $filtered = array_values(array_filter($columns, function ($column) use ($validColumns) {
            return in_array($column, $validColumns, true);
        }));

        return !empty($filtered) ? $filtered : $this->defaultExportColumns;
    }

    protected function mapColumnLabels(array $selectedColumns): array
    {
        $labels = [];
        foreach ($selectedColumns as $columnKey) {
            $labels[$columnKey] = $this->exportableColumns[$columnKey] ?? ucfirst(str_replace('_', ' ', $columnKey));
        }
        return $labels;
    }

    protected function formatColumnValueForExcel(Admission $admission, string $columnKey)
    {
        switch ($columnKey) {
            case 'id':
                return $admission->id;
            case 'biometric_id':
                return $admission->biometric_id ?? '-';
            case 'full_name':
                $first = trim($admission->first_name ?? '');
                $last = trim($admission->last_name ?? '');
                $father = trim($admission->father_name ?? '');
                if ($first !== '' && $father !== '') {
                    $cleanFather = trim(preg_replace('/^' . preg_quote($first, '/') . '\s+/i', '', $father));
                } else {
                    $cleanFather = $father;
                }
                $fullName = trim("{$first} {$last} {$cleanFather}");
                return $fullName !== '' ? $fullName : '-';
            case 'date_of_birth':
                return $admission->date_of_birth
                    ? \Carbon\Carbon::parse($admission->date_of_birth)->format('d-m-Y')
                    : '-';
            case 'admission_date':
                return $admission->admission_date
                    ? \Carbon\Carbon::parse($admission->admission_date)->format('d-m-Y')
                    : '-';
            case 'gender':
                return $admission->gender ?? '-';
            case 'mobile_no':
                return $admission->mobile_no ?? '-';
            case 'parent_mobile_no':
                return $admission->parent_mobile_no ?? '-';
            case 'other_mobile_no':
                return $admission->other_mobile_no ?? '-';
            case 'whatsapp_no':
                return $admission->whatsapp_no ?? '-';
            case 'aadhar_card_no':
                return $admission->aadhar_card_no ?? '-';
            case 'status':
                return $admission->status ? ucfirst($admission->status) : '-';
            case 'created_at':
                return $admission->created_at
                    ? $admission->created_at->format('d-m-Y')
                    : '-';
            case 'course_names':
                $courseNames = $admission->courses
                    ->map(fn($cr) => $cr->course?->course_name)
                    ->filter()
                    ->unique()
                    ->join(', ');
                return $courseNames ?: '-';
            default:
                return data_get($admission, $columnKey, '-');
        }
    }
}
