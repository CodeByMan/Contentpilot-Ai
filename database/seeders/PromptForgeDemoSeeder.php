<?php

namespace Database\Seeders;

use App\Models\billingHistory;
use App\Models\ChatAssistant;
use App\Models\ChatConversation;
use App\Models\Contact;
use App\Models\GeneratedAudio;
use App\Models\GeneratedContent;
use App\Models\GeneratedImage;
use App\Models\Heading;
use App\Models\Plan;
use App\Models\Questions;
use App\Models\Slider;
use App\Models\Template;
use App\Models\TemplateInputFields;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class PromptForgeDemoSeeder extends Seeder
{
    public function run(): void
    {
        Model::unguarded(function (): void {
        $plans = collect([
            ['id' => 1, 'name' => 'Starter', 'monthly_word_limit' => 5000, 'price' => 0, 'templates' => 4],
            ['id' => 2, 'name' => 'Creator', 'monthly_word_limit' => 25000, 'price' => 19, 'templates' => 12],
            ['id' => 3, 'name' => 'Agency', 'monthly_word_limit' => 100000, 'price' => 79, 'templates' => 30],
        ])->map(fn ($plan) => Plan::updateOrCreate(['id' => $plan['id']], $plan));

        $admin = User::updateOrCreate(
            ['email' => 'admin@contentpilot.test'],
            [
                'name' => 'Muhammad Ali Nawaz',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'plan_id' => 3,
                'current_word_usage' => 100000,
                'words_used' => 8750,
                'status' => '1',
                'email_verified_at' => now(),
            ]
        );

        $creator = User::updateOrCreate(
            ['email' => 'creator@contentpilot.test'],
            [
                'name' => 'Ayesha Creator',
                'password' => Hash::make('password'),
                'role' => 'user',
                'plan_id' => 2,
                'current_word_usage' => 25000,
                'words_used' => 4320,
                'status' => '1',
                'email_verified_at' => now(),
            ]
        );

        $client = User::updateOrCreate(
            ['email' => 'client@contentpilot.test'],
            [
                'name' => 'Demo Client',
                'password' => Hash::make('password'),
                'role' => 'user',
                'plan_id' => 1,
                'current_word_usage' => 5000,
                'words_used' => 1250,
                'status' => '1',
                'email_verified_at' => now(),
            ]
        );


        foreach ([
            ['id' => 1, 'title' => 'Create English content faster with AI templates', 'description' => 'Choose a template, describe your idea, and generate polished English copy for blogs, ads, emails, and landing pages.'],
            ['id' => 2, 'title' => 'Generate polished content in three simple steps', 'description' => 'Select a template, provide your topic details, and let the AI generate clean, editable English content in seconds.'],
            ['id' => 3, 'title' => 'Simple pricing for creators and teams', 'description' => 'Start free, then upgrade when you need more templates, higher word limits, and advanced AI tools.'],
        ] as $heading) {
            Heading::updateOrCreate(['id' => $heading['id']], $heading);
        }

        $templates = [
            [
                'title' => 'SEO Blog Post Generator',
                'description' => 'Create optimized long-form blog posts with a clear outline, headings, and search-friendly copy.',
                'category' => 'Blogging',
                'icon' => 'icon ni ni-edit-alt',
                'prompt' => 'Create a complete SEO blog post about {Topic} for {Audience}. Include title, intro, headings, bullet points, and conclusion.',
                'fields' => [
                    ['title' => 'Topic', 'description' => 'Main topic or keyword', 'type' => 'text'],
                    ['title' => 'Audience', 'description' => 'Target readers or customers', 'type' => 'text'],
                ],
            ],
            [
                'title' => 'Product Description Writer',
                'description' => 'Generate persuasive ecommerce descriptions focused on benefits, use cases, and conversion.',
                'category' => 'Ecommerce',
                'icon' => 'icon ni ni-cart',
                'prompt' => 'Write a persuasive product description for {Product_Name}. Highlight these features: {Key_Features}.',
                'fields' => [
                    ['title' => 'Product Name', 'description' => 'Product title', 'type' => 'text'],
                    ['title' => 'Key Features', 'description' => 'Important features to include', 'type' => 'textarea'],
                ],
            ],
            [
                'title' => 'Cold Email Campaign',
                'description' => 'Produce concise B2B outreach emails with a strong value proposition and CTA.',
                'category' => 'Sales',
                'icon' => 'icon ni ni-mail',
                'prompt' => 'Write a cold email for {Company_Type} offering {Service}. Keep it personal, concise, and conversion-focused.',
                'fields' => [
                    ['title' => 'Company Type', 'description' => 'Target business segment', 'type' => 'text'],
                    ['title' => 'Service', 'description' => 'What you are selling', 'type' => 'text'],
                ],
            ],
            [
                'title' => 'Social Media Caption Pack',
                'description' => 'Generate short caption variations for LinkedIn, Instagram, and Facebook.',
                'category' => 'Marketing',
                'icon' => 'icon ni ni-share',
                'prompt' => 'Create 5 social media captions for {Campaign} in a {Tone} tone. Include hashtags and CTA.',
                'fields' => [
                    ['title' => 'Campaign', 'description' => 'Campaign topic', 'type' => 'text'],
                    ['title' => 'Tone', 'description' => 'Friendly, premium, bold, educational, etc.', 'type' => 'text'],
                ],
            ],
        ];

        foreach ($templates as $index => $templateData) {
            $template = Template::updateOrCreate(
                ['title' => $templateData['title']],
                [
                    'description' => $templateData['description'],
                    'category' => $templateData['category'],
                    'icon' => $templateData['icon'],
                    'prompt' => $templateData['prompt'],
                    'is_active' => true,
                    'created_by' => $admin->id,
                ]
            );

            $template->inputFields()->delete();
            foreach ($templateData['fields'] as $fieldIndex => $field) {
                TemplateInputFields::create([
                    'template_id' => $template->id,
                    'title' => $field['title'],
                    'description' => $field['description'],
                    'type' => $field['type'],
                    'is_required' => true,
                    'order' => $fieldIndex,
                ]);
            }

            GeneratedContent::updateOrCreate(
                ['user_id' => $creator->id, 'template_id' => $template->id, 'output' => 'Demo generated output for '.$template->title],
                [
                    'input' => ['demo' => 'seeded portfolio content'],
                    'word_count' => 180 + ($index * 55),
                ]
            );
        }

        billingHistory::updateOrCreate(
            ['user_id' => $creator->id, 'plan_id' => 2],
            [
                'payment_date' => now()->subDays(7),
                'total' => 19,
                'bank_name' => 'Demo Bank',
                'account_holder' => 'Ayesha Creator',
                'account_number' => '**** 4582',
                'status' => 'Paid',
            ]
        );

        ChatAssistant::updateOrCreate(
            ['name' => 'Marketing Strategist'],
            [
                'role_description' => 'Helps users plan campaigns, content calendars, and conversion copy.',
                'welcome_message' => 'Hi! Tell me what you are launching and I will help you shape the campaign.',
                'instructions' => 'Act as a senior SaaS marketing strategist. Be practical, concise, and conversion-focused.',
                'category' => 'Marketing',
                'is_active' => true,
            ]
        );

        $assistant = ChatAssistant::firstWhere('name', 'Marketing Strategist');
        $conversation = ChatConversation::updateOrCreate(
            ['assistant_id' => $assistant->id, 'user_id' => $creator->id, 'message' => 'Help me launch a new AI writing tool.'],
            ['response' => 'Start with a landing page, three use-case campaigns, and a clear free-plan CTA.']
        );
        $conversation->update(['conversation_id' => $conversation->id]);

        GeneratedImage::updateOrCreate(
            ['user_id' => $creator->id, 'prompt' => 'Premium SaaS dashboard hero illustration'],
            ['image_path' => 'upload/generated_image/demo-saas-dashboard.png']
        );

        GeneratedAudio::updateOrCreate(
            ['user_id' => $creator->id, 'prompt' => 'Welcome to ContentPilot, your English content creation workspace.'],
            ['audio_path' => 'upload/audio/demo-welcome.mp3']
        );

        Slider::updateOrCreate(
            ['id' => 1],
            [
                'title' => 'Create premium content with AI in minutes',
                'description' => 'ContentPilot combines English templates, usage limits, documents, images, audio, and assistants in a clean Laravel SaaS dashboard.',
                'image' => null,
                'link' => '/register',
            ]
        );

        foreach ([
            ['title' => 'Does the demo need an OpenAI key?', 'description' => 'Yes. Add OPENAI_API_KEY in .env to generate live content, images, audio, and assistant replies.'],
            ['title' => 'Can users save generated content?', 'description' => 'Yes. Generated content is stored with inputs, template reference, word count, and owner.'],
            ['title' => 'Is there plan-based usage?', 'description' => 'Yes. Plans control monthly word limits and how many templates users can access.'],
        ] as $faq) {
            Questions::updateOrCreate(['title' => $faq['title']], $faq);
        }

        Contact::updateOrCreate(
            ['email' => 'lead@example.com'],
            [
                'name' => 'Recruiter Demo Lead',
                'subject' => 'Portfolio demo inquiry',
                'message' => 'The ContentPilot project looks polished. Please share the GitHub repository.',
            ]
        );
        });
    }
}
