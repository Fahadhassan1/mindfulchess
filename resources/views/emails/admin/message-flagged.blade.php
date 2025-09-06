@component('mail::message')
# 🚨 Message Flagged for Review

**ADMIN ALERT:** A message has been automatically flagged for containing potentially sensitive information.

## Message Details:
- **From:** {{ $senderRole }} {{ $sender->name }} ({{ $sender->email }})
- **To:** {{ $recipientRole }} {{ $recipient->name }} ({{ $recipient->email }})
- **Sent:** {{ $message->created_at->format('M j, Y at g:i A') }}
- **Message ID:** #{{ $message->id }}

## Flagged Reasons:
@foreach($flaggedReasons as $reason)
- ⚠️ {{ ucfirst($reason) }} detected
@endforeach

## Message Content:
@component('mail::panel')
{{ $messagePreview }}
@if(strlen($message->content) > 200)
... *(truncated)*
@endif
@endcomponent

## Original Content:
@if($message->original_content && $message->original_content !== $message->content)
@component('mail::panel')
{{ $message->original_content }}
@endcomponent
@else
*No modifications were made to the original content*
@endif

@component('mail::button', ['url' => $adminUrl, 'color' => 'error'])
Review in Admin Panel
@endcomponent

**Action Required:** Please review this message and determine if it violates platform guidelines. You can approve, reject, or request modifications through the admin panel.

---
**Security Notice:** This message was automatically flagged by our content filter system. The message has been held for moderation and will not be delivered to the recipient until reviewed.

Best regards,<br>
{{ config('app.name') }} Security System

@component('mail::subcopy')
This is an automated security alert. Message ID: {{ $message->id }} | Timestamp: {{ now()->format('Y-m-d H:i:s T') }}
@endcomponent
@endcomponent
