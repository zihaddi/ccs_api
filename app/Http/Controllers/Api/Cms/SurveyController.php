<?php


namespace App\Http\Controllers\Api\Cms;

use App\Http\Controllers\Controller;
use App\Http\Resources\Cms\Survey\SurveyResource;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Traits\HttpResponses;

class SurveyController extends Controller
{
    use HttpResponses;

    public function getCurrentSurvey()
    {
        try {
            $survey = Survey::with('responses')
                          ->active()
                          ->latest()
                          ->first();

            if (!$survey) {
                return $this->error(null, 'No active survey found', Response::HTTP_NOT_FOUND, false);
            }

            return $this->success(new SurveyResource($survey), 'Current survey retrieved successfully', Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function submitResponse(Request $request, $surveyId)
    {
        try {
            $request->validate([
                'response' => 'required|in:yes,no,no_idea'
            ]);

            $survey = Survey::active()->findOrFail($surveyId);

            // Check if user already responded (based on IP)
            $ipAddress = $request->ip();
            $existingResponse = SurveyResponse::where('survey_id', $surveyId)
                                            ->where('ip_address', $ipAddress)
                                            ->first();

            if ($existingResponse) {
                return $this->error(null, 'You have already responded to this survey', Response::HTTP_CONFLICT, false);
            }

            $response = SurveyResponse::create([
                'survey_id' => $surveyId,
                'response' => $request->response,
                'ip_address' => $ipAddress,
                'user_agent' => $request->userAgent(),
            ]);

            // Return updated survey data
            $survey->load('responses');
            return $this->success(new SurveyResource($survey), 'Response submitted successfully', Response::HTTP_CREATED, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function getSurveyResults($surveyId)
    {
        try {
            $survey = Survey::with('responses')->findOrFail($surveyId);

            return $this->success(new SurveyResource($survey), 'Survey results retrieved successfully', Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }

    public function getOlderSurveys()
    {
        try {
            $surveys = Survey::with('responses')
                           ->where('status', true)
                           ->where(function ($query) {
                               $query->where('end_date', '<', now())
                                     ->orWhere(function ($q) {
                                         $q->whereNotNull('end_date')
                                           ->where('end_date', '<', now());
                                     });
                           })
                           ->orderBy('created_at', 'desc')
                           ->get();

            $data = SurveyResource::collection($surveys);
            return $this->success($data, 'Older surveys retrieved successfully', Response::HTTP_OK, true);
        } catch (\Exception $e) {
            return $this->error(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR, false);
        }
    }
}