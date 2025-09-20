//KENA DEBUG NI
<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Service;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ServiceController extends Controller
{
    // Configuration constants - can be moved to config file later
    private const MAX_QUANTITY = 10000;
    private const MAX_PER_PRICE = 100000;
    private const MAX_LABOR_COST = 100000;
    private const MAX_TOTAL_AMOUNT = 1000000;
    private const MAX_VEHICLE_TOTAL = 10000000;
    private const INVOICE_RESET_NUMBER = 5000;

    public function store(Request $request)
    {
        // Enhanced validation with better error messages
        $data = $request->validate([
            'vehicle_id'   => ['required', 'exists:vehicles,id'],
            'service_date' => ['required', 'date', 'before_or_equal:today'],
            'items'        => ['required', 'string', 'max:255'],
            'quantity'     => ['required', 'integer', 'min:1', 'max:' . self::MAX_QUANTITY],
            'per_price'    => ['required', 'numeric', 'min:0', 'max:' . self::MAX_PER_PRICE],
            'labour_total' => ['nullable', 'numeric', 'min:0', 'max:' . self::MAX_LABOR_COST],
        ], [
            'service_date.before_or_equal' => 'Service date cannot be in the future.',
            'quantity.max' => 'Quantity cannot exceed ' . number_format(self::MAX_QUANTITY) . ' items.',
            'per_price.max' => 'Price per item cannot exceed $' . number_format(self::MAX_PER_PRICE) . '.',
            'labour_total.max' => 'Labor cost cannot exceed $' . number_format(self::MAX_LABOR_COST) . '.',
        ]);

        // Clean input data
        $data['items'] = trim($data['items']);

        try {
            DB::transaction(function () use ($data) {
                // Calculate total with enhanced validation
                $data['total'] = $this->calculateItemTotal($data['quantity'], $data['per_price']);

                // Handle labor cost logic for duplicate dates (existing logic preserved)
                $exists = Service::where('vehicle_id', $data['vehicle_id'])
                    ->whereDate('service_date', $data['service_date'])
                    ->exists();

                $data['labour_total'] = $exists ? 0 : ($data['labour_total'] ?? 0);

                $service = Service::create($data);

                $this->updateGrandTotal($service->vehicle_id, $service->service_date);
                $this->updateVehicleSumTotal($service->vehicle_id);
            });

            return redirect()->back()->with('success', 'Service added successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error creating service: ' . $e->getMessage(), [
                'vehicle_id' => $data['vehicle_id'] ?? null,
                'service_date' => $data['service_date'] ?? null
            ]);

            return redirect()->back()
                ->with('error', 'Failed to add service. Please try again.')
                ->withInput();
        }
    }

    public function update(Request $request, Service $service)
    {
        // Enhanced validation with better error messages
        $data = $request->validate([
            'service_date' => ['required', 'date', 'before_or_equal:today'],
            'items'        => ['nullable', 'string', 'max:255'],
            'quantity'     => ['nullable', 'integer', 'min:1', 'max:' . self::MAX_QUANTITY],
            'per_price'    => ['nullable', 'numeric', 'min:0', 'max:' . self::MAX_PER_PRICE],
            'labour_total' => ['nullable', 'numeric', 'min:0', 'max:' . self::MAX_LABOR_COST],
        ], [
            'service_date.before_or_equal' => 'Service date cannot be in the future.',
            'quantity.max' => 'Quantity cannot exceed ' . number_format(self::MAX_QUANTITY) . ' items.',
            'per_price.max' => 'Price per item cannot exceed $' . number_format(self::MAX_PER_PRICE) . '.',
            'labour_total.max' => 'Labor cost cannot exceed $' . number_format(self::MAX_LABOR_COST) . '.',
        ]);

        // Clean input data
        if (isset($data['items'])) {
            $data['items'] = trim($data['items']);
        }

        try {
            DB::transaction(function () use ($request, $service, $data) {
                // Preserve existing logic exactly
                if ($request->filled('labour_total')) {
                    $this->updateLaborForDate($service->vehicle_id, $data['service_date'], $request->labour_total);
                } else {
                    // Update the normal item row with enhanced validation
                    if (isset($data['quantity']) && isset($data['per_price'])) {
                        $data['total'] = $this->calculateItemTotal($data['quantity'], $data['per_price']);
                    }

                    $service->update($data);
                }

                $this->updateGrandTotal($service->vehicle_id, $service->service_date);
                $this->updateVehicleSumTotal($service->vehicle_id);
            });

            return redirect()->back()->with('success', 'Service updated successfully.');
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating service: ' . $e->getMessage(), [
                'service_id' => $service->id,
                'vehicle_id' => $service->vehicle_id
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update service. Please try again.')
                ->withInput();
        }
    }

    public function destroy(Service $service)
    {
        $vehicleId = $service->vehicle_id;
        $serviceDate = $service->service_date;

        try {
            DB::transaction(function () use ($service, $vehicleId, $serviceDate) {
                $service->delete();

                if ($serviceDate) {
                    $this->updateGrandTotal($vehicleId, $serviceDate);
                }

                $this->updateVehicleSumTotal($vehicleId);
            });

            return redirect()->back()->with('success', 'Service deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting service: ' . $e->getMessage(), [
                'service_id' => $service->id,
                'vehicle_id' => $vehicleId
            ]);

            return redirect()->back()
                ->with('error', 'Failed to delete service. Please try again.');
        }
    }

    public function manage($vehicleId)
    {
        try {
            $vehicle = Vehicle::with(['service' => function ($q) {
                $q->orderBy('service_date')->orderBy('id');
            }])->findOrFail($vehicleId);

            return view('services.manage', compact('vehicle'));
        } catch (\Exception $e) {
            Log::error('Error loading vehicle services: ' . $e->getMessage(), [
                'vehicle_id' => $vehicleId
            ]);

            return redirect()->back()
                ->with('error', 'Failed to load vehicle services. Please try again.');
        }
    }

    public function downloadPdf(Request $request, $vehicleId, $date)
    {
        // Rate limiting check (basic implementation)
        $cacheKey = 'pdf_download_' . request()->ip() . '_' . $vehicleId;
        if (Cache::has($cacheKey)) {
            return redirect()->back()
                ->with('error', 'Please wait before downloading another PDF. Try again in a few moments.');
        }

        try {
            $vehicle = Vehicle::findOrFail($vehicleId);

            $services = Service::where('vehicle_id', $vehicleId)
                ->whereDate('service_date', $date)
                ->get();

            if ($services->isEmpty()) {
                return redirect()->back()
                    ->with('error', 'No services found for the selected date.');
            }

            // Enhanced calculation with better error handling
            $totals = $this->calculateTotalsForServices($services);
            $invoice = $this->findOrCreateInvoice((int)$vehicleId, $date);

            $pdf = Pdf::loadView('services.download-pdf', [
                'vehicle'     => $vehicle,
                'items'       => $services,
                'invoiceNo'   => $invoice->invoice_no,
                'date'        => $invoice->invoice_date,
                'subtotal'    => $totals['subtotal'],
                'labour'      => $totals['labour'],
                'totalAmount' => $totals['total'],
            ]);

            // Set cache for rate limiting (30 seconds)
            Cache::put($cacheKey, true, 30);

            return $pdf->download("Invoice-{$invoice->invoice_no}.pdf");
        } catch (\Exception $e) {
            Log::error('Error generating PDF: ' . $e->getMessage(), [
                'vehicle_id' => $vehicleId,
                'date' => $date
            ]);

            return redirect()->back()
                ->with('error', 'Failed to generate PDF. Please try again.');
        }
    }

    // ENHANCED PRIVATE METHODS (preserving existing behavior)

    private function calculateItemTotal(int $quantity, float $perPrice): float
    {
        $total = $quantity * $perPrice;

        if ($total > self::MAX_TOTAL_AMOUNT) {
            throw new \InvalidArgumentException(
                'Calculated total ($' . number_format($total, 2) . ') exceeds maximum allowed amount ($' . number_format(self::MAX_TOTAL_AMOUNT, 2) . '). Please check your inputs.'
            );
        }

        return $total;
    }

    private function calculateTotalsForServices($services): array
    {
        $subtotal = $services->sum(function ($service) {
            $q = (float) ($service->quantity ?? 0);
            $p = (float) ($service->per_price ?? 0);
            return $q * $p;
        });

        $labour = (float) $services->max('labour_total');
        $total = $subtotal + $labour;

        return [
            'subtotal' => $subtotal,
            'labour' => $labour,
            'total' => $total
        ];
    }

    private function updateLaborForDate(int $vehicleId, string $serviceDate, float $laborTotal): void
    {
        // Reset labour_total = 0 for all services on that date
        Service::where('vehicle_id', $vehicleId)
            ->whereDate('service_date', $serviceDate)
            ->update(['labour_total' => 0]);

        // Assign the new labour_total only to the FIRST service record for that date
        $firstService = Service::where('vehicle_id', $vehicleId)
            ->whereDate('service_date', $serviceDate)
            ->orderBy('id')
            ->first();

        if ($firstService) {
            $firstService->update(['labour_total' => $laborTotal]);
        }
    }

    private function updateGrandTotal($vehicleId, $serviceDate)
    {
        try {
            // Use more efficient single query with subqueries
            $totals = Service::where('vehicle_id', $vehicleId)
                ->whereDate('service_date', $serviceDate)
                ->selectRaw('
                    SUM(total) as parts_total,
                    SUM(labour_total) as labour_total
                ')
                ->first();

            $grandTotal = ($totals->parts_total ?? 0) + ($totals->labour_total ?? 0);

            // Update all services for this date with the grand total
            Service::where('vehicle_id', $vehicleId)
                ->whereDate('service_date', $serviceDate)
                ->update(['grand_total' => $grandTotal]);

            // Clear cache for this vehicle if using caching
            $this->clearVehicleCache($vehicleId);
        } catch (\Exception $e) {
            Log::error('Error updating grand total: ' . $e->getMessage(), [
                'vehicle_id' => $vehicleId,
                'service_date' => $serviceDate
            ]);
            throw $e;
        }
    }

    private function updateVehicleSumTotal($vehicleId)
    {
        try {
            // More efficient query to avoid duplicate grand_total values
            $sumTotal = Service::where('vehicle_id', $vehicleId)
                ->select('service_date', DB::raw('MAX(grand_total) as daily_total'))
                ->groupBy('service_date')
                ->get()
                ->sum('daily_total');

            // Enhanced safety check with better logging
            if ($sumTotal > self::MAX_VEHICLE_TOTAL) {
                Log::warning('Unusually high sumTotal calculated', [
                    'vehicle_id' => $vehicleId,
                    'sum_total' => $sumTotal,
                    'threshold' => self::MAX_VEHICLE_TOTAL
                ]);

                // Optional: Add notification system here
                // event(new HighVehicleTotalDetected($vehicleId, $sumTotal));
            }

            // Update the vehicle's sumTotal column
            Vehicle::where('id', $vehicleId)->update(['sumTotal' => $sumTotal]);

            // Clear cache for this vehicle
            $this->clearVehicleCache($vehicleId);
        } catch (\Exception $e) {
            Log::error('Error updating vehicle sumTotal', [
                'vehicle_id' => $vehicleId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            // Don't throw here to prevent cascading failures
        }
    }

    protected function findOrCreateInvoice(int $vehicleId, string $date): Invoice
    {
        // Check cache first for frequently accessed invoices
        $cacheKey = "invoice_{$vehicleId}_{$date}";
        $existing = Cache::remember($cacheKey, 300, function () use ($vehicleId, $date) {
            return Invoice::where('vehicle_id', $vehicleId)
                ->whereDate('invoice_date', $date)
                ->first();
        });

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($vehicleId, $date, $cacheKey) {
            // Double-check within transaction to prevent race conditions
            $existing = Invoice::where('vehicle_id', $vehicleId)
                ->whereDate('invoice_date', $date)
                ->first();

            if ($existing) {
                Cache::put($cacheKey, $existing, 300);
                return $existing;
            }

            // Enhanced invoice number generation with better collision handling
            $last = Invoice::lockForUpdate()->orderBy('id', 'desc')->first();
            $nextNumber = $last ? (int)$last->invoice_no + 1 : 1;

            // Reset at configured number
            if ($nextNumber > self::INVOICE_RESET_NUMBER) {
                $nextNumber = 1;
            }

            $invoiceNo = str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'vehicle_id'   => $vehicleId,
                'invoice_date' => $date,
                'invoice_no'   => $invoiceNo,
            ]);

            // Cache the new invoice
            Cache::put($cacheKey, $invoice, 300);

            return $invoice;
        });
    }

    // HELPER METHODS

    private function clearVehicleCache($vehicleId): void
    {
        // Clear relevant cache keys when vehicle data changes
        Cache::forget("vehicle_services_{$vehicleId}");
        Cache::forget("vehicle_total_{$vehicleId}");

        // Clear invoice cache for this vehicle
        $today = now()->format('Y-m-d');
        Cache::forget("invoice_{$vehicleId}_{$today}");
    }
}
