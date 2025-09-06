<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin']);
    }

    /**
     * Display all messages for admin review
     */
    public function index(Request $request)
    {
        $query = Message::with(['sender', 'recipient', 'moderator'])
            ->orderBy('created_at', 'desc');

        // Filter by flagged status
        if ($request->has('flagged') && $request->flagged === 'true') {
            $query->where('is_flagged', true);
        }

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('content', 'like', "%{$search}%")
                  ->orWhere('original_content', 'like', "%{$search}%")
                  ->orWhereHas('sender', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('recipient', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $messages = $query->paginate(20);

        // Get statistics
        $stats = [
            'total' => Message::count(),
            'flagged' => Message::where('is_flagged', true)->count(),
            'pending_review' => Message::where('is_flagged', true)
                                     ->whereNull('moderated_at')
                                     ->count(),
            'hidden' => Message::where('status', 'hidden')->count(),
        ];

        return view('admin.messages.index', compact('messages', 'stats'));
    }

    /**
     * Show specific message details
     */
    public function show(Message $message)
    {
        $message->load(['sender', 'recipient', 'moderator']);
        
        // Get conversation context (previous messages between same users)
        $conversationMessages = Message::conversation($message->sender_id, $message->recipient_id)
            ->where('id', '!=', $message->id)
            ->with(['sender', 'recipient'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.messages.show', compact('message', 'conversationMessages'));
    }

    /**
     * Moderate a message (approve/hide/delete)
     */
    public function moderate(Request $request, Message $message)
    {
        $request->validate([
            'action' => 'required|in:approve,hide,delete',
            'notes' => 'nullable|string|max:1000',
        ]);

        $admin = Auth::user();

        switch ($request->action) {
            case 'approve':
                $message->update([
                    'is_flagged' => false,
                    'status' => 'active',
                    'moderated_by' => $admin->id,
                    'moderation_notes' => $request->notes,
                    'moderated_at' => now(),
                ]);
                $action_message = 'Message approved successfully.';
                break;

            case 'hide':
                $message->update([
                    'status' => 'hidden',
                    'moderated_by' => $admin->id,
                    'moderation_notes' => $request->notes,
                    'moderated_at' => now(),
                ]);
                $action_message = 'Message hidden successfully.';
                break;

            case 'delete':
                $message->update([
                    'status' => 'deleted',
                    'moderated_by' => $admin->id,
                    'moderation_notes' => $request->notes,
                    'moderated_at' => now(),
                ]);
                $action_message = 'Message marked as deleted successfully.';
                break;
        }

        // If returning JSON for AJAX
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $action_message,
                'status' => $message->status,
            ]);
        }

        return redirect()->route('admin.messages.index')
                         ->with('success', $action_message);
    }

    /**
     * Bulk moderate messages
     */
    public function bulkModerate(Request $request)
    {
        $request->validate([
            'message_ids' => 'required|array',
            'message_ids.*' => 'exists:messages,id',
            'action' => 'required|in:approve,hide,delete',
            'notes' => 'nullable|string|max:1000',
        ]);

        $admin = Auth::user();
        $messageIds = $request->message_ids;
        $action = $request->action;

        $updateData = [
            'moderated_by' => $admin->id,
            'moderation_notes' => $request->notes,
            'moderated_at' => now(),
        ];

        switch ($action) {
            case 'approve':
                $updateData['is_flagged'] = false;
                $updateData['status'] = 'active';
                break;
            case 'hide':
                $updateData['status'] = 'hidden';
                break;
            case 'delete':
                $updateData['status'] = 'deleted';
                break;
        }

        $affectedRows = Message::whereIn('id', $messageIds)->update($updateData);

        return response()->json([
            'success' => true,
            'message' => "Successfully {$action}d {$affectedRows} message(s).",
        ]);
    }

    /**
     * Get conversation between two specific users
     */
    public function conversation(User $user1, User $user2)
    {
        $messages = Message::conversation($user1->id, $user2->id)
            ->with(['sender', 'recipient', 'moderator'])
            ->orderBy('created_at', 'asc')
            ->get();

        return view('admin.messages.conversation', compact('messages', 'user1', 'user2'));
    }

    /**
     * Export flagged messages for review
     */
    public function export(Request $request)
    {
        $query = Message::flagged()->with(['sender', 'recipient']);

        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
        }

        $messages = $query->get();

        $filename = 'flagged_messages_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function () use ($messages) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID',
                'Sender',
                'Sender Role',
                'Recipient',
                'Recipient Role',
                'Original Content',
                'Filtered Content',
                'Flagged Reasons',
                'Created At',
                'Status',
                'Moderated By',
                'Moderation Notes',
                'Moderated At'
            ]);

            // CSV data
            foreach ($messages as $message) {
                fputcsv($file, [
                    $message->id,
                    $message->sender->name,
                    $message->sender->getRoleNames()->first(),
                    $message->recipient->name,
                    $message->recipient->getRoleNames()->first(),
                    $message->original_content ?: $message->content,
                    $message->content,
                    implode('; ', $message->flagged_reasons ?? []),
                    $message->created_at->format('Y-m-d H:i:s'),
                    $message->status,
                    $message->moderator ? $message->moderator->name : '',
                    $message->moderation_notes,
                    $message->moderated_at ? $message->moderated_at->format('Y-m-d H:i:s') : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
