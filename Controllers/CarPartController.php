<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\CarPart;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CarPartController extends Controller
{
    // Constants for better maintainability
    private const UPLOAD_DIR = 'car-parts';
    private const MAX_FILE_SIZE = 2048; // KB
    private const ALLOWED_EXTENSIONS = ['jpeg', 'png', 'jpg', 'gif', 'svg', 'webp'];

    private const CATEGORIES = [
        'Engine' => 'Engine Parts',
        'Transmission' => 'Transmission Parts',
        'Electrical' => 'Electrical Parts',
        'Suspension' => 'Suspension Parts',
        'Brakes' => 'Brake System',
        'Exterior' => 'Exterior Parts',
        'Interior' => 'Interior Parts',
        'Accessories' => 'Accessories'
    ];

    public function index(Request $request)
    {
        $query = CarPart::query();

        // Add search functionality
        if ($search = $request->get('search')) {
            $query->search($search);
        }

        // Add category filter
        if ($category = $request->get('category')) {
            $query->category($category);
        }

        $carParts = $query->latest()->paginate(10)->withQueryString();

        return view('inventory.index', compact('carParts'));
    }

    public function create()
    {
        return view('inventory.create', ['categories' => self::CATEGORIES]);
    }

    public function store(Request $request)
    {
        // Enhanced validation with better rules
        $validated = $request->validate([
            'name' => 'required|string|max:255|min:2',
            'description' => 'nullable|string|max:1000',
            'quantity' => 'required|integer|min:0|max:999999',
            'part_number' => 'nullable|string|max:100|unique:car_parts,part_number',
            'category' => ['nullable', 'string', Rule::in(array_keys(self::CATEGORIES))],
            'image' => [
                'nullable',
                'image',
                'max:' . self::MAX_FILE_SIZE,
                'mimes:' . implode(',', self::ALLOWED_EXTENSIONS)
            ]
        ]);

        // Use database transaction for data integrity
        DB::beginTransaction();

        try {
            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $this->handleImageUpload($request->file('image'));
                if ($imagePath) {
                    $validated['image_path'] = $imagePath;
                } else {
                    throw new Exception('Image upload failed');
                }
            }

            // Create record
            $carPart = CarPart::create($validated);

            DB::commit();

            Log::info('Car part created successfully', ['id' => $carPart->id, 'name' => $carPart->name]);

            return redirect()->route('inventory.index')
                ->with('success', 'Car part added successfully.');
        } catch (Exception $e) {
            DB::rollBack();

            // Clean up uploaded file if it exists
            if (isset($validated['image_path'])) {
                $this->deleteImageFile($validated['image_path']);
            }

            Log::error('Failed to create car part', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);

            return redirect()->back()
                ->with('error', 'Failed to save car part. Please try again.')
                ->withInput();
        }
    }

    public function show(CarPart $carPart)
    {
        return view('inventory.show', compact('carPart'));
    }

    public function edit(CarPart $carPart)
    {
        return view('inventory.edit', [
            'carPart' => $carPart,
            'categories' => self::CATEGORIES
        ]);
    }

    public function update(Request $request, CarPart $carPart)
    {
        // Enhanced validation with unique rule exception
        $validated = $request->validate([
            'name' => 'required|string|max:255|min:2',
            'description' => 'nullable|string|max:1000',
            'quantity' => 'required|integer|min:0|max:999999',
            'part_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('car_parts', 'part_number')->ignore($carPart->id)
            ],
            'category' => ['nullable', 'string', Rule::in(array_keys(self::CATEGORIES))],
            'image' => [
                'nullable',
                'image',
                'max:' . self::MAX_FILE_SIZE,
                'mimes:' . implode(',', self::ALLOWED_EXTENSIONS)
            ]
        ]);

        $oldImagePath = $carPart->image_path;

        DB::beginTransaction();

        try {
            // Handle new image upload
            if ($request->hasFile('image')) {
                $imagePath = $this->handleImageUpload($request->file('image'));
                if ($imagePath) {
                    $validated['image_path'] = $imagePath;
                } else {
                    throw new Exception('Image upload failed');
                }
            }

            // Update record
            $carPart->update($validated);

            // Delete old image if we uploaded a new one
            if (isset($validated['image_path']) && $oldImagePath && $oldImagePath !== $validated['image_path']) {
                $this->deleteImageFile($oldImagePath);
            }

            DB::commit();

            Log::info('Car part updated successfully', ['id' => $carPart->id, 'name' => $carPart->name]);

            return redirect()->route('inventory.index')
                ->with('success', 'Car part updated successfully.');
        } catch (Exception $e) {
            DB::rollBack();

            // Clean up newly uploaded file if update failed
            if (isset($validated['image_path'])) {
                $this->deleteImageFile($validated['image_path']);
            }

            Log::error('Failed to update car part', [
                'error' => $e->getMessage(),
                'id' => $carPart->id
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update car part. Please try again.')
                ->withInput();
        }
    }

    public function destroy(CarPart $carPart)
    {
        $imagePath = $carPart->image_path;
        $carPartName = $carPart->name;

        DB::beginTransaction();

        try {
            // Delete from database
            $carPart->delete();

            // Delete associated image
            if ($imagePath) {
                $this->deleteImageFile($imagePath);
            }

            DB::commit();

            Log::info('Car part deleted successfully', ['name' => $carPartName]);

            return redirect()->route('inventory.index')
                ->with('success', 'Car part deleted successfully.');
        } catch (Exception $e) {
            DB::rollBack();

            Log::error('Failed to delete car part', [
                'error' => $e->getMessage(),
                'id' => $carPart->id
            ]);

            return redirect()->route('inventory.index')
                ->with('error', 'Failed to delete car part. Please try again.');
        }
    }

    public function download(CarPart $carPart)
    {
        if (!$carPart->image_path) {
            return redirect()->back()->with('error', 'No image available for download.');
        }

        $filePath = $this->getFullImagePath($carPart->image_path);

        if (!file_exists($filePath)) {
            Log::warning('Download requested for missing file', [
                'car_part_id' => $carPart->id,
                'image_path' => $carPart->image_path
            ]);

            return redirect()->back()->with('error', 'Image file not found.');
        }

        try {
            $extension = pathinfo($carPart->image_path, PATHINFO_EXTENSION);
            $downloadName = $this->sanitizeFilename($carPart->name) . '.' . $extension;

            return response()->download($filePath, $downloadName);
        } catch (Exception $e) {
            Log::error('Failed to download image', [
                'error' => $e->getMessage(),
                'car_part_id' => $carPart->id
            ]);

            return redirect()->back()->with('error', 'Failed to download image.');
        }
    }

    /**
     * Handle image upload with improved security and error handling
     */
    private function handleImageUpload($file): ?string
    {
        if (!$file || !$file->isValid()) {
            return null;
        }

        // Additional security checks
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_EXTENSIONS)) {
            throw new Exception('Invalid file type');
        }

        // Check actual file type (not just extension)
        $mimeType = $file->getMimeType();
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
        if (!in_array($mimeType, $allowedMimes)) {
            throw new Exception('Invalid file format');
        }

        // Generate secure filename
        $filename = time() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;

        // Ensure upload directory exists
        $uploadDir = storage_path('app/public/' . self::UPLOAD_DIR);
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Move file
        $destinationPath = $uploadDir . '/' . $filename;

        if (move_uploaded_file($file->getPathname(), $destinationPath)) {
            // Set proper permissions
            chmod($destinationPath, 0644);
            return self::UPLOAD_DIR . '/' . $filename;
        }

        throw new Exception('Failed to move uploaded file');
    }

    /**
     * Delete image file safely
     */
    private function deleteImageFile(string $imagePath): bool
    {
        $fullPath = $this->getFullImagePath($imagePath);

        if (file_exists($fullPath)) {
            try {
                return unlink($fullPath);
            } catch (Exception $e) {
                Log::warning('Failed to delete image file', [
                    'path' => $fullPath,
                    'error' => $e->getMessage()
                ]);
                return false;
            }
        }

        return true; // File doesn't exist, consider it "deleted"
    }

    /**
     * Get full path to image file
     */
    private function getFullImagePath(string $imagePath): string
    {
        return storage_path('app/public/' . $imagePath);
    }

    /**
     * Sanitize filename for download
     */
    private function sanitizeFilename(string $filename): string
    {
        return preg_replace('/[^A-Za-z0-9\-_]/', '_', $filename);
    }

    public function updateQuantity(Request $request, CarPart $carPart)
    {
        // Validate the quantity input
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0|max:999999'
        ]);

        try {
            // Update only the quantity field
            $carPart->update(['quantity' => $validated['quantity']]);

            Log::info('Quantity updated successfully', [
                'id' => $carPart->id,
                'old_quantity' => $carPart->getOriginal('quantity'),
                'new_quantity' => $validated['quantity']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Quantity updated successfully',
                'quantity' => $carPart->quantity,
                'in_stock' => $carPart->quantity > 0
            ]);
        } catch (Exception $e) {
            Log::error('Failed to update quantity', [
                'error' => $e->getMessage(),
                'id' => $carPart->id,
                'quantity' => $validated['quantity']
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update quantity. Please try again.'
            ], 500);
        }
    }

    /**
     * Generate and download PDF for car part
     */
    public function downloadPdf(CarPart $carPart)
    {
        try {
            // Prepare data for PDF
            $data = [
                'carPart' => $carPart,
                'imagePath' => $carPart->image_path ? storage_path('app/public/' . $carPart->image_path) : null,
                'generatedAt' => now()->format('F j, Y \a\t g:i A')
            ];

            // Generate PDF
            $pdf = Pdf::loadView('inventory.pdf', $data);
            $pdf->setPaper('A4', 'portrait');

            // Create filename
            $filename = $this->sanitizeFilename($carPart->name) . '_details.pdf';

            Log::info('PDF generated successfully', [
                'car_part_id' => $carPart->id,
                'filename' => $filename
            ]);

            return $pdf->download($filename);
        } catch (Exception $e) {
            Log::error('Failed to generate PDF', [
                'error' => $e->getMessage(),
                'car_part_id' => $carPart->id
            ]);

            return redirect()->back()->with('error', 'Failed to generate PDF. Please try again.');
        }
    }
}
