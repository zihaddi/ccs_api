<?php


namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Survey\StoreSurveyRequest;
use App\Http\Requests\Admin\Survey\UpdateSurveyRequest;
use App\Http\Resources\Admin\Survey\SurveyResource;
use App\Http\Traits\HttpResponses;
use App\Models\Survey;
use App\Constants\Constants;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SurveyController extends Controller
{
    use HttpResponses;

    public function index(Request $request)
    {
        try {
            $query = Survey::with(['createdBy', 'modifiedBy', 'responses']);

            // Apply filters
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('question', 'like', '%' . $search . '%')
                      ->orWhere('question_bn', 'like', '%' . $search . '%');
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('trashed')) {
                if ($request->trashed === 'with') {
                    $query->withTrashed();
                } elseif ($request->trashed === 'only') {
                    $query->onlyTrashed();
                }
            }

            // Order by latest
            $query->orderBy('created_at', 'desc');

            // Paginate or get all
            if ($request->filled('paginate') && $request->paginate == true) {
                $surveys = $query->paginate($request->length ?? 15)->withQueryString();
            } else {
                $surveys = $query->get();
            }

            $data = SurveyResource::collection($surveys)->response()->getData();
            return $this->success($data, Constants::GETALL, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function store(StoreSurveyRequest $request)
    {
        try {
            $survey = Survey::create([
                'question' => $request->question,
                'question_bn' => $request->question_bn,
                'status' => $request->status,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);

            $survey->load(['createdBy', 'modifiedBy', 'responses']);
            return $this->success(new SurveyResource($survey), Constants::STORE, Response::HTTP_CREATED, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function show($id)
    {
        try {
            $survey = Survey::with(['createdBy', 'modifiedBy', 'responses'])
                          ->withTrashed()
                          ->findOrFail($id);

            return $this->success(new SurveyResource($survey), Constants::SHOW, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function update(UpdateSurveyRequest $request, $id)
    {
        try {
            $survey = Survey::withTrashed()->findOrFail($id);

            $survey->update([
                'question' => $request->question,
                'question_bn' => $request->question_bn,
                'status' => $request->status,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
            ]);

            $survey->load(['createdBy', 'modifiedBy', 'responses']);
            return $this->success(new SurveyResource($survey), Constants::UPDATE, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function destroy($id)
    {
        try {
            $survey = Survey::findOrFail($id);
            $survey->delete();

            return $this->success(null, Constants::DESTROY, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function restore($id)
    {
        try {
            $survey = Survey::withTrashed()->findOrFail($id);
            $survey->restore();

            $survey->load(['createdBy', 'modifiedBy', 'responses']);
            return $this->success(new SurveyResource($survey), Constants::RESTORE, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function forceDelete($id)
    {
        try {
            $survey = Survey::withTrashed()->findOrFail($id);
            $survey->forceDelete();

            return $this->success(null, Constants::DESTROY, Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function getSurveyStats()
    {
        try {
            $stats = [
                'total_surveys' => Survey::count(),
                'active_surveys' => Survey::where('status', true)->count(),
                'inactive_surveys' => Survey::where('status', false)->count(),
                'current_active_survey' => Survey::active()->latest()->first() ? 1 : 0,
                'total_responses' => \App\Models\SurveyResponse::count(),
            ];

            return $this->success($stats, 'Survey statistics retrieved successfully', Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function getSurveyResponses($id)
    {
        try {
            $survey = Survey::with(['responses' => function($query) {
                $query->orderBy('created_at', 'desc');
            }])->findOrFail($id);

            $responses = $survey->responses->map(function ($response) {
                return [
                    'id' => $response->id,
                    'response' => $response->response,
                    'ip_address' => $response->ip_address,
                    'user_agent' => $response->user_agent,
                    'created_at' => $response->created_at->format('Y-m-d H:i:s'),
                ];
            });

            $data = [
                'survey' => new SurveyResource($survey),
                'responses' => $responses,
            ];

            return $this->success($data, 'Survey responses retrieved successfully', Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}