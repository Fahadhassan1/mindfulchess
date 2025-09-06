<?php

namespace App\Services;

use App\Models\Message;
use App\Notifications\MessageFlagged;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class ContentFilterService
{
    /**
     * Patterns to detect sensitive information
     */
    private array $patterns = [
        'email' => '/\b[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Z|a-z]{2,}\b/',
        'phone_uk' => '/\b(?:\+44|0)(?:\d{2,4}\s?\d{3,4}\s?\d{3,4}|\d{3}\s?\d{3}\s?\d{4}|\d{4}\s?\d{6})\b/',
        'phone_international' => '/\+\d{1,4}[\s\-]?\(?\d{1,4}\)?[\s\-]?\d{1,4}[\s\-]?\d{1,9}/',
        'skype_username' => '/\b(?:skype|skype\s*name|skype\s*id|my\s*skype)[:\s]*([a-zA-Z0-9._-]+)/i',
        'discord_tag' => '/\b[A-Za-z0-9._-]+#\d{4}\b/',
        'social_media' => '/\b(?:instagram|ig|twitter|facebook|fb|snapchat|snap|whatsapp|telegram|tiktok)[:\s]*[@]?([a-zA-Z0-9._-]+)/i',
        'personal_address' => '/\b\d+\s+[A-Za-z\s]+(?:street|st|avenue|ave|road|rd|lane|ln|drive|dr|court|ct|place|pl|boulevard|blvd)\b/i',
    ];

    /**
     * Filter content and flag if sensitive information is detected
     */
    public function filterContent(string $content): array
    {
        $flaggedReasons = [];
        $filteredContent = $content;

        foreach ($this->patterns as $type => $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                $flaggedReasons[] = $this->getReasonMessage($type, $matches[0]);
                
                // Replace sensitive information with placeholders
                $filteredContent = preg_replace($pattern, $this->getPlaceholder($type), $filteredContent);
            }
        }

        return [
            'filtered_content' => $filteredContent,
            'flagged_reasons' => $flaggedReasons,
            'is_flagged' => !empty($flaggedReasons),
        ];
    }

    /**
     * Process a message through content filtering
     */
    public function processMessage(Message $message): Message
    {
        $filterResult = $this->filterContent($message->content);

        if ($filterResult['is_flagged']) {
            // Store original content
            $message->original_content = $message->content;
            
            // Update content with filtered version
            $message->content = $filterResult['filtered_content'];
            
            // Flag the message
            $message->flagForModeration($filterResult['flagged_reasons']);
            
            // Notify admins about flagged content
            $this->notifyAdminsAboutFlaggedContent($message);
            
            Log::warning('Message flagged for sensitive content', [
                'message_id' => $message->id,
                'sender_id' => $message->sender_id,
                'recipient_id' => $message->recipient_id,
                'reasons' => $filterResult['flagged_reasons'],
            ]);
        }

        return $message;
    }

    /**
     * Get reason message for flagged content type
     */
    private function getReasonMessage(string $type, array $matches): string
    {
        $count = count($matches);
        
        return match($type) {
            'email' => "Contains email address" . ($count > 1 ? "es ({$count})" : " (1)"),
            'phone_uk' => "Contains UK phone number" . ($count > 1 ? "s ({$count})" : " (1)"),
            'phone_international' => "Contains international phone number" . ($count > 1 ? "s ({$count})" : " (1)"),
            'skype_username' => "Contains Skype username" . ($count > 1 ? "s ({$count})" : " (1)"),
            'discord_tag' => "Contains Discord tag" . ($count > 1 ? "s ({$count})" : " (1)"),
            'social_media' => "Contains social media handle" . ($count > 1 ? "s ({$count})" : " (1)"),
            'personal_address' => "Contains personal address" . ($count > 1 ? "es ({$count})" : " (1)"),
            default => "Contains sensitive information ({$type})",
        };
    }

    /**
     * Get placeholder text for filtered content
     */
    private function getPlaceholder(string $type): string
    {
        return match($type) {
            'email' => '[EMAIL FILTERED]',
            'phone_uk', 'phone_international' => '[PHONE FILTERED]',
            'skype_username' => '[SKYPE ID FILTERED]',
            'discord_tag' => '[DISCORD TAG FILTERED]',
            'social_media' => '[SOCIAL MEDIA HANDLE FILTERED]',
            'personal_address' => '[ADDRESS FILTERED]',
            default => '[SENSITIVE INFO FILTERED]',
        };
    }

    /**
     * Notify administrators about flagged content
     */
    private function notifyAdminsAboutFlaggedContent(Message $message): void
    {
        try {
            // Load relationships if not already loaded
            $message->loadMissing(['sender', 'recipient']);
            
            $sender = $message->sender;
            $recipient = $message->recipient;
            $flaggedReasons = $message->flagged_reasons ?? [];

            // Notify admin users in the system
            $admins = User::role('admin')->get();
            
            foreach ($admins as $admin) {
                $admin->notify(new MessageFlagged($message, $sender, $recipient, $flaggedReasons));
            }

            // Also send notification to the admin email from config
            $adminEmail = config('app.admin_email', env('ADMIN_EMAIL'));
            if ($adminEmail && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                Notification::route('mail', $adminEmail)
                    ->notify(new MessageFlagged($message, $sender, $recipient, $flaggedReasons));
            }

        } catch (\Exception $e) {
            Log::error('Failed to notify admins about flagged message content', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check if content contains sensitive information without filtering
     */
    public function containsSensitiveInfo(string $content): bool
    {
        foreach ($this->patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get detailed analysis of content
     */
    public function analyzeContent(string $content): array
    {
        $analysis = [];
        
        foreach ($this->patterns as $type => $pattern) {
            if (preg_match_all($pattern, $content, $matches)) {
                $analysis[$type] = [
                    'count' => count($matches[0]),
                    'matches' => $matches[0],
                ];
            }
        }
        
        return $analysis;
    }
}
