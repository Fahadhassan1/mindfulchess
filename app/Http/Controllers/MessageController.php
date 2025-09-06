<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Notifications\NewMessage;
use App\Services\ContentFilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class MessageController extends Controller
{
    protected ContentFilterService $contentFilter;

    public function __construct(ContentFilterService $contentFilter)
    {
        $this->middleware(['auth']);
        $this->contentFilter = $contentFilter;
    }

    /**
     * Display messages/conversations for the authenticated user
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get all conversations (unique sender/recipient pairs)
        $conversations = Message::select('*')
            ->where(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->orWhere('recipient_id', $user->id);
            })
            ->where('status', 'active')
            ->with(['sender', 'recipient'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy(function ($message) use ($user) {
                // Group by the other participant in the conversation
                return $message->sender_id == $user->id 
                    ? $message->recipient_id 
                    : $message->sender_id;
            })
            ->map(function ($messages) {
                return $messages->sortByDesc('created_at')->first();
            });

        // Get unread message count for each conversation
        $unreadCounts = [];
        foreach ($conversations as $otherUserId => $lastMessage) {
            $unreadCounts[$otherUserId] = Message::unreadForUser($user->id)
                ->where('sender_id', $otherUserId)
                ->count();
        }

        return view('messages.index', compact('conversations', 'unreadCounts'));
    }

    /**
     * Show conversation between authenticated user and another user
     */
    public function conversation(User $user)
    {
        $currentUser = Auth::user();
        
        // Check if users can communicate (teacher-student or admin)
        $this->authorizeConversation($currentUser, $user);

        // Get all messages in the conversation
        $messages = Message::conversation($currentUser->id, $user->id)
            ->where('status', 'active')
            ->with(['sender', 'recipient'])
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read
        Message::conversation($currentUser->id, $user->id)
            ->where('recipient_id', $currentUser->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return view('messages.conversation', compact('messages', 'user'));
    }

    /**
     * Send a new message
     */
    public function store(Request $request)
    {
        $request->validate([
            'recipient_id' => 'required|exists:users,id',
            'content' => 'required|string|max:2000',
        ]);

        $sender = Auth::user();
        $recipient = User::findOrFail($request->recipient_id);

        // Check if users can communicate
        $this->authorizeConversation($sender, $recipient);

        DB::beginTransaction();
        try {
            // Create the message
            $message = Message::create([
                'sender_id' => $sender->id,
                'recipient_id' => $recipient->id,
                'content' => $request->input('content'),
            ]);

            // Process through content filter
            $message = $this->contentFilter->processMessage($message);
            $message->save();

            // Send email notification to recipient (only if message is not flagged)
            if (!$message->is_flagged) {
                $recipient->notify(new NewMessage($message, $sender));
            }

            DB::commit();

            // Return JSON response for AJAX requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message->load(['sender', 'recipient']),
                    'is_flagged' => $message->is_flagged,
                ]);
            }

            $redirectMessage = $message->is_flagged 
                ? 'Message sent, but flagged for review due to sensitive content.'
                : 'Message sent successfully!';

            return redirect()->route('messages.conversation', $recipient)
                           ->with('success', $redirectMessage);

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send message. Please try again.',
                ], 500);
            }

            return redirect()->back()
                           ->with('error', 'Failed to send message. Please try again.')
                           ->withInput();
        }
    }

    /**
     * Get available contacts for messaging (teachers/students based on role)
     */
    public function contacts()
    {
        $user = Auth::user();
        $contacts = [];

        if ($user->isStudent()) {
            // Students can only message their assigned teacher(s)
            if ($user->studentProfile && $user->studentProfile->teacher_id) {
                $contacts = User::where('id', $user->studentProfile->teacher_id)
                    ->role('teacher')
                    ->get();
            }
        } elseif ($user->isTeacher()) {
            // Teachers can message their assigned students
            $contacts = User::role('student')
                ->whereHas('studentProfile', function ($query) use ($user) {
                    $query->where('teacher_id', $user->id);
                })
                ->get();
        } elseif ($user->hasRole('admin')) {
            // Admins can message anyone
            $contacts = User::role(['teacher', 'student'])->get();
        }

        return response()->json($contacts);
    }

    /**
     * Get unread message count for the authenticated user
     */
    public function unreadCount()
    {
        $count = Message::unreadForUser(Auth::id())->count();
        return response()->json(['count' => $count]);
    }

    /**
     * Check if two users are authorized to communicate
     */
    private function authorizeConversation(User $user1, User $user2): void
    {
        // Admins can communicate with anyone
        if ($user1->hasRole('admin') || $user2->hasRole('admin')) {
            return;
        }

        // Check teacher-student relationship
        if ($user1->isTeacher() && $user2->isStudent()) {
            if ($user2->studentProfile && $user2->studentProfile->teacher_id === $user1->id) {
                return;
            }
        }

        if ($user1->isStudent() && $user2->isTeacher()) {
            if ($user1->studentProfile && $user1->studentProfile->teacher_id === $user2->id) {
                return;
            }
        }

        abort(403, 'You are not authorized to communicate with this user.');
    }
}
