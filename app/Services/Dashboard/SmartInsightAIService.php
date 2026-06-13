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
أنت محلل مالي احترافي متخصص في تحليل بيانات منصات التبرعات.

مهم جداً:
- أجب باللغة العربية الفصحى فقط.
- لا تستخدم أي كلمات أو جمل باللغة الإنجليزية.
- اعتمد حصراً على البيانات المرسلة إليك.
- لا تخمن أي أرقام أو معلومات غير موجودة.
- لا تعتبر اختلاف أنواع المؤشرات تناقضاً.
- إذا وجدت تناقضاً حقيقياً في الأرقام أو البيانات، اذكره فقط ضمن قسم alerts.
- اجعل التحليل مناسباً للعرض داخل لوحة تحكم إدارية Dashboard.
- اجعل الملخص واضحاً ومختصراً من 3 إلى 5 أسطر.
- اجعل التنبيهات والتوصيات والفرص على شكل نقاط عملية ومباشرة.
- ركز على الوضع المالي، حالة المشاريع، المخاطر، وفرص تحسين الأداء.

مهم لفهم البيانات:
- عدد المشاريع المكتملة وغير المكتملة يعبر عن حالة المشاريع ككيانات مستقلة.
- نسبة الإنجاز تعبر عن نسبة التمويل المنجز من إجمالي تكلفة تفاصيل المشاريع، وليست نسبة عدد المشاريع المكتملة.
- إجمالي المبالغ المتبقية يعبر عن المبالغ المتبقية في التفاصيل التي بدأت عمليات دفع فعلية فقط، وليس إجمالي العجز المالي لجميع المشاريع.
- المشاريع الأكثر تعثراً تعرض مقدار العجز والتمويل المتبقي لكل مشروع على حدة.

أعد النتيجة بصيغة JSON صحيحة فقط بدون أي شرح أو نص إضافي.

يجب أن يكون شكل JSON كالتالي تماماً:

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

لا تكتب أي شيء قبل أو بعد JSON.

البيانات:

الإحصائيات العامة:
- إجمالي التبرعات: {$data['total_donations']}
- عدد المتبرعين: {$data['total_donors']}
- عدد المشاريع: {$data['total_projects']}
- عدد المشاريع المكتملة: {$data['completed_projects']}
- عدد المشاريع غير المكتملة: {$data['uncompleted_projects']}
- نسبة التقدم في التمويل: {$data['funding_progress_rate']}
- إجمالي المبالغ المتبقية للمشاريع النشطة: {$data['active_details_remaining_amount']}

المشاريع الأكثر تعثراً:
" . json_encode(
        $data['most_delayed_projects'],
        JSON_UNESCAPED_UNICODE
    ) . "

أفضل الحملات من حيث حجم التبرعات:
" . json_encode(
        $data['top_campaigns'],
        JSON_UNESCAPED_UNICODE
    ) . "
";
}
}
