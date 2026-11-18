<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Master\MasterSubject;
use App\Models\StudentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class StudentRequestController extends Controller
{
    /**
     * Create student request — shows on web panel /student-requests
     */
    public function request_form(Request $request)
    {
        try {
            $student = Auth::guard('student-api')->user();
            if (!$student) {
                return $this->sendError('Unauthorized.', [], [], 401);
            }

            // Accept request_subject_id (preferred) or legacy subject_id
            if ($request->filled('request_subject_id') && !$request->filled('subject_id')) {
                $request->merge(['subject_id' => $request->input('request_subject_id')]);
            }

            $validator = Validator::make($request->all(), [
                'request_subject_id' => 'nullable|integer|exists:master_subjects,id',
                'subject_id'         => 'nullable|integer|exists:master_subjects,id',
                'subject'            => 'nullable|string|max:255',
                'detail'             => 'required|string|max:1000',
            ]);

            if ($validator->fails()) {
                return $this->sendError(
                    $validator->errors()->first(),
                    $validator->errors(),
                    [],
                    422
                );
            }

            $subjectId = (int) ($request->input('request_subject_id') ?: $request->input('subject_id', 0));
            $subjectName = trim((string) $request->input('subject', ''));

            if ($subjectId > 0) {
                $master = MasterSubject::where('id', $subjectId)->where('status', 'active')->first();
                if (!$master) {
                    return $this->sendError('Selected subject not found.', [], [], 422);
                }
                $subjectName = (string) $master->name;
            }

            if ($subjectName === '') {
                return $this->sendError('Subject is required. Pass request_subject_id or subject.', [], [], 422);
            }

            $row = StudentRequest::create([
                'student_id' => $student->id,
                'subject_id' => $subjectId > 0 ? $subjectId : null,
                'subject'    => $subjectName,
                'detail'     => trim((string) $request->input('detail')),
                'status'     => 'Pending',
                'created_by' => $student->id,
            ]);

            return $this->sendResponse([
                'id'                  => (int) $row->id,
                'request_subject_id'  => $row->subject_id ? (int) $row->subject_id : null,
                'subject_name'        => (string) $row->subject,
                'detail'              => (string) $row->detail,
                'status'              => (string) ($row->status ?? 'Pending'),
                'created_at'          => $row->created_at?->format('Y-m-d H:i:s'),
            ], 'Request submitted successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Update own pending student request
     */
    public function request_update(Request $request)
    {
        try {
            $student = Auth::guard('student-api')->user();
            if (!$student) {
                return $this->sendError('Unauthorized.', [], [], 401);
            }

            if ($request->filled('request_subject_id') && !$request->filled('subject_id')) {
                $request->merge(['subject_id' => $request->input('request_subject_id')]);
            }

            $validator = Validator::make($request->all(), [
                'id'                 => 'required|integer',
                'request_subject_id' => 'nullable|integer|exists:master_subjects,id',
                'subject_id'         => 'nullable|integer|exists:master_subjects,id',
                'subject'            => 'nullable|string|max:255',
                'detail'             => 'required|string|max:1000',
            ]);

            if ($validator->fails()) {
                return $this->sendError(
                    $validator->errors()->first(),
                    $validator->errors(),
                    [],
                    422
                );
            }

            $row = StudentRequest::where('id', (int) $request->input('id'))
                ->where('student_id', $student->id)
                ->first();

            if (!$row) {
                return $this->sendError('Request not found.', [], [], 404);
            }

            if (strtolower((string) ($row->status ?? 'Pending')) === 'completed') {
                return $this->sendError('Completed request cannot be edited.', [], [], 422);
            }

            $subjectId = (int) ($request->input('request_subject_id') ?: $request->input('subject_id', $row->subject_id ?: 0));
            $subjectName = trim((string) $request->input('subject', ''));

            if ($subjectId > 0) {
                $master = MasterSubject::where('id', $subjectId)->where('status', 'active')->first();
                if (!$master) {
                    return $this->sendError('Selected subject not found.', [], [], 422);
                }
                $subjectName = (string) $master->name;
            }

            if ($subjectName === '') {
                $subjectName = (string) ($row->subject ?? '');
            }

            if ($subjectName === '') {
                return $this->sendError('Subject is required. Pass request_subject_id or subject.', [], [], 422);
            }

            $row->subject_id = $subjectId > 0 ? $subjectId : null;
            $row->subject = $subjectName;
            $row->detail = trim((string) $request->input('detail'));
            $row->updated_by = $student->id;
            $row->save();

            return $this->sendResponse([
                'id'                 => (int) $row->id,
                'request_subject_id' => $row->subject_id ? (int) $row->subject_id : null,
                'subject_name'       => (string) $row->subject,
                'detail'             => (string) $row->detail,
                'response'           => (string) ($row->response ?? ''),
                'status'             => (string) ($row->status ?? 'Pending'),
                'created_at'         => $row->created_at?->format('Y-m-d H:i:s'),
            ], 'Request updated successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Logged-in student's own request list
     */
    public function request_list()
    {
        try {
            $student = Auth::guard('student-api')->user();
            if (!$student) {
                return $this->sendError('Unauthorized.', [], [], 401);
            }

            $requests = StudentRequest::where('student_id', $student->id)
                ->with(['subjectRelation:id,name'])
                ->orderByDesc('id')
                ->get()
                ->map(function ($item) {
                    $subjectName = $item->getRawOriginal('subject');
                    if (!$subjectName && $item->subjectRelation) {
                        $subjectName = $item->subjectRelation->name;
                    }

                    return [
                        'id'                 => (int) $item->id,
                        'request_subject_id' => $item->subject_id ? (int) $item->subject_id : null,
                        'subject_name'       => (string) ($subjectName ?? ''),
                        'detail'             => (string) ($item->detail ?? ''),
                        'response'           => (string) ($item->response ?? ''),
                        'status'             => (string) ($item->status ?? 'Pending'),
                        'created_at'         => $item->created_at?->format('Y-m-d H:i:s'),
                    ];
                })
                ->values();

            return $this->sendResponse($requests, 'Request list fetched successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }

    /**
     * Active subjects from admin Request Subject (/request-subject)
     */
    public function request_subject_list()
    {
        try {
            $student = Auth::guard('student-api')->user();
            if (!$student) {
                return $this->sendError('Unauthorized.', [], [], 401);
            }

            $subjects = MasterSubject::query()
                ->where('status', 'active')
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(function ($row) {
                    return [
                        'id'   => (int) $row->id,
                        'name' => (string) ($row->name ?? ''),
                    ];
                })
                ->values();

            return $this->sendResponse($subjects, 'Request Subject list fetched successfully.');
        } catch (\Exception $e) {
            return $this->sendError('Something went wrong.', $e->getMessage(), [], 500);
        }
    }
}
