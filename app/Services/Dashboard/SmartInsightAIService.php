<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmartInsightAIService
{
    public function generateInsights(array $data): array
    {
        $prompt = $this->buildPrompt($data);

        try {

            $response = Http::timeout(60)
                ->post(
                    'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . env('GEMINI_API_KEY'),
                    [
                        'contents' => [
                            [
                                'parts' => [
                                    [
                                        'text' => $prompt
                                    ]
                                ]
                            ]
                        ]
                    ]
                );

            if (!$response->successful()) {

                Log::error('Gemini Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return [
                    'summary' => null,
                    'alerts' => [],
                    'recommendations' => [],
                    'opportunities' => []
                ];
            }

            $content = $response->json(
                'candidates.0.content.parts.0.text'
            );

            if (!$content) {

                return [
                    'summary' => null,
                    'alerts' => [],
                    'recommendations' => [],
                    'opportunities' => []
                ];
            }

            // إزالة markdown إذا أعاد Gemini JSON داخل ```json
            $content = preg_replace('/```json|```/', '', $content);
            $content = trim($content);

            $decoded = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {

                Log::error('Gemini JSON Parse Error', [
                    'content' => $content
                ]);

                return [
                    'summary' => $content,
                    'alerts' => [],
                    'recommendations' => [],
                    'opportunities' => []
                ];
            }

            return $decoded;

        } catch (\Exception $e) {

            Log::error('Gemini Exception', [
                'message' => $e->getMessage()
            ]);

            return [
                'summary' => 'حدث خطأ أثناء توليد التحليلات.',
                'alerts' => [],
                'recommendations' => [],
                'opportunities' => []
            ];
        }
        
    }
    

    private function buildPrompt(array $data): string
    {
        return "
أنت محلل مالي احترافي لمنصة تبرعات.

حلل البيانات التالية.

أعد النتيجة بصيغة JSON فقط بدون أي شرح إضافي.

يجب أن يكون الشكل هكذا تماماً:

{
  \"summary\": \"...\",
  \"alerts\": [
    \"...\"
  ],
  \"recommendations\": [
    \"...\"
  ],
  \"opportunities\": [
    \"...\"
  ]
}

لا تكتب أي نص خارج JSON.

البيانات:

إجمالي التبرعات:
{$data['total_donations']}

عدد المتبرعين:
{$data['total_donors']}

عدد المشاريع:
{$data['total_projects']}

عدد المشاريع المكتملة:
{$data['completed_projects']}

عدد المشاريع غير المكتملة:
{$data['uncompleted_projects']}

نسبة الإنجاز:
{$data['completion_rate']}

إجمالي المبالغ المتبقية:
{$data['total_outstanding_amounts']}

المشاريع الأكثر تعثراً:
" . json_encode(
            $data['most_delayed_projects'],
            JSON_UNESCAPED_UNICODE
        ) . "

أفضل الحملات:
" . json_encode(
            $data['top_campaigns'],
            JSON_UNESCAPED_UNICODE
        );
    }
}
