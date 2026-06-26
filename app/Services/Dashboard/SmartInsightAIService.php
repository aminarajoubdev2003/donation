<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SmartInsightAIService
{
    private string $file = 'dashboard_ai_insights.json';

    public function generateInsights(array $data): array
    {
        $prompt = $this->buildPrompt($data);

        try {

            $response = Http::timeout(60)->post(
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
                    ],
                    'generationConfig' => [
                        'responseMimeType' => 'application/json'
                    ]
                ]
            );

            if (!$response->successful()) {

                Log::error('Gemini API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return $this->getLastInsights();
            }

            $responseData = $response->json();

            if (empty($responseData['candidates'])) {

                Log::error('Gemini No Candidates', [
                    'response' => $responseData
                ]);

                return $this->getLastInsights();
            }

            $content = data_get(
                $responseData,
                'candidates.0.content.parts.0.text'
            );

            if (!$content) {

                Log::error('Gemini Empty Content', [
                    'response' => $responseData
                ]);

                return $this->getLastInsights();
            }

            $content = trim($content);

            $decoded = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {

                Log::error('Gemini JSON Decode Error', [
                    'error' => json_last_error_msg(),
                    'content' => $content
                ]);

                return $this->getLastInsights();
            }

            $result = [
                'summary' => $decoded['summary'] ?? null,
                'alerts' => $decoded['alerts'] ?? [],
                'recommendations' => $decoded['recommendations'] ?? [],
                'opportunities' => $decoded['opportunities'] ?? []
            ];

            // حفظ آخر تحليل ناجح
            Storage::disk('local')->put(
                $this->file,
                json_encode(
                    $result,
                    JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
                )
            );

            return $result;

        } catch (\Exception $e) {

            Log::error('Gemini Exception', [
                'message' => $e->getMessage()
            ]);

            return $this->getLastInsights();
        }
    }

    private function getLastInsights(): array
    {
        try {

            if (Storage::disk('local')->exists($this->file)) {

                $data = json_decode(
                    Storage::disk('local')->get($this->file),
                    true
                );

                if (is_array($data)) {
                    return $data;
                }
            }

        } catch (\Exception $e) {

            Log::error('Read AI Insights File Error', [
                'message' => $e->getMessage()
            ]);
        }

        return [
            'summary' => 'لا توجد تحليلات سابقة متاحة.',
            'alerts' => [],
            'recommendations' => [],
            'opportunities' => []
        ];
    }

    private function buildPrompt(array $data): string
    {
        return "
أنت محلل مالي احترافي متخصص في تحليل بيانات منصات التبرعات.

قواعد الإجابة:
- أجب باللغة العربية الفصحى فقط.
- لا تستخدم أي كلمة أو عبارة باللغة الإنجليزية.
- اعتمد حصراً على البيانات المرسلة إليك.
- لا تخمن أي رقم أو معلومة غير موجودة في البيانات.
- لا تنشئ إجماليات مالية جديدة من خلال جمع قيم المشاريع.
- لا تستخدم تقديرات أو أحكاماً عامة غير مدعومة بالأرقام الموجودة.
- لا تصف أي قيمة مالية أو عدد متبرعين بأنه مرتفع أو منخفض أو كبير أو صغير إلا إذا كانت البيانات تحتوي على معيار واضح للمقارنة.
- لا تعتبر اختلاف طبيعة المؤشرات المالية تناقضاً.
- إذا وجدت تناقضاً رقمياً حقيقياً داخل البيانات، اذكره فقط داخل قسم alerts.
- اجعل التحليل مناسباً للعرض داخل لوحة تحكم إدارية.
- اجعل الملخص واضحاً ومختصراً من 3 إلى 5 أسطر.
- اجعل التنبيهات والتوصيات والفرص على شكل نقاط عملية ومباشرة.
- يجب أن تكون جميع الملاحظات مبنية مباشرة على البيانات المقدمة فقط.

قواعد تحليل المشاريع:
- عدد المشاريع المكتملة وغير المكتملة يعبر فقط عن حالة المشاريع ككيانات مستقلة.
- عدم وجود مشاريع مكتملة لا يعني فشل التمويل أو ضعف الأداء ما لم توجد بيانات تدعم ذلك.
- نسبة التقدم في التمويل تعبر عن نسبة التمويل المحقق من إجمالي تكلفة تفاصيل المشاريع، ولا تمثل نسبة المشاريع المكتملة.
- وجود نسبة تمويل مرتفعة أو متوسطة لا يتعارض مع عدم اكتمال المشاريع.
- لا تستنتج وجود أزمة مالية أو نقص في التبرعات إلا إذا كانت البيانات تحتوي على مؤشر صريح على ذلك.
- قيمة المبلغ المتبقي للتفاصيل النشطة تمثل فقط التفاصيل التي بدأت عمليات دفع فعلية، ولا تمثل إجمالي العجز المالي لجميع المشاريع.
- المبالغ المتبقية داخل قائمة المشاريع الأكثر تعثراً تعبر عن احتياج كل مشروع على حدة، ولا يجوز جمعها للحصول على إجمالي احتياج المنصة.
- المبلغ المتبقي للتفاصيل النشطة مؤشر مستقل عن المبالغ المتبقية للمشاريع الأكثر تعثراً.
- لا تقارن بين المبلغ المتبقي للتفاصيل النشطة والمبالغ المتبقية للمشاريع الأكثر تعثراً.
- لا تعتبر الفرق بينهما مشكلة أو تناقضاً.
- لا تعتبر المشروع غير ممول إلا إذا كانت قيمة total_paid تساوي صفر.
- وجود مبلغ مدفوع للمشروع يعني أنه تلقى تمويلاً حتى لو كانت نسبة التأخر مرتفعة.
- ارتفاع نسبة التأخر يعني وجود احتياج تمويلي متبقٍ في ذلك المشروع فقط.
-لا تعتبر المستخدم الذي نمطه أدمن متبرع هو ليس متبرع بل مدير المنصة

قواعد تحليل الحملات:
- يمكن الإشارة إلى الحملات ذات أعلى التبرعات كحملات ذات أداء أفضل مقارنة ببقية الحملات الموجودة في البيانات فقط.
- لا تفترض أسباب نجاح أي حملة ما لم توجد بيانات تدعم ذلك.

تعليمات الإخراج:
- أعد النتيجة بصيغة JSON صحيحة فقط.
- لا تكتب أي نص قبل أو بعد JSON.
- لا تستخدم markdown.

{
  \"summary\": \"...\",
  \"alerts\": [],
  \"recommendations\": [],
  \"opportunities\": []
}

الإحصائيات العامة:
- إجمالي التبرعات: {$data['total_donations']}
- عدد المتبرعين: {$data['total_donors']}
- عدد المشاريع: {$data['total_projects']}
- عدد المشاريع المكتملة: {$data['completed_projects']}
- عدد المشاريع غير المكتملة: {$data['uncompleted_projects']}
- نسبة التقدم في التمويل: {$data['funding_progress_rate']}
- المبلغ المتبقي للتفاصيل النشطة: {$data['active_details_funding_gap']}

المشاريع الأكثر تعثراً:
" .
json_encode(
    $data['most_delayed_projects'],
    JSON_UNESCAPED_UNICODE
)
.
"

أفضل الحملات حسب حجم التبرعات:
" .
json_encode(
    $data['top_campaigns'],
    JSON_UNESCAPED_UNICODE
)
.
"
";
    }
}
