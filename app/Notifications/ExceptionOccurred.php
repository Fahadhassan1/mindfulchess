<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Throwable;

class ExceptionOccurred extends Notification implements ShouldQueue
{
    use Queueable;

    protected $exceptionData;
    protected $requestData;
    protected $userData;

    /**
     * Create a new notification instance.
     */
    public function __construct(Throwable $exception, $request, $user = null)
    {
        // Extract needed exception information
        $this->exceptionData = [
            'class' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString()
        ];
        
        // Extract needed request information
        if ($request) {
            $this->requestData = [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'userAgent' => $request->userAgent(),
                'headers' => $this->getRequestHeadersArray($request),
                'inputs' => $this->safelyGetRequestInputs($request)
            ];
        }
        
        // Extract needed user information
        if ($user) {
            $this->userData = [
                'id' => $user->id ?? null,
                'name' => $user->name ?? null,
                'email' => $user->email ?? null
            ];
        }
    }
    
    /**
     * Safely extract request headers without closures
     */
    private function getRequestHeadersArray($request): array
    {
        $safeHeaders = [];
        if (method_exists($request, 'headers')) {
            foreach ($request->headers->all() as $key => $value) {
                $safeHeaders[$key] = is_array($value) && !empty($value) ? $value[0] : $value;
            }
        }
        return $safeHeaders;
    }
    
    /**
     * Safely get request input without closures
     */
    private function safelyGetRequestInputs($request): array
    {
        try {
            $input = $request->except(['password', 'password_confirmation']);
            
            // Remove any potential closures from the input array
            array_walk_recursive($input, function(&$value) {
                if ($value instanceof \Closure) {
                    $value = '[Closure]';
                }
            });
            
            return $input;
        } catch (\Throwable $e) {
            return ['error' => 'Unable to get request inputs safely'];
        }
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $exceptionMessage = $this->exceptionData['message'] ?: 'No message';
        $exceptionClass = $this->exceptionData['class'];
        $file = $this->exceptionData['file'];
        $line = $this->exceptionData['line'];
        $trace = $this->exceptionData['trace'];
        
        // Get request information
        $url = $this->requestData['url'] ?? 'Unknown URL';
        $method = $this->requestData['method'] ?? 'Unknown Method';
        $ip = $this->requestData['ip'] ?? 'Unknown IP';
        $userAgent = $this->requestData['userAgent'] ?? 'Unknown User Agent';
        
        // User information if available
        $userInfo = 'Guest';
        if ($this->userData) {
            $userInfo = "ID: {$this->userData['id']}, Name: {$this->userData['name']}, Email: {$this->userData['email']}";
        }
        
        $content = "
## Exception Details
- **Type**: {$exceptionClass}
- **Message**: {$exceptionMessage}
- **Location**: {$file}:{$line}

## Request Information
- **URL**: {$url}
- **Method**: {$method}
- **IP Address**: {$ip}
- **User Agent**: {$userAgent}
- **User**: {$userInfo}

## Stack Trace
```
{$trace}
```
";

        return (new MailMessage)
                    ->error()
                    ->subject('🚨 Exception Occurred on ' . config('app.name'))
                    ->greeting('Critical Error Detected!')
                    ->line('An exception occurred in your application:')
                    ->line($exceptionMessage)
                    ->line('Please check the details below:')
                    ->with($content);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'exception_class' => $this->exceptionData['class'],
            'exception_message' => $this->exceptionData['message'],
            'exception_file' => $this->exceptionData['file'],
            'exception_line' => $this->exceptionData['line'],
            'request' => $this->requestData ?? [],
            'user' => $this->userData ?? []
        ];
    }
}
