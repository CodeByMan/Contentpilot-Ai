<?php

namespace Database\Seeders;

use App\Enums\PaymentStatus;
use App\Enums\UserRole;
use App\Models\BillingHistory;
use App\Models\ChatAssistant;
use App\Models\ChatConversation;
use App\Models\Contact;
use App\Models\GeneratedAudio;
use App\Models\GeneratedContent;
use App\Models\GeneratedImage;
use App\Models\Plan;
use App\Models\Template;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('Demo content was not seeded in production.');

            return;
        }

        $plans = Plan::query()->get()->keyBy('name');

        if (! $plans->has('Starter') || ! $plans->has('Creator') || ! $plans->has('Agency')) {
            $this->call(ApplicationReferenceSeeder::class);
            $plans = Plan::query()->get()->keyBy('name');
        }

        $users = $this->seedUsers($plans);
        $templateOwner = User::query()->where('role', UserRole::Admin->value)->oldest()->first() ?? $users->first();
        $templates = $this->seedTemplates($templateOwner);

        $this->clearPreviousDemoActivity();
        $this->seedDocuments($users, $templates);
        $this->seedImages($users);
        $this->seedAudio($users);
        $this->seedPayments($users, $plans);
        $this->seedAssistantsAndConversations($users);
        $this->seedContacts();
        $this->syncUsageCounters($users);
    }

    /** @return \Illuminate\Support\Collection<int, User> */
    private function seedUsers($plans)
    {
        $profiles = [
            [
                'name' => 'Ayesha Khan',
                'email' => 'ayesha.creator@contentpilot.demo',
                'plan' => 'Creator',
                'phone' => '+92 300 555 0141',
                'address' => 'Karachi, Pakistan',
            ],
            [
                'name' => 'Hamza Ali',
                'email' => 'hamza.marketing@contentpilot.demo',
                'plan' => 'Agency',
                'phone' => '+92 301 555 0198',
                'address' => 'Lahore, Pakistan',
            ],
            [
                'name' => 'Sara Malik',
                'email' => 'sara.studio@contentpilot.demo',
                'plan' => 'Starter',
                'phone' => '+92 302 555 0107',
                'address' => 'Islamabad, Pakistan',
            ],
        ];

        return collect($profiles)->map(function (array $profile) use ($plans): User {
            $plan = $plans->get($profile['plan']);
            $user = User::withTrashed()->firstOrNew(['email' => $profile['email']]);

            if (! $user->exists) {
                $user->password = Hash::make(Str::random(64));
            }

            if ($user->trashed()) {
                $user->restore();
            }

            $user->forceFill([
                'name' => $profile['name'],
                'email_verified_at' => now()->subMonths(5),
                'phone' => $profile['phone'],
                'address' => $profile['address'],
                'role' => UserRole::User,
                'plan_id' => $plan?->id,
                'word_limit' => (int) ($plan?->monthly_word_limit ?? 0),
                'words_reserved' => 0,
                'image_generations_reserved' => 0,
                'audio_generations_reserved' => 0,
                'is_active' => true,
                'plan_activated_at' => now()->subMonths(4),
                'usage_reset_at' => now()->addMonthNoOverflow(),
            ])->save();

            return $user->refresh();
        });
    }

    /** @return \Illuminate\Support\Collection<string, Template> */
    private function seedTemplates(User $owner)
    {
        $definitions = [
            'Social Media Campaign' => [
                'description' => 'Build a coordinated social campaign with positioning, content pillars, captions, and calls to action.',
                'category' => 'Social Media',
                'icon' => 'cp-icon cp-icon-spark',
                'prompt' => 'Create a social media campaign for {brand} about {campaign_goal}. The audience is {audience}. Use the tone {tone} and provide content for {channels}.',
                'fields' => [
                    ['key' => 'brand', 'title' => 'Brand or product', 'description' => 'Name the brand, product, or service.', 'type' => 'text'],
                    ['key' => 'campaign_goal', 'title' => 'Campaign goal', 'description' => 'Describe the result this campaign should achieve.', 'type' => 'textarea'],
                    ['key' => 'audience', 'title' => 'Target audience', 'description' => 'Describe the intended audience.', 'type' => 'textarea'],
                    ['key' => 'tone', 'title' => 'Tone', 'description' => 'For example: confident, friendly, or premium.', 'type' => 'text'],
                    ['key' => 'channels', 'title' => 'Channels', 'description' => 'For example: Instagram, LinkedIn, and Facebook.', 'type' => 'text'],
                ],
            ],
            'Product Description' => [
                'description' => 'Turn product specifications and benefits into clear, persuasive ecommerce copy.',
                'category' => 'Commerce',
                'icon' => 'cp-icon cp-icon-document',
                'prompt' => 'Write a persuasive product description for {product_name}. Important details: {features}. The primary customer is {audience}. Emphasize {benefits} in a {tone} tone.',
                'fields' => [
                    ['key' => 'product_name', 'title' => 'Product name', 'description' => 'Enter the product name.', 'type' => 'text'],
                    ['key' => 'features', 'title' => 'Features', 'description' => 'List the most important product specifications.', 'type' => 'textarea'],
                    ['key' => 'benefits', 'title' => 'Customer benefits', 'description' => 'Explain how the product helps the customer.', 'type' => 'textarea'],
                    ['key' => 'audience', 'title' => 'Target customer', 'description' => 'Describe the ideal buyer.', 'type' => 'textarea'],
                    ['key' => 'tone', 'title' => 'Tone', 'description' => 'For example: practical, luxurious, or energetic.', 'type' => 'text'],
                ],
            ],
            'Email Newsletter' => [
                'description' => 'Create an engaging newsletter with a subject line, introduction, key updates, and a focused call to action.',
                'category' => 'Email',
                'icon' => 'cp-icon cp-icon-chat',
                'prompt' => 'Draft an email newsletter for {audience}. The main update is {main_update}. Include these supporting points: {supporting_points}. End with the call to action {call_to_action}.',
                'fields' => [
                    ['key' => 'audience', 'title' => 'Audience', 'description' => 'Who will receive this newsletter?', 'type' => 'text'],
                    ['key' => 'main_update', 'title' => 'Main update', 'description' => 'Describe the most important announcement.', 'type' => 'textarea'],
                    ['key' => 'supporting_points', 'title' => 'Supporting points', 'description' => 'Add any secondary news or details.', 'type' => 'textarea'],
                    ['key' => 'call_to_action', 'title' => 'Call to action', 'description' => 'What should the reader do next?', 'type' => 'text'],
                ],
            ],
            'SEO Blog Article' => [
                'description' => 'Produce a structured long-form article outline and draft around a search topic and reader intent.',
                'category' => 'Editorial',
                'icon' => 'cp-icon cp-icon-document',
                'prompt' => 'Write an SEO-friendly article about {topic} for {audience}. Target the keyword {keyword}. Cover {key_points} and use a helpful, evidence-aware tone.',
                'fields' => [
                    ['key' => 'topic', 'title' => 'Article topic', 'description' => 'Describe the subject of the article.', 'type' => 'text'],
                    ['key' => 'keyword', 'title' => 'Primary keyword', 'description' => 'Enter the main search phrase.', 'type' => 'text'],
                    ['key' => 'audience', 'title' => 'Reader', 'description' => 'Describe the intended reader.', 'type' => 'textarea'],
                    ['key' => 'key_points', 'title' => 'Key points', 'description' => 'List the points the article must cover.', 'type' => 'textarea'],
                ],
            ],
            'Landing Page Copy' => [
                'description' => 'Create conversion-focused landing page sections for a product, service, or campaign.',
                'category' => 'Marketing',
                'icon' => 'cp-icon cp-icon-templates',
                'prompt' => 'Create landing page copy for {offer}. The audience is {audience}. The primary value proposition is {value_proposition}. Address {objections} and use the call to action {call_to_action}.',
                'fields' => [
                    ['key' => 'offer', 'title' => 'Offer', 'description' => 'What product, service, or campaign is being promoted?', 'type' => 'text'],
                    ['key' => 'audience', 'title' => 'Audience', 'description' => 'Who is this page for?', 'type' => 'textarea'],
                    ['key' => 'value_proposition', 'title' => 'Value proposition', 'description' => 'State the primary benefit.', 'type' => 'textarea'],
                    ['key' => 'objections', 'title' => 'Customer objections', 'description' => 'List concerns the copy should address.', 'type' => 'textarea'],
                    ['key' => 'call_to_action', 'title' => 'Call to action', 'description' => 'What should visitors do next?', 'type' => 'text'],
                ],
            ],
            'Video Script' => [
                'description' => 'Draft a concise video script with a hook, narrative structure, and closing action.',
                'category' => 'Video',
                'icon' => 'cp-icon cp-icon-audio',
                'prompt' => 'Write a {duration} video script about {topic} for {audience}. Open with {hook}, include {key_points}, and finish with {call_to_action}.',
                'fields' => [
                    ['key' => 'topic', 'title' => 'Video topic', 'description' => 'Describe the video subject.', 'type' => 'text'],
                    ['key' => 'audience', 'title' => 'Audience', 'description' => 'Who should the video speak to?', 'type' => 'text'],
                    ['key' => 'duration', 'title' => 'Target duration', 'description' => 'For example: 60 seconds or 3 minutes.', 'type' => 'text'],
                    ['key' => 'hook', 'title' => 'Opening hook', 'description' => 'How should the video capture attention?', 'type' => 'textarea'],
                    ['key' => 'key_points', 'title' => 'Key points', 'description' => 'List the content that must be included.', 'type' => 'textarea'],
                    ['key' => 'call_to_action', 'title' => 'Call to action', 'description' => 'What should the viewer do next?', 'type' => 'text'],
                ],
            ],
            'Press Release' => [
                'description' => 'Create a professional press release for launches, partnerships, milestones, and company announcements.',
                'category' => 'Public Relations',
                'icon' => 'cp-icon cp-icon-document',
                'prompt' => 'Write a press release announcing {announcement}. Include the organization {organization}, the date and location {date_location}, supporting details {details}, and the quote {quote}.',
                'fields' => [
                    ['key' => 'organization', 'title' => 'Organization', 'description' => 'Name the organization issuing the release.', 'type' => 'text'],
                    ['key' => 'announcement', 'title' => 'Announcement', 'description' => 'Describe what is being announced.', 'type' => 'textarea'],
                    ['key' => 'date_location', 'title' => 'Date and location', 'description' => 'Provide the release date and location.', 'type' => 'text'],
                    ['key' => 'details', 'title' => 'Supporting details', 'description' => 'Add facts, context, and relevant background.', 'type' => 'textarea'],
                    ['key' => 'quote', 'title' => 'Spokesperson quote', 'description' => 'Provide an approved quote or key message.', 'type' => 'textarea'],
                ],
            ],
            'Customer Support Reply' => [
                'description' => 'Draft a helpful, empathetic support response grounded in the customer issue and desired resolution.',
                'category' => 'Customer Support',
                'icon' => 'cp-icon cp-icon-chat',
                'prompt' => 'Write a customer support reply about {issue}. Acknowledge {customer_context}, explain {resolution}, and provide the next step {next_step}.',
                'fields' => [
                    ['key' => 'issue', 'title' => 'Customer issue', 'description' => 'Summarize the reported problem.', 'type' => 'textarea'],
                    ['key' => 'customer_context', 'title' => 'Customer context', 'description' => 'Add any relevant account or order context.', 'type' => 'textarea'],
                    ['key' => 'resolution', 'title' => 'Resolution', 'description' => 'Explain what can be done.', 'type' => 'textarea'],
                    ['key' => 'next_step', 'title' => 'Next step', 'description' => 'Tell the customer what happens next.', 'type' => 'text'],
                ],
            ],
        ];

        return collect($definitions)->mapWithKeys(function (array $definition, string $title) use ($owner): array {
            $template = Template::query()->firstOrNew(['title' => $title]);
            $template->forceFill([
                'description' => $definition['description'],
                'category' => $definition['category'],
                'icon' => $definition['icon'],
                'prompt' => $definition['prompt'],
                'is_active' => true,
                'created_by' => $owner->id,
            ])->save();

            $template->inputFields()->delete();
            foreach ($definition['fields'] as $index => $field) {
                $template->inputFields()->create([
                    ...$field,
                    'is_required' => true,
                    'order' => $index,
                ]);
            }

            return [$title => $template->refresh()];
        });
    }

    private function clearPreviousDemoActivity(): void
    {
        GeneratedContent::withTrashed()->where('model', 'like', '%-demo')->forceDelete();
        GeneratedImage::withTrashed()->where('image_path', 'like', 'demo/%')->forceDelete();
        GeneratedAudio::withTrashed()->where('audio_path', 'like', 'demo/%')->forceDelete();
        BillingHistory::query()->where('payment_reference', 'like', 'CP-DEMO-%')->delete();
        ChatConversation::query()->where('model', 'demo-seed')->delete();
        Contact::query()->where('subject', 'like', '[Demo]%')->delete();

        Storage::disk('private')->deleteDirectory('demo');
    }

    private function seedDocuments($users, $templates): void
    {
        $documents = [
            ['Social Media Campaign', 0, 'Summer collection launch', 'A coordinated launch campaign focused on comfort, color, and practical everyday styling. The campaign uses short-form videos, product carousels, creator stories, and a clear shop-now call to action.', 8],
            ['Email Newsletter', 1, 'Q3 product newsletter', 'This quarter brought faster workflows, improved private media handling, and clearer usage controls. The newsletter introduces the updates, explains the customer impact, and invites readers to explore the refreshed workspace.', 22],
            ['SEO Blog Article', 2, 'How responsible AI supports content teams', 'Responsible AI workflows work best when people define the source material, review every output, and retain ownership of publishing decisions. This article explains practical safeguards for prompts, quotas, storage, and editorial review.', 50],
            ['Product Description', 0, 'Minimal workspace lamp', 'A compact adjustable desk lamp designed for focused work. Its warm and cool lighting modes, touch controls, and low-profile base make it a practical fit for home offices and study spaces.', 95],
            ['Landing Page Copy', 1, 'Agency workspace launch', 'Bring content planning, guided generation, private media, and billing review into one workspace. ContentPilot helps teams move from a clear brief to an editable draft without losing control of approvals.', 180],
            ['Video Script', 2, 'Product walkthrough script', 'Open on the dashboard. Show how a creator chooses a template, enters source details, generates a draft, edits the document, and stores it securely. Close with an invitation to create the first project.', 400],
            ['Press Release', 1, 'ContentPilot feature announcement', 'ContentPilot today announced an expanded creative workspace that combines guided writing templates, image generation, speech generation, private document storage, and administrator review controls.', 780],
            ['Customer Support Reply', 0, 'Plan upgrade response', 'Thank you for submitting your payment details. Your request is currently pending administrator review. Your existing plan remains active until the receipt is approved, and you will receive an update when the review is complete.', 1100],
            ['Social Media Campaign', 1, 'Creator partnership brief', 'A partnership campaign featuring three creator perspectives, practical product demonstrations, and a shared message around faster content production with careful human review.', 1600],
            ['SEO Blog Article', 0, 'Content workflow security checklist', 'A practical checklist covering authentication, authorization, private media storage, upload validation, rate limiting, quota reservation, safe error handling, and dependency auditing.', 2200],
            ['Email Newsletter', 2, 'Monthly creator digest', 'A friendly monthly digest highlighting recent templates, content workflow tips, product updates, and a focused invitation to continue a saved project.', 3000],
            ['Landing Page Copy', 0, 'Freelancer portfolio offer', 'Present a clear service promise, concise proof points, common client concerns, and a direct booking call to action for a freelance content and web development portfolio.', 4200],
        ];

        foreach ($documents as [$templateTitle, $userIndex, $projectTitle, $output, $minutesAgo]) {
            $template = $templates->get($templateTitle);
            $user = $users->get($userIndex);
            $content = GeneratedContent::query()->create([
                'user_id' => $user->id,
                'template_id' => $template->id,
                'input' => [
                    'project_title' => $projectTitle,
                    'seeded_demo' => true,
                    'brief' => 'Portfolio-ready sample content stored in MySQL.',
                ],
                'output' => $output,
                'model' => 'gpt-4.1-mini-demo',
            ]);
            $content->forceFill([
                'created_at' => now()->subMinutes($minutesAgo),
                'updated_at' => now()->subMinutes(max(1, $minutesAgo - 2)),
            ])->saveQuietly();
        }
    }

    private function seedImages($users): void
    {
        $images = [
            ['campaign-moodboard', 'Summer campaign moodboard with vibrant editorial blocks', '#7d2ae8', '#00c4cc', 0, 14],
            ['product-launch', 'Minimal product launch poster with modern typography', '#ff6b6b', '#f9c74f', 1, 46],
            ['newsletter-cover', 'Newsletter cover with abstract geometric shapes', '#4361ee', '#4cc9f0', 2, 130],
            ['workspace-hero', 'Creative workspace hero illustration', '#7209b7', '#f72585', 0, 380],
            ['social-carousel', 'Social carousel cover for creator productivity', '#06d6a0', '#118ab2', 1, 880],
            ['brand-pattern', 'Reusable brand pattern with soft gradients', '#8338ec', '#3a86ff', 2, 1500],
        ];

        foreach ($images as [$slug, $prompt, $start, $end, $userIndex, $minutesAgo]) {
            $path = "demo/generated-images/{$slug}.svg";
            $svg = $this->demoSvg($prompt, $start, $end);
            Storage::disk('private')->put($path, $svg);

            $image = GeneratedImage::query()->create([
                'user_id' => $users->get($userIndex)->id,
                'prompt' => $prompt,
                'image_path' => $path,
                'model' => 'gpt-image-demo',
                'mime_type' => 'image/svg+xml',
                'size_bytes' => strlen($svg),
            ]);
            $image->forceFill([
                'created_at' => now()->subMinutes($minutesAgo),
                'updated_at' => now()->subMinutes($minutesAgo),
            ])->saveQuietly();
        }
    }

    private function seedAudio($users): void
    {
        $audioBytes = $this->silentWav();
        $items = [
            ['campaign-intro', 'A warm introduction for the summer campaign video.', 'nova', 0, 35],
            ['product-demo', 'A concise product demonstration voice-over.', 'alloy', 1, 240],
            ['newsletter-summary', 'A clear spoken summary of this month’s product updates.', 'nova', 2, 720],
            ['workspace-tour', 'A guided tour of the ContentPilot creative workspace.', 'alloy', 0, 1440],
        ];

        foreach ($items as [$slug, $prompt, $voice, $userIndex, $minutesAgo]) {
            $path = "demo/generated-audio/{$slug}.wav";
            Storage::disk('private')->put($path, $audioBytes);

            $audio = GeneratedAudio::query()->create([
                'user_id' => $users->get($userIndex)->id,
                'prompt' => $prompt,
                'audio_path' => $path,
                'model' => 'tts-1-demo',
                'voice' => $voice,
                'mime_type' => 'audio/wav',
                'size_bytes' => strlen($audioBytes),
            ]);
            $audio->forceFill([
                'created_at' => now()->subMinutes($minutesAgo),
                'updated_at' => now()->subMinutes($minutesAgo),
            ])->saveQuietly();
        }
    }

    private function seedPayments($users, $plans): void
    {
        $reviewer = User::query()->where('role', UserRole::Admin->value)->oldest()->first();
        $payments = [
            ['CP-DEMO-1001', 0, 'Agency', PaymentStatus::Pending, 18, null],
            ['CP-DEMO-1002', 1, 'Agency', PaymentStatus::Approved, 1440, null],
            ['CP-DEMO-1003', 2, 'Creator', PaymentStatus::Rejected, 2880, 'The uploaded receipt did not include a verifiable transaction reference.'],
            ['CP-DEMO-1004', 0, 'Creator', PaymentStatus::Approved, 5760, null],
        ];

        foreach ($payments as [$reference, $userIndex, $planName, $status, $minutesAgo, $rejectionReason]) {
            $user = $users->get($userIndex);
            $plan = $plans->get($planName);
            $path = 'demo/payment-receipts/'.Str::lower($reference).'.pdf';
            Storage::disk('private')->put($path, $this->demoPdf($reference, $user->name, $plan->name));

            BillingHistory::query()->create([
                'invoice_number' => (string) Str::uuid(),
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'submitted_at' => now()->subMinutes($minutesAgo),
                'total' => $plan->price,
                'bank_name' => 'ContentPilot Demo Bank',
                'account_holder' => $user->name,
                'payment_reference' => $reference,
                'reference_fingerprint' => hash('sha256', Str::lower('ContentPilot Demo Bank').'|'.$reference),
                'receipt_path' => $path,
                'status' => $status,
                'pending_key' => $status === PaymentStatus::Pending ? (string) $user->id : null,
                'reviewed_at' => $status === PaymentStatus::Pending ? null : now()->subMinutes(max(1, $minutesAgo - 30)),
                'reviewed_by' => $status === PaymentStatus::Pending ? null : $reviewer?->id,
                'rejection_reason' => $rejectionReason,
                'created_at' => now()->subMinutes($minutesAgo),
                'updated_at' => now()->subMinutes(max(1, $minutesAgo - 30)),
            ]);
        }
    }

    private function seedAssistantsAndConversations($users): void
    {
        $assistants = collect([
            [
                'name' => 'Campaign Strategist',
                'role_description' => 'Plans campaign angles, messages, and channel-specific content.',
                'welcome_message' => 'Share your campaign goal and I will help structure the creative brief.',
                'instructions' => 'Help users plan responsible, evidence-aware marketing campaigns. Ask for missing facts and avoid inventing claims.',
                'category' => 'Marketing',
            ],
            [
                'name' => 'Editorial Coach',
                'role_description' => 'Improves clarity, structure, tone, and reader flow.',
                'welcome_message' => 'Paste a draft or describe the audience and publishing goal.',
                'instructions' => 'Provide concise editing guidance while preserving the author’s meaning and facts.',
                'category' => 'Writing',
            ],
            [
                'name' => 'Support Reply Assistant',
                'role_description' => 'Drafts clear, empathetic customer support replies.',
                'welcome_message' => 'Describe the customer issue and the resolution you can offer.',
                'instructions' => 'Write helpful support replies without promising actions that were not approved.',
                'category' => 'Customer Support',
            ],
        ])->map(function (array $data): ChatAssistant {
            return ChatAssistant::query()->updateOrCreate(
                ['name' => $data['name']],
                [...$data, 'avatar' => null, 'is_active' => true],
            );
        });

        $conversation = $assistants->first();
        ChatConversation::query()->create([
            'assistant_id' => $conversation->id,
            'user_id' => $users->first()->id,
            'conversation_uuid' => (string) Str::uuid(),
            'message' => 'Help me structure a launch campaign for a productivity tool.',
            'response' => 'Start with one measurable goal, define the audience problem, choose three proof points, and adapt the core message for each channel. Keep every product claim tied to verified source material.',
            'model' => 'demo-seed',
            'created_at' => now()->subHours(3),
            'updated_at' => now()->subHours(3),
        ]);
    }

    private function seedContacts(): void
    {
        foreach ([
            ['Nadia Sheikh', 'nadia@example.test', '[Demo] Agency onboarding question', 'Can ContentPilot support an approval workflow for a small content team?'],
            ['Usman Raza', 'usman@example.test', '[Demo] Billing review timing', 'How will I know when an offline payment request has been approved?'],
            ['Mariam Noor', 'mariam@example.test', '[Demo] Private media storage', 'Are generated images and audio accessible to other users?'],
        ] as $index => [$name, $email, $subject, $message]) {
            Contact::query()->create([
                'name' => $name,
                'email' => $email,
                'subject' => $subject,
                'message' => $message,
                'created_at' => now()->subHours($index + 2),
                'updated_at' => now()->subHours($index + 2),
            ]);
        }
    }

    private function syncUsageCounters($users): void
    {
        foreach ($users as $user) {
            $user->forceFill([
                'words_used' => (int) $user->generatedContents()->sum('word_count'),
                'image_generations_used' => $user->generatedImages()->count(),
                'audio_generations_used' => $user->generatedAudios()->count(),
            ])->save();
        }
    }

    private function demoSvg(string $label, string $start, string $end): string
    {
        $escaped = htmlspecialchars(Str::limit($label, 54), ENT_QUOTES | ENT_XML1, 'UTF-8');

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="800" viewBox="0 0 1200 800" role="img" aria-label="{$escaped}">
  <defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop stop-color="{$start}"/><stop offset="1" stop-color="{$end}"/></linearGradient></defs>
  <rect width="1200" height="800" rx="48" fill="url(#g)"/>
  <circle cx="1020" cy="150" r="170" fill="#fff" opacity=".14"/>
  <circle cx="180" cy="680" r="260" fill="#fff" opacity=".1"/>
  <rect x="95" y="105" width="1010" height="590" rx="38" fill="#fff" opacity=".14"/>
  <text x="120" y="620" fill="#fff" font-size="54" font-family="Arial, sans-serif" font-weight="700">{$escaped}</text>
  <text x="120" y="675" fill="#fff" opacity=".82" font-size="25" font-family="Arial, sans-serif">ContentPilot seeded media preview</text>
</svg>
SVG;
    }

    private function silentWav(): string
    {
        $sampleRate = 8000;
        $seconds = 1;
        $samples = $sampleRate * $seconds;
        $data = str_repeat(pack('v', 0), $samples);
        $header = 'RIFF'.pack('V', 36 + strlen($data)).'WAVE';
        $header .= 'fmt '.pack('VvvVVvv', 16, 1, 1, $sampleRate, $sampleRate * 2, 2, 16);
        $header .= 'data'.pack('V', strlen($data));

        return $header.$data;
    }

    private function demoPdf(string $reference, string $customer, string $plan): string
    {
        $text = "ContentPilot demo receipt {$reference} for {$customer} - {$plan}";
        $stream = "BT /F1 12 Tf 72 720 Td ({$text}) Tj ET";

        return "%PDF-1.4\n1 0 obj<< /Type /Catalog /Pages 2 0 R >>endobj\n"
            ."2 0 obj<< /Type /Pages /Kids [3 0 R] /Count 1 >>endobj\n"
            ."3 0 obj<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources<< /Font<< /F1 4 0 R >> >> /Contents 5 0 R >>endobj\n"
            ."4 0 obj<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>endobj\n"
            .'5 0 obj<< /Length '.strlen($stream)." >>stream\n{$stream}\nendstream\nendobj\n"
            ."trailer<< /Root 1 0 R >>\n%%EOF";
    }
}
