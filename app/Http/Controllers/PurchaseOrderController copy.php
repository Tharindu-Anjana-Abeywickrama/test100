<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Sku;
use App\Models\Region;
use App\Models\User;
use App\Models\Territory;
use App\Models\PoItem;
use App\Models\PurchaseOrder;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Auth;
use App\Models\FreeIssue;
use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $skus = Sku::select(
            'skus.id as sku_id',
            'skus.sku_code',
            'skus.sku_name',
            'skus.distributor_price as unit_price',
            'skus.weight_unit',
            'discounts.discount'
        )
        ->leftJoin('discounts', 'skus.id', '=', 'discounts.purches_product_id')
        ->get()
        ->map(function ($sku) {
            $discountPrice = $sku->unit_price;
            if ($sku->discount) {
                $discountPrice = ($sku->unit_price) - ($sku->unit_price)*($sku->discount/100);
            }
    
            return [ 
                'sku_id' => $sku->sku_id,
                'sku_code' => $sku->sku_code,
                'sku_name' => $sku->sku_name,
                'unit_price' => $sku->unit_price,
                'discount' => $sku->discount ?? 0,
                'discounted_price' => $discountPrice,
                'avg_qty' => 0,
                'enter_qty' => '',
                'units' => $sku->weight_unit,
                'total_price' => ''
            ];
        });
    

        return Inertia::render('PurchaseOrders/Index', [
            'skus' => $skus ]);
    }

   
    
    public function store(Request $request)
    {    
        $validated = $request->validate([ 
            'po_no'=>'required|string|max:255',
            'zone_id' => 'required',
            'region_id' => 'required',
            'territory_id' => 'required',
            'distributor_id' => 'required',
            'date' => 'required|date',
            'remark' => 'nullable|string',
            'items' => 'required|array'
        ]);
    
        $poNumber = $request->po_no;
    
        $purchaseOrder = PurchaseOrder::create([
            'user_id' => $validated['distributor_id'],
            'territory_id' => $validated['territory_id'],
            'created_by' => Auth::id(),
            'po_number' => $poNumber,
            'date' => $validated['date'],
            'remark' => $validated['remark'],
            'total_amount' => array_reduce($validated['items'], function ($carry, $item) {
                return $carry + ($item['enter_qty'] * $item['discounted_price']);
            }, 0)
        ]);
    
        foreach ($validated['items'] as $item) {
            // Normal item insert 

          
            PoItem::create([
                'po_id' => $purchaseOrder->id,
                'sku_id' => $item['sku_id'],
                'sku_code' => $item['sku_code'],
                'quantity' => $item['enter_qty'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['total_price'],
                'discounted_price' => $item['discounted_price'],
                'discount' => $item['discount']?? '0',
            ]);
    
            // Free Issue check
            $freeIssue = FreeIssue::where('purches_product_id', $item['sku_id'])->first();
            if ($freeIssue) {
                $freeQty = 0;
    
                if ($freeIssue->free_issue_type == 'Flat') {
                    if ($item['enter_qty'] >= $freeIssue->purches_qty) {
                        $freeQty = $freeIssue->free_qty;
                    }
                } elseif ($freeIssue->free_issue_type == 'Multiple') {
                    if ($item['enter_qty'] >= $freeIssue->purches_qty) {
                        $multiplier = floor($item['enter_qty'] / $freeIssue->purches_qty);
                        $freeQty = $multiplier * $freeIssue->free_qty;
                    }
                }
           
                if ($freeQty > 0) {
                    PoItem::create([
                        'po_id' => $purchaseOrder->id,
                        'sku_id' => $freeIssue->free_product_id,
                        'sku_code' => $freeIssue->freeProduct->sku_code ?? '',
                        'quantity' => $freeQty,
                        'unit_price' => 0,
                        'total_price' => 0
                    ]);
                }
            }
        }
    }
    


     public function generatePONumber() {
         // Fetch the last PO number from the purchase_orders table
         $lastPONumber = PurchaseOrder::orderBy('id', 'desc')->first()->po_number ?? '0000000000';
     
         // Extract the numeric part and increment it
         $numericPart = (int)substr($lastPONumber, 0, -3);
         $newNumericPart = str_pad($numericPart + 1, 7, '0', STR_PAD_LEFT);
     
         // Append milliseconds
         $milliseconds = round(microtime(true) * 1000) % 1000;
         $newPONumber = $newNumericPart . str_pad($milliseconds, 3, '0', STR_PAD_LEFT);
     
         return response()->json(['po_number' => $newPONumber]);
     }

    public function getRegionsByZone($zone_id)
    {
        $regions = Region::where('zone_id', $zone_id)->get();
        return response()->json($regions);
    } 

    public function getRegionsAll(){
        $regions = Region::all();
        return response()->json($regions);
    }

    public function getRegions()
    {
        $regions = Region::all();
        return response()->json($regions);
    }

    public function getTerritories()
    {
        $territories = Territory::all();
        return response()->json($territories);
    }

    public function getDistributors()
    {
        $distributors = User::where('role', 'distributor')->where('id',Auth::id())->get();
         return response()->json($distributors);
    } 

    public function viewPurchaseOrders(Request $request)
    {  
        $purchaseOrders = PurchaseOrder::with(['user', 'territory.region'])->where('created_by',Auth::id())
            ->orderBy('date', 'desc')
            ->paginate(10)
            ->map(function ($order) {
                return [
                    'id' => $order->id,  // Add this line
                    'region' => $order->territory->region->code ?? '',
                    'territory' => $order->territory->code ?? '',
                    'distributor' => $order->user->name ?? '',
                    'po_number' => $order->po_number,
                    'date' => $order->date,
                    'time' => date('H:i', strtotime($order->created_at)),
                    'total_amount' => number_format($order->total_amount, 2)
                ];
            });

        return Inertia::render('PurchaseOrders/ViewPurchaseOrder', [
            'initialOrders' => $purchaseOrders,
        ]);
    }

    public function getPurchaseOrderDetails($poId)
    {
        $purchaseOrder = PurchaseOrder::with(['user', 'items'])->findOrFail($poId);
        return response()->json($purchaseOrder);            
    } 

    public function getPurchaseOrders(Request $request) {
        $query = PurchaseOrder::with(['user', 'territory.region'])->where('created_by',Auth::id());
    
        if ($request->region) {
            $query->whereHas('territory.region', function($q) use ($request) {
                $q->where('id', $request->region);
            });
        }
        if ($request->territory) {
            $query->where('territory_id', $request->territory);
        }
        if ($request->po_no) {
            $query->where('po_number', 'LIKE', '%' . $request->po_no . '%');
        }
        if ($request->from) {
            $query->whereDate('date', '>=', $request->from);
        }
        if ($request->to) {
            $query->whereDate('date', '<=', $request->to);
        }
    
        $purchaseOrders = $query->orderBy('date', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,  // Make sure this is included
                    'region' => $order->territory->region->code ?? '',
                    'territory' => $order->territory->code ?? '',
                    'distributor' => $order->user->name ?? '',
                    'po_number' => $order->po_number,
                    'date' => $order->date,
                    'time' => date('H:i', strtotime($order->created_at)),
                    'total_amount' => number_format($order->total_amount, 2)
                ];
            });
    
        return response()->json(['data' => $purchaseOrders]);
    }
    
    // Add this new method to the controller
    public function exportPurchaseOrder($id)
    {   
        try {
            $purchaseOrder = PurchaseOrder::with(['user', 'territory.region', 'items.sku'])
                ->findOrFail($id);
    
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
    
            // Set title
            $sheet->setCellValue('A1', 'PURCHASE ORDER');
            $sheet->mergeCells('A1:E1');
            $sheet->getStyle('A1')->applyFromArray([
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ]);
    
            // Order information with better formatting
            $sheet->setCellValue('A3', 'PO Number:');
            $sheet->setCellValue('B3', $purchaseOrder->po_number);
            $sheet->setCellValue('A4', 'Date:');
            $sheet->setCellValue('B4', date('Y-m-d', strtotime($purchaseOrder->date)));
            $sheet->setCellValue('A5', 'Distributor:');
            $sheet->setCellValue('B5', $purchaseOrder->user->name);
            $sheet->setCellValue('A6', 'Territory:');
            $sheet->setCellValue('B6', $purchaseOrder->territory->code);
            $sheet->setCellValue('A7', 'Region:');
            $sheet->setCellValue('B7', $purchaseOrder->territory->region->code);
    
            // Style the header section
            $sheet->getStyle('A3:A7')->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8E8E8']
                ]
            ]);
    
            // Items header
            $headers = ['SKU Code', 'SKU Name', 'Quantity', 'Unit Price', 'Total Price'];
            foreach (range('A', 'E') as $index => $column) {
                $sheet->setCellValue($column . '9', $headers[$index]);
            }
    
            // Style the items header
            $sheet->getStyle('A9:E9')->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '28A745']  // Green color
                ],
                'font' => [
                    'color' => ['rgb' => 'FFFFFF']  // White text
                ]
            ]);
    
            // Add items with formatting
            $row = 10;
            foreach ($purchaseOrder->items as $item) {
                $sheet->setCellValue('A' . $row, $item->sku->sku_code); // Changed to get SKU code from SKU table
                $sheet->setCellValue('B' . $row, $item->sku->sku_name);
                $sheet->setCellValue('C' . $row, $item->quantity);
                $sheet->setCellValue('D' . $row, number_format($item->unit_price, 2));
                $sheet->setCellValue('E' . $row, number_format($item->total_price, 2));
                
                // Align numbers to right
                $sheet->getStyle('C' . $row . ':E' . $row)->applyFromArray([
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]
                ]);
                $row++;
            }
    
            // Add total with formatting
            $row += 1;
            $sheet->setCellValue('D' . $row, 'Total Amount:');
            $sheet->setCellValue('E' . $row, number_format($purchaseOrder->total_amount, 2));
            $sheet->getStyle('D' . $row . ':E' . $row)->applyFromArray([
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8E8E8']
                ],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]
            ]);
    
            // Auto-size columns
            foreach (range('A', 'E') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }
    
            // Add borders to the entire table
            $sheet->getStyle('A9:E' . ($row))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    ]
                ]
            ]);
    
            // Create the Excel file
            $writer = new Xlsx($spreadsheet);
            $filename = 'PO_' . $purchaseOrder->po_number . '.xlsx';
    
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
    
            $writer->save('php://output');
            return;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    } 

    public function bulkExportPurchaseOrders(Request $request) {
        $ids = explode(',', $request->input('ids', ''));
        $format = $request->input('format', 'excel');
    
        if (empty($ids) || (count($ids) === 1 && empty($ids[0]))) {
            if ($format === 'pdf') {
                $pdf = new Dompdf();
                $pdf->loadHtml('<h1>No Purchase Orders Available</h1>');
                $pdf->setPaper('A4', 'portrait');
                $pdf->render();
                return response()->streamDownload(function() use ($pdf) {
                    echo $pdf->output();
                }, 'Bulk_PO_Export.pdf', [
                    'Content-Type' => 'application/pdf'
                ]);
            } else {
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setCellValue('A1', 'No Purchase Orders Available');
                $writer = new Xlsx($spreadsheet);
                return response()->streamDownload(function() use ($writer) {
                    $writer->save('php://output');
                }, 'Bulk_PO_Export.xlsx', [
                    'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                ]);
            }
        }
    
        $ids = explode(',', $request->input('ids', ''));
        $format = $request->input('format', 'excel');
     
       
        if ($format === 'pdf') {
            $pdf = new Dompdf();
            $html = '<h1>Purchase Orders</h1>';
    
            foreach ($ids as $id) { 
                
                $po = PurchaseOrder::with(['user','territory.region','items.sku'])->find($id);
                if (!$po) continue;
    
                $html .= '<h2>PO Number: ' . $po->po_number . '</h2>';
                $html .= '<p>Date: ' . Carbon::parse($po->date)->format('Y-m-d') . '</p>';
                $html .= '<p>Distributor: ' . $po->user->name . '</p>';
                $html .= '<p>Territory: ' . $po->territory->code . '</p>';
                $html .= '<p>Region: ' . $po->territory->region->code . '</p>';
    
                $html .= '<table border="1"><tr><th>SKU Code</th><th>SKU Name</th><th>Quantity</th><th>Unit Price</th><th>Total Price</th></tr>';
                foreach ($po->items as $item) {
                    $html .= '<tr><td>' . $item->sku->sku_code . '</td><td>' . $item->sku->sku_name . '</td><td>' . $item->quantity . '</td><td>' . number_format($item->unit_price, 2) . '</td><td>' . number_format($item->total_price, 2) . '</td></tr>';
                }
                $html .= '</table>';
                $html .= '<p>Total Amount: ' . number_format($po->total_amount, 2) . '</p>';
            }
    
            $pdf->loadHtml($html);
            $pdf->setPaper('A4', 'portrait');
            $pdf->render();
            return response()->streamDownload(function() use ($pdf) {
                echo $pdf->output();
            }, 'Bulk_PO_Export.pdf', [
                'Content-Type' => 'application/pdf'
            ]);
        } else {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $row = 1;
    
            foreach ($ids as $id) {
                $po = PurchaseOrder::with(['user','territory.region','items.sku'])->find($id);
                if (!$po) continue;
    
                $sheet->setCellValue("A{$row}", 'PURCHASE ORDER');
                $sheet->mergeCells("A{$row}:E{$row}");
                $sheet->getStyle("A{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
                ]);
                $row += 2;
    
                $info = [
                    ['PO Number:', $po->po_number],
                    ['Date:', Carbon::parse($po->date)->format('Y-m-d')],
                    ['Distributor:', $po->user->name],
                    ['Territory:', $po->territory->code],
                    ['Region:', $po->territory->region->code],
                ];
                foreach ($info as $i => [$lbl, $val]) {
                    $r = $row + $i;
                    $sheet->setCellValue("A{$r}", $lbl);
                    $sheet->setCellValue("B{$r}", $val);
                }
                $sheet->getStyle("A{$row}:A" . ($row + 4))->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E8E8E8']
                    ]
                ]);
                $row += 6;
    
                $hdrs = ['SKU Code', 'SKU Name', 'Quantity', 'Unit Price', 'Total Price'];
                foreach (range('A', 'E') as $i => $col) {
                    $sheet->setCellValue("{$col}{$row}", $hdrs[$i]);
                }
                $sheet->getStyle("A{$row}:E{$row}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '28A745']
                    ]
                ]);
                $row++;
    
                foreach ($po->items as $item) {
                    $sheet->setCellValue("A{$row}", $item->sku->sku_code);
                    $sheet->setCellValue("B{$row}", $item->sku->sku_name);
                    $sheet->setCellValue("C{$row}", $item->quantity);
                    $sheet->setCellValue("D{$row}", number_format($item->unit_price, 2));
                    $sheet->setCellValue("E{$row}", number_format($item->total_price, 2));
                    $sheet->getStyle("C{$row}:E{$row}")->getAlignment()
                        ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT);
                    $row++;
                }
    
                $sheet->setCellValue("D{$row}", 'Total Amount:');
                $sheet->setCellValue("E{$row}", number_format($po->total_amount, 2));
                $sheet->getStyle("D{$row}:E{$row}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E8E8E8']
                    ],
                    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]
                ]);
                $row += 2;
            }
    
            // auto-size
            foreach (range('A', 'E') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            // borders
            $sheet->getStyle("A1:E" . ($row - 1))->applyFromArray([
                'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
            ]);
    
            $writer = new Xlsx($spreadsheet);
    
            return response()->streamDownload(function() use ($writer) {
                $writer->save('php://output');
            }, 'Bulk_PO_Export.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            ]);
        }
    }

    public function viewPurchaseOrder($id)
    {
        $order = PurchaseOrder::with(['user', 'territory.region', 'items.sku'])->where('created_by',Auth::id())
            ->findOrFail($id);
    
        $orderData = [
            'id' => $order->id,
            'po_number' => $order->po_number,
            'date' => $order->date,
            'time' => date('H:i', strtotime($order->created_at)),
            'distributor' => $order->user->name,
            'region' => $order->territory->region->code,
            'territory' => $order->territory->code,
            'total_amount' => $order->total_amount,
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'sku_code' => $item->sku->sku_code, // Add this line to get SKU code
                    'sku' => [
                        'sku_name' => $item->sku->sku_name
                    ],
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'discount' => $item->discount,
                    'discounted_price' => $item->discounted_price,
                    'total_price' => $item->total_price
                ];
            })
        ];
    
        return Inertia::render('PurchaseOrders/ShowPurchaseOrder', [
            'order' => $orderData
        ]);
    }
    
    public function storePurchaseOrder(Request $request) {
        $validated = $request->validate([
            'po_no' => 'required|string|max:255',
            'zone_id' => 'required',
            'region_id' => 'required',
            'territory_id' => 'required',
            'distributor_id' => 'required',
            'date' => 'required|date',
            'remark' => 'nullable|string',
            'items' => 'required|array'
        ]);
    
        $poNumber = $request->po_no;
    
        $purchaseOrder = PurchaseOrder::create([
            'user_id' => $validated['distributor_id'],
            'created_by' => Auth::id(),
            'territory_id' => $validated['territory_id'],
            'po_number' => $poNumber,
            'date' => $validated['date'],
            'remark' => $validated['remark'],
            'total_amount' => array_reduce($validated['items'], function ($carry, $item) {
                return $carry + ($item['enter_qty'] * $item['unit_price']);
            }, 0)
        ]);
    
        foreach ($validated['items'] as $item) {
            PoItem::create([
                'po_id' => $purchaseOrder->id,
                'sku_id' => $item['sku_id'],
                'sku_code' => $item['sku_code'],
                'quantity' => $item['enter_qty'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['total_price']
            ]);
        }
    
        return response()->json(['success' => true, 'purchase_order_id' => $purchaseOrder->id]);
    }
}
