<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transfer;
use App\Models\User;
use App\Models\ChessSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\Transfer as StripeTransfer;

class TransferController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    /**
     * Display a listing of the transfers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $query = Transfer::with(['teacher', 'session.student']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transferred_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transferred_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $searchTerm = '%' . $request->search . '%';
            $query->where(function($q) use ($searchTerm) {
                $q->whereHas('teacher', function($tq) use ($searchTerm) {
                    $tq->where('name', 'like', $searchTerm)
                      ->orWhere('email', 'like', $searchTerm);
                })->orWhere('stripe_transfer_id', 'like', $searchTerm);
            });
        }

        if ($request->filled('amount_min')) {
            $query->where('amount', '>=', $request->amount_min);
        }

        if ($request->filled('amount_max')) {
            $query->where('amount', '<=', $request->amount_max);
        }

        $transfers = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get teachers for filter dropdown
        $teachers = User::role('teacher')->with('teacherProfile')->get();

        // Calculate statistics
        $stats = [
            'total_transfers' => Transfer::count(),
            'total_transferred' => Transfer::where('status', 'completed')->sum('amount'),
            'pending_transfers' => Transfer::where('status', 'pending')->count(),
            'failed_transfers' => Transfer::where('status', 'failed')->count(),
            'completed_transfers' => Transfer::where('status', 'completed')->count(),
            'total_fees_collected' => Transfer::where('status', 'completed')->sum('application_fee'),
            'this_month_transfers' => Transfer::where('status', 'completed')
                ->whereMonth('transferred_at', now()->month)
                ->whereYear('transferred_at', now()->year)
                ->sum('amount'),
        ];

        return view('admin.transfers.index', compact('transfers', 'teachers', 'stats'));
    }

    /**
     * Display the specified transfer.
     *
     * @param  \App\Models\Transfer  $transfer
     * @return \Illuminate\Contracts\View\View
     */
    public function show(Transfer $transfer)
    {
        $transfer->load(['teacher', 'session.student', 'session.payment']);
        
        return view('admin.transfers.show', compact('transfer'));
    }
    
    /**
     * Show the invoice for the specified transfer.
     *
     * @param  \App\Models\Transfer  $transfer
     * @return \Illuminate\Contracts\View\View
     */
    public function showInvoice(Transfer $transfer)
    {
        $transfer->load(['teacher', 'session.student', 'session.payment']);
        
        return view('admin.transfers.invoice', compact('transfer'));
    }

    /**
     * Process all pending transfers.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processPending(Request $request)
    {
        $pendingTransfers = Transfer::where('status', 'pending')
            ->whereHas('session', function($q) {
                $q->where('status', 'completed');
            })
            ->get();

        $processed = 0;
        $failed = 0;

        foreach ($pendingTransfers as $transfer) {
            try {
                $this->processTransfer($transfer);
                $processed++;
            } catch (\Exception $e) {
                $failed++;
                $transfer->update([
                    'status' => 'failed',
                    'notes' => 'Failed to process: ' . $e->getMessage()
                ]);
            }
        }

        $message = "Processed {$processed} transfers successfully.";
        if ($failed > 0) {
            $message .= " {$failed} transfers failed.";
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Retry a failed transfer.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Transfer  $transfer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function retry(Request $request, Transfer $transfer)
    {
        if ($transfer->status !== 'failed') {
            return redirect()->back()->with('error', 'Only failed transfers can be retried.');
        }

        try {
            $this->processTransfer($transfer);
            return redirect()->back()->with('success', 'Transfer retried successfully.');
        } catch (\Exception $e) {
            $transfer->update([
                'notes' => ($transfer->notes ? $transfer->notes . "\n\n" : '') . 
                          now()->format('Y-m-d H:i:s') . " - Retry failed: " . $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Transfer retry failed: ' . $e->getMessage());
        }
    }

    /**
     * Process a single transfer.
     *
     * @param  \App\Models\Transfer  $transfer
     * @return void
     * @throws \Exception
     */
    private function processTransfer(Transfer $transfer)
    {
        $teacher = $transfer->teacher;
        
        if (!$teacher->teacherProfile || !$teacher->teacherProfile->stripe_account_id) {
            throw new \Exception('Teacher does not have a Stripe account set up.');
        }

        // Create transfer in Stripe
        $stripeTransfer = StripeTransfer::create([
            'amount' => $transfer->amount * 100, // Convert to cents
            'currency' => 'usd',
            'destination' => $teacher->teacherProfile->stripe_account_id,
            'metadata' => [
                'transfer_id' => $transfer->id,
                'teacher_id' => $teacher->id,
                'session_id' => $transfer->session_id,
            ]
        ]);

        $transfer->update([
            'stripe_transfer_id' => $stripeTransfer->id,
            'status' => 'completed',
            'transferred_at' => now(),
            'notes' => ($transfer->notes ? $transfer->notes . "\n\n" : '') . 
                      now()->format('Y-m-d H:i:s') . " - Transfer completed successfully."
        ]);
    }

    /**
     * Export transfers to Excel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        $query = Transfer::with(['teacher', 'session']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('teacher_id')) {
            $query->where('teacher_id', $request->teacher_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('transferred_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('transferred_at', '<=', $request->date_to);
        }

        $transfers = $query->orderBy('created_at', 'desc')->get();

        $filename = 'transfers_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($transfers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Transfer ID', 'Teacher Name', 'Teacher Email', 'Amount', 'Application Fee', 
                'Total Session Amount', 'Status', 'Stripe Transfer ID', 'Transferred At', 'Created At'
            ]);

            foreach ($transfers as $transfer) {
                fputcsv($file, [
                    $transfer->id,
                    $transfer->teacher->name,
                    $transfer->teacher->email,
                    $transfer->amount,
                    $transfer->application_fee,
                    $transfer->total_session_amount,
                    $transfer->status,
                    $transfer->stripe_transfer_id,
                    $transfer->transferred_at ? $transfer->transferred_at->format('Y-m-d H:i:s') : '',
                    $transfer->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
