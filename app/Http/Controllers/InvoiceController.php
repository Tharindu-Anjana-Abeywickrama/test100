<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\PoItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the invoices.
     */
    public function index()
    {
        $invoices = Invoice::with(['purchaseOrder', 'creator'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'po_number' => $invoice->purchaseOrder->po_number,
                    'purchase_order_id' => $invoice->purchase_order_id,
                    'date' => $invoice->date,
                    'total_amount' => $invoice->total_amount,
                    'status' => $invoice->status,
                    'distributor' => $invoice->purchaseOrder->distributor->name,
                    'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
                ];
            });

        $purchaseOrders = PurchaseOrder::whereNotIn('id', Invoice::pluck('purchase_order_id'))
            ->with('distributor')
            ->get()
            ->map(function ($po) {
                return [
                    'id' => $po->id,
                    'po_number' => $po->po_number,
                    'date' => $po->date,
                    'total_amount' => $po->total_amount,
                    'distributor' => $po->distributor->name,
                ];
            });

        return Inertia::render('Invoice/Index', [
            'invoices' => $invoices,
            'purchaseOrders' => $purchaseOrders,
        ]);
    }

    /**
     * Show the details of a specific invoice.
     */
    public function show($id)
    {
        $invoice = Invoice::with(['purchaseOrder.items.sku', 'purchaseOrder.distributor'])
            ->findOrFail($id);

        return Inertia::render('Invoice/Show', [
            'invoice' => [
                'id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'date' => $invoice->date,
                'total_amount' => $invoice->total_amount,
                'status' => $invoice->status,
                'remarks' => $invoice->remarks,
                'purchase_order' => [
                    'id' => $invoice->purchaseOrder->id,
                    'po_number' => $invoice->purchaseOrder->po_number,
                    'date' => $invoice->purchaseOrder->date,
                    'distributor' => $invoice->purchaseOrder->distributor->name,
                    'items' => $invoice->purchaseOrder->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'sku_code' => $item->sku_code,
                            'sku_name' => $item->sku->sku_name,
                            'quantity' => $item->quantity,
                            'unit_price' => $item->unit_price,
                            'discount' => $item->discount,
                            'discounted_price' => $item->discounted_price,
                            'total_price' => $item->total_price,
                        ];
                    }),
                ],
            ],
        ]);
    }

    /**
     * Show the purchase order details for creating an invoice.
     */
    public function createFromPurchaseOrder($id)
    {
        $purchaseOrder = PurchaseOrder::with(['items.sku', 'distributor'])
            ->findOrFail($id);

        return Inertia::render('Invoice/Create', [
            'purchaseOrder' => [
                'id' => $purchaseOrder->id,
                'po_number' => $purchaseOrder->po_number,
                'date' => $purchaseOrder->date,
                'total_amount' => $purchaseOrder->total_amount,
                'distributor' => $purchaseOrder->distributor->name,
                'items' => $purchaseOrder->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'sku_code' => $item->sku_code,
                        'sku_name' => $item->sku->sku_name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'discount' => $item->discount,
                        'discounted_price' => $item->discounted_price,
                        'total_price' => $item->total_price,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Store a newly created invoice in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'purchase_order_id' => 'required|exists:purchase_orders,id',
            'invoice_number' => 'required|string|unique:invoices,invoice_number',
            'date' => 'required|date',
            'remarks' => 'nullable|string',
            'items' => 'required|array',
            'total_amount' => 'required|numeric',
        ]);

        // Calculate total amount from items to ensure it matches
        $calculatedTotal = 0;
        foreach ($request->items as $item) {
            $calculatedTotal += $item['invoice_qty'] * $item['discounted_price'];
        }

        $invoice = Invoice::create([
            'purchase_order_id' => $validated['purchase_order_id'],
            'invoice_number' => $validated['invoice_number'],
            'date' => $validated['date'],
            'total_amount' => $calculatedTotal,
            'status' => 'pending',
            'remarks' => $validated['remarks'],
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('invoice.index')
            ->with('success', 'Invoice created successfully.');
    }

    /**
     * Update the specified invoice status.
     */
    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,paid,cancelled',
        ]);

        $invoice = Invoice::findOrFail($id);
        $invoice->update([
            'status' => $validated['status'],
        ]);

        return redirect()->back()
            ->with('success', 'Invoice status updated successfully.');
    }

    /**
     * Generate a unique invoice number.
     */
    public function generateInvoiceNumber()
    {
        $prefix = 'INV-' . date('Ymd');
        $lastInvoice = Invoice::where('invoice_number', 'like', $prefix . '%')->latest()->first();

        if ($lastInvoice) {
            $lastNumber = intval(substr($lastInvoice->invoice_number, -4));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return response()->json([
            'invoice_number' => $prefix . '-' . str_pad($newNumber, 4, '0', STR_PAD_LEFT),
        ]);
    }

    /**
     * Get purchase order items by purchase order ID.
     */
    public function getPurchaseOrderItems($id)
    {
        $purchaseOrder = PurchaseOrder::with(['items.sku', 'distributor'])
            ->findOrFail($id);

        return response()->json([
            'purchase_order' => [
                'id' => $purchaseOrder->id,
                'po_number' => $purchaseOrder->po_number,
                'date' => $purchaseOrder->date,
                'total_amount' => $purchaseOrder->total_amount,
                'distributor' => $purchaseOrder->distributor->name,
                'items' => $purchaseOrder->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'sku_id' => $item->sku_id,
                        'sku_code' => $item->sku_code,
                        'sku_name' => $item->sku->sku_name,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'discount' => $item->discount,
                        'discounted_price' => $item->discounted_price,
                        'total_price' => $item->total_price,
                        'invoice_qty' => $item->quantity, // Default invoice quantity to PO quantity
                    ];
                }),
            ],
        ]);
    }
}