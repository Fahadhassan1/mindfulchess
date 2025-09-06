@component('mail::message')
# New Message Received

Hello {{ $notifiable->name }},

You have received a new message from **{{ $senderRole }} {{ $sender->name }}** on MindfulChess.

## Message Preview:
> {{ $messagePreview }}

@component('mail::button', ['url' => $actionUrl])
View Full Message
@endcomponent

Please log in to your MindfulChess account to read the full message and reply. Stay connected with your chess learning community!

Best regards,<br>
{{ config('app.name') }} Team

---
*This is an automated message. Please do not reply directly to this email.*
@endcomponent
