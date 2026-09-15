<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\School;
use App\Models\Subscription;
use App\Services\InvoiceService;
use App\Services\SchoolContextService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class InvoicesController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoices,
        private readonly SchoolContextService $context
    ) {}

    public function index(Request $request)
    {
        $filters = $request->only(['school', 'school_id', 'status', 'billing_cycle', 'date_from', 'date_to']);

        $school = $this->context->resolveSchool(auth()->user());
        $isSuperAdmin = auth()->user()->hasRole('Super Admin');

        $invoices = Invoice::query()
            ->filter($filters)
            ->with(['school', 'plan', 'subscription'])
            ->when($school && ! $isSuperAdmin, fn ($q) => $q->where('school_id', $school->id))
            ->latest('issued_at')
            ->paginate(10)
            ->withQueryString();

        $schools = School::orderBy('name')->get();

        return view('invoices.index', compact('invoices', 'filters', 'schools'));
    }

    public function show(Invoice $invoice)
    {
        $this->authorizeInvoiceAccess($invoice);
        $invoice->load(['school', 'plan', 'subscription']);

        return view('invoices.show', compact('invoice'));
    }

    public function print(Invoice $invoice)
    {
        $this->authorizeInvoiceAccess($invoice);
        $invoice->load(['school', 'plan', 'subscription']);

        return view('invoices.print', compact('invoice'));
    }

    /**
     * Ensure the current user can access the given invoice.
     * Super Admin can access all; others only their own school's invoices.
     */
    private function authorizeInvoiceAccess(Invoice $invoice): void
    {
        $user = auth()->user();

        if ($user->hasRole('Super Admin')) {
            return;
        }

        $school = $this->context->resolveSchool($user);

        if (! $school || $invoice->school_id !== $school->id) {
            throw new AuthorizationException('You are not authorized to view this invoice.');
        }
    }

    public function store(Subscription $subscription)
    {
        $invoice = $this->invoices->generateForSubscription($subscription);

        if ($invoice === null) {
            return back()->with(
                'error',
                'An invoice can only be generated for an active subscription.'
            );
        }

        return back()->with(
            'success',
            "Invoice {$invoice->invoice_number} generated successfully."
        );
    }

    public function markAsPaid(Invoice $invoice)
    {
        try {
            $this->invoices->markAsPaid($invoice);
        } catch (ValidationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with(
            'success',
            "Invoice {$invoice->invoice_number} marked as paid."
        );
    }

    public function void(Invoice $invoice)
    {
        try {
            $this->invoices->void($invoice);
        } catch (ValidationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with(
            'success',
            "Invoice {$invoice->invoice_number} voided successfully."
        );
    }
}
