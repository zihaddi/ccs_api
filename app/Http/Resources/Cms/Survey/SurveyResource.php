<?php


namespace App\Http\Resources\Cms\Survey;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $responseCounts = $this->getResponseCounts();
        $totalVotes = $this->getTotalVotes();

        return [
            'id' => $this->id,
            'question' => $this->question,
            'question_bn' => $this->question_bn,
            'status' => $this->status,
            'start_date' => $this->start_date?->format('Y-m-d H:i:s'),
            'end_date' => $this->end_date?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            'total_votes' => $totalVotes,
            'response_counts' => [
                'yes' => $responseCounts['yes'] ?? 0,
                'no' => $responseCounts['no'] ?? 0,
                'no_idea' => $responseCounts['no_idea'] ?? 0,
            ],
            'response_percentages' => [
                'yes' => $totalVotes > 0 ? round((($responseCounts['yes'] ?? 0) / $totalVotes) * 100, 1) : 0,
                'no' => $totalVotes > 0 ? round((($responseCounts['no'] ?? 0) / $totalVotes) * 100, 1) : 0,
                'no_idea' => $totalVotes > 0 ? round((($responseCounts['no_idea'] ?? 0) / $totalVotes) * 100, 1) : 0,
            ],
        ];
    }
}