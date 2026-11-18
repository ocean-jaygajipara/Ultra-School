<?php

namespace App\Http\Controllers;

use App\Http\Requests\NotificationSettingRequest;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FCMNotification;
use App\Models\StudentDeviceToken;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Http\Controllers\Controller;
use App\Models\Admission;
use App\Models\NotificationSetting;
use App\Models\Master\MasterAcademicYear;
use App\Models\Master\MasterDivision;
use App\Models\Master\MasterStandard;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class NotificationSettingController extends Controller
{
    /**
     * Module setup
     */
    public $modules = [];
    public function __construct()
    {
        $this->modules = [
            'title' => 'Announcement',
            'folder_path' => 'software.module.notification_setting',
            'route' => 'notification_setting',
            'table_name' => (new NotificationSetting())->getTable(),
            'permission_prefix' => 'notification-setting',
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $modules = $this->modules;
        $modules['login_user_role'] = Helper::getLoginUserRole();
        $modules['permission_list'] = Helper::directCan($modules['permission_prefix'] . '-list');
        $modules['permission_add'] = Helper::directCan($modules['permission_prefix'] . '-create');
        $modules['permission_edit'] = Helper::directCan($modules['permission_prefix'] . '-edit');
        $modules['permission_delete'] = Helper::directCan($modules['permission_prefix'] . '-delete');

        if (!$modules['permission_list']) {
            if ($request->ajax()) {
                return response()->json(['error' => 'User does not have the right permissions.'], 403);
            }
            abort(403, 'User does not have the right permissions.');
        }

        View::share('modules', $modules);

        $columns = [
            (object)['data' => "title", 'name' => 'title', 'td_label' => 'Title'],
            (object)['data' => "body", 'name' => 'body', 'td_label' => 'Message'],
            (object)['data' => "standard_name", 'name' => 'standard_id', 'td_label' => 'Standard'],
            (object)['data' => "class_id", 'name' => 'class_id', 'td_label' => 'Class'],
            (object)['data' => "action", 'name' => 'action', 'td_label' => 'Action', 'orderable' => false, 'searchable' => false, 'className' => 'w-10 text-center'],
        ];
        View::share("columns", $columns);

        try {
            if ($request->ajax()) {
                $data = NotificationSetting::with('standard:id,name');
                $data = $data->orderBy('id', 'desc');
                return DataTables::of($data)
                    ->addIndexColumn()
                    ->filter(function ($query) use ($request) {
                        $searchInput = $request->input('search');
                        $searchValue = is_array($searchInput)
                            ? ($searchInput['value'] ?? '')
                            : (string) ($searchInput ?? '');
                        $searchValue = trim($searchValue);

                        if ($searchValue !== '') {
                            $query->where(function ($q) use ($searchValue) {
                                $q->where('title', 'like', "%{$searchValue}%")
                                    ->orWhere('body', 'like', "%{$searchValue}%");
                            });
                        }
                    })
                    ->addColumn('student_name', fn($row) => $row->student_names)
                    ->addColumn('standard_name', fn($row) => $row->standard?->name ?? '-')
                    ->editColumn('class_id', fn($row) => $row->class_id ?: '-')
                    ->editColumn('body', function ($row) {

                        return $row->body ?? '-';
                    })
                    ->addColumn('action', function ($row) use ($modules) {

                        $btn = '';
                        if ($modules['permission_edit']) {
                            // $btn .= '<a href="' . route($modules["route"] . ".edit", [$row->id]) . '" class="btn btn-light btn-icon mx-1"><i class="fa-solid fa-pen-to-square"></i></a>';
                        }
                        if ($modules['permission_delete']) {
                            $btn .= '<a href="javascript:void(0)" data-id="' . $row->id . '" data-did="' . route($modules["route"] . ".destroy", [$row->id]) . '" class="btn btn-danger btn-icon deletebutton mx-1"><i class="fa-solid fa-trash"></i></a>';
                        }
                        return $btn ?: '-';
                    })
                    ->rawColumns(['action', 'body'])
                    ->make(true);
            }

            return view($modules['folder_path'] . '.index');
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function create()
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        $academicYears = MasterAcademicYear::where('status', 'active')->orderByDesc('start_date')->get();
        $currentAcademicYear = MasterAcademicYear::current();
        $standards = MasterStandard::where('status', 'active')->orderBy('id')->pluck('name', 'id');
        $divisions = MasterDivision::where('status', 'active')->orderBy('name')->pluck('name');
        $students = [];
        $selectedStudents = [];

        View::share('academicYears', $academicYears);
        View::share('currentAcademicYear', $currentAcademicYear);
        View::share('standards', $standards);
        View::share('divisions', $divisions);
        View::share('students', $students);
        View::share('selectedStudents', $selectedStudents);

        return view($modules['folder_path'] . '.form');
    }

    public function store(NotificationSettingRequest $request)
    {
        $validated = $request->validated();

        try {
            $studentIds = array_filter($validated['student_id'], fn($id) => $id !== 'all');
            $validated['created_by'] = Auth::user()->id;

            // Handle image upload
            if ($request->hasFile('image')) {
                $imageFile = $request->file('image');
                $imageName = time() . '_img_' . $imageFile->getClientOriginalName();
                $imagePath = public_path('uploads/notifications/images');
                if (!file_exists($imagePath)) {
                    mkdir($imagePath, 0777, true);
                }
                $imageFile->move($imagePath, $imageName);
                $validated['image'] = 'uploads/notifications/images/' . $imageName;
            }

            // Handle PDF upload
            if ($request->hasFile('pdf')) {
                $pdfFile = $request->file('pdf');
                $pdfName = time() . '_pdf_' . $pdfFile->getClientOriginalName();
                $pdfPath = public_path('uploads/notifications/pdfs');
                if (!file_exists($pdfPath)) {
                    mkdir($pdfPath, 0777, true);
                }
                $pdfFile->move($pdfPath, $pdfName);
                $validated['pdf'] = 'uploads/notifications/pdfs/' . $pdfName;
            }

            $imageUrl = isset($validated['image']) ? asset($validated['image']) : null;
            $pdfUrl = isset($validated['pdf']) ? asset($validated['pdf']) : null;

            // Create a single NotificationSetting record for all selected students
            $notificationData = $validated;
            $notificationData['course_id'] = null;
            $notificationData['batch_id'] = null;
            $notificationData['student_id'] = implode(',', $studentIds);
            if (isset($validated['class_id']) && is_array($validated['class_id'])) {
                $notificationData['class_id'] = implode(',', $validated['class_id']);
            }
            $notification = NotificationSetting::create($notificationData);

            // Prepare notification body
            $cleanBody = $validated['body'];
            $cleanBody = str_ireplace(['<br>', '<br/>', '<br />', '</p>'], "\n", $cleanBody);
            $cleanBody = str_ireplace('<p>', '', $cleanBody);
            $cleanBody = strip_tags($cleanBody);
            $cleanBody = html_entity_decode($cleanBody, ENT_QUOTES, 'UTF-8');
            $cleanBody = preg_replace("/[ \t]+/", " ", $cleanBody);
            $cleanBody = preg_replace("/\r\n|\r/", "\n", $cleanBody);
            $cleanBody = trim($cleanBody);

            foreach ($studentIds as $sid) {
                // Get all the student's device tokens
                $tokens = StudentDeviceToken::where('student_id', $sid)
                    ->pluck('device_token')
                    ->toArray();

                if (count($tokens) > 0) {
                    $androidConfig = AndroidConfig::fromArray([
                        'ttl' => '3600s',
                        'priority' => 'high',
                        'notification' => [
                            'channel_id'   => 'default',
                            'sound'        => 'default',
                            'click_action' => 'OPEN_NOTIFICATION',
                        ],
                        'fcm_options' => [
                            'analytics_label' => 'student_notification',
                        ],
                    ]);

                    foreach ($tokens as $token) {
                        if (!empty($token)) {
                            $fcmNotification = FCMNotification::create($validated['title'], $cleanBody);
                            if ($imageUrl) {
                                $fcmNotification = $fcmNotification->withImageUrl($imageUrl);
                            }

                            $message = CloudMessage::withTarget('token', $token)
                                ->withNotification($fcmNotification)
                                ->withAndroidConfig($androidConfig)
                                ->withData([
                                    'student_id' => (string) $sid,
                                    'title'      => $validated['title'],
                                    'body'       => $cleanBody,
                                    'image'      => $imageUrl ?? '',
                                    'pdf'        => $pdfUrl ?? '',
                                ]);

                            /*
                            try {
                                app('firebase.messaging')->send($message);
                            } catch (\Exception $e) {
                                \Log::error("Failed to send push notification to device token: {$token}. Error: " . $e->getMessage());
                            }
                            */
                        }
                    }
                }
            }

            return Redirect::route($this->modules['route'] . '.index')
                ->withSuccess($this->modules['title'] . ' created successfully');
        } catch (\Exception $e) {
            return Redirect::route('software.dashboard')->withErrors($e->getMessage());
        }
    }



    public function edit(string $id)
    {
        $modules = $this->modules;
        View::share('modules', $modules);

        try {
            $edit = NotificationSetting::findOrFail($id);

            $academicYears = MasterAcademicYear::where('status', 'active')->orderByDesc('start_date')->get();
            $currentAcademicYear = MasterAcademicYear::current();
            $standards = MasterStandard::where('status', 'active')->orderBy('id')->pluck('name', 'id');
            $divisions = MasterDivision::where('status', 'active')->orderBy('name')->pluck('name');
            $selectedStudents = $edit->student_id ? explode(',', $edit->student_id) : [];
            $selectedClasses = $edit->class_id ? array_filter(array_map('trim', explode(',', $edit->class_id))) : [];

            $students = [];
            if ($edit->standard_id) {
                $students = $this->fetchStudentsByStandardClass(
                    (int) $edit->standard_id,
                    $selectedClasses,
                    $edit->academic_year_id ? (int) $edit->academic_year_id : null
                );
            }

            View::share('edit', $edit);
            View::share('academicYears', $academicYears);
            View::share('currentAcademicYear', $currentAcademicYear);
            View::share('standards', $standards);
            View::share('divisions', $divisions);
            View::share('selectedClasses', $selectedClasses);
            View::share('students', $students);
            View::share('selectedStudents', $selectedStudents);

            return view($modules['folder_path'] . '.form');
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function update(NotificationSettingRequest $request, string $id)
    {
        $validated = $request->validated();

        try {
            $notificationSetting = NotificationSetting::findOrFail($id);

            $studentIds = array_filter($validated['student_id'], fn($id) => $id !== 'all');
            $validated['course_id'] = null;
            $validated['batch_id'] = null;
            $validated['student_id'] = implode(',', $studentIds);
            if (isset($validated['class_id']) && is_array($validated['class_id'])) {
                $validated['class_id'] = implode(',', $validated['class_id']);
            }
            $validated['updated_by'] = Auth::user()->id;

            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($notificationSetting->image && file_exists(public_path($notificationSetting->image))) {
                    unlink(public_path($notificationSetting->image));
                }
                $imageFile = $request->file('image');
                $imageName = time() . '_img_' . $imageFile->getClientOriginalName();
                $imagePath = public_path('uploads/notifications/images');
                if (!file_exists($imagePath)) {
                    mkdir($imagePath, 0777, true);
                }
                $imageFile->move($imagePath, $imageName);
                $validated['image'] = 'uploads/notifications/images/' . $imageName;
            }

            // Handle PDF upload
            if ($request->hasFile('pdf')) {
                // Delete old PDF if exists
                if ($notificationSetting->pdf && file_exists(public_path($notificationSetting->pdf))) {
                    unlink(public_path($notificationSetting->pdf));
                }
                $pdfFile = $request->file('pdf');
                $pdfName = time() . '_pdf_' . $pdfFile->getClientOriginalName();
                $pdfPath = public_path('uploads/notifications/pdfs');
                if (!file_exists($pdfPath)) {
                    mkdir($pdfPath, 0777, true);
                }
                $pdfFile->move($pdfPath, $pdfName);
                $validated['pdf'] = 'uploads/notifications/pdfs/' . $pdfName;
            }

            $notificationSetting->update($validated);

            foreach ($studentIds as $sid) {
                Helper::sendPushNotification($sid, $validated['title'], $validated['body']);
            }

            return Redirect::route($this->modules['route'] . '.index')
                ->withSuccess($this->modules['title'] . ' updated successfully');
        } catch (\Exception $e) {
            return Redirect::route('dashboard')->withErrors($e->getMessage());
        }
    }

    public function destroy(Request $request, string $id)
    {
        try {
            $notificationSetting = NotificationSetting::findOrFail($id);
            if ($notificationSetting->image && file_exists(public_path($notificationSetting->image))) {
                unlink(public_path($notificationSetting->image));
            }
            if ($notificationSetting->pdf && file_exists(public_path($notificationSetting->pdf))) {
                unlink(public_path($notificationSetting->pdf));
            }
            $notificationSetting->delete();

            // If AJAX request, return JSON success
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => $this->modules['title'] . ' deleted successfully.',
                ]);
            }

            // For normal requests, fallback redirect
            return redirect()->route($this->modules['route'] . '.index')
                ->withSuccess($this->modules['title'] . ' deleted successfully.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }

            return redirect()->route('dashboard')->withErrors($e->getMessage());
        }
    }



    public function getStudentsByStandard(Request $request, $standard_id)
    {
        try {
            $classIds = $request->input('class_id', []);
            if (!is_array($classIds)) {
                $classIds = array_filter(array_map('trim', explode(',', (string) $classIds)));
            }

            $academicYearId = $request->filled('academic_year_id')
                ? (int) $request->input('academic_year_id')
                : null;

            $students = $this->fetchStudentsByStandardClass((int) $standard_id, $classIds, $academicYearId);

            return response()->json($students);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function fetchStudentsByStandardClass(int $standardId, array $divisions = [], ?int $academicYearId = null): array
    {
        $divisions = array_values(array_filter(array_map('trim', $divisions)));
        if ($standardId <= 0) {
            return [];
        }

        $query = Admission::query()
            ->select('id', 'first_name', 'last_name', 'father_name', 'gr_no', 'division')
            ->where('current_std_id', $standardId);

        if ($academicYearId) {
            $query->where('academic_year_id', $academicYearId);
        }

        if (!empty($divisions)) {
            $query->whereIn('division', $divisions);
        }

        return $query
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(function ($admission) {
                $fullName = trim(preg_replace(
                    '/\s+/',
                    ' ',
                    ($admission->first_name ?? '') . ' ' . ($admission->last_name ?? '') . ' ' . ($admission->father_name ?? '')
                ));
                $label = $fullName !== '' ? $fullName : ('Student #' . $admission->id);
                if (!empty($admission->gr_no)) {
                    $label .= ' (GR: ' . $admission->gr_no . ')';
                }
                if (!empty($admission->division)) {
                    $label .= ' - ' . $admission->division;
                }

                return [
                    'id' => $admission->id,
                    'name' => $label,
                ];
            })
            ->values()
            ->toArray();
    }
}
