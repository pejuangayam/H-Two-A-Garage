@extends('layout4')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Car Part</h5>
                    <a href="{{ route('inventory.index') }}" class="btn btn-light btn-sm">← Back</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('inventory.update', $carPart) }}" method="POST" enctype="multipart/form-data" id="editForm">
                        @csrf
                        @method('PUT')

                        {{-- Name - Pre-populated --}}
                        <div class="mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name', $carPart->name) }}" 
                                class="form-control shadow-sm @error('name') is-invalid @enderror"
                                placeholder="Enter part name" required>
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Description - Pre-populated --}}
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="3" 
                                class="form-control shadow-sm @error('description') is-invalid @enderror"
                                placeholder="Describe the car part...">{{ old('description', $carPart->description) }}</textarea>
                            @error('description')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Quantity - Pre-populated --}}
                        <div class="mb-3">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" value="{{ old('quantity', $carPart->quantity) }}" 
                                class="form-control shadow-sm @error('quantity') is-invalid @enderror"
                                min="0" placeholder="0" required>
                            @error('quantity')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Part Number - Pre-populated --}}
                        <div class="mb-3">
                            <label class="form-label">Part Number</label>
                            <input type="text" name="part_number" value="{{ old('part_number', $carPart->part_number) }}" 
                                class="form-control shadow-sm @error('part_number') is-invalid @enderror"
                                placeholder="Enter part number">
                            @error('part_number')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Category - Pre-selected --}}
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select shadow-sm @error('category') is-invalid @enderror">
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}" 
                                        {{ old('category', $carPart->category) == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Current Image Display --}}
                        @if($carPart->image_path)
                        <div class="mb-3">
                            <label class="form-label">Current Image</label>
                            <div class="current-image-container p-3 border rounded bg-light" id="currentImageContainer">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <img src="{{ asset('storage/' . $carPart->image_path) }}" 
                                             alt="Current image" 
                                             class="img-fluid rounded shadow-sm"
                                             style="max-height: 150px; object-fit: cover;" id="currentImage">
                                    </div>
                                    <div class="col-md-8">
                                        <p class="mb-1"><strong>Current Image:</strong> {{ basename($carPart->image_path) }}</p>
                                        <p class="text-muted small mb-2">Upload a new image below to replace this one, or leave empty to keep the current image.</p>
                                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmRemoveCurrentImage()">
                                            Remove Current Image
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        {{-- New Image Upload --}}
                        <div class="mb-4">
                            <label class="form-label">
                                {{ $carPart->image_path ? 'Replace Image' : 'Upload Image' }}
                                @if($carPart->image_path)
                                    <small class="text-muted">(Leave empty to keep current image)</small>
                                @endif
                            </label>
                            <input type="file" name="image" 
                                class="form-control shadow-sm @error('image') is-invalid @enderror"
                                accept="image/*" id="imageInput">
                            @error('image')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                            <small class="form-text text-muted">
                                Supported formats: JPEG, PNG, GIF, SVG, WebP. Max size: 2MB.
                            </small>
                            
                            {{-- New Image Preview --}}
                            <div id="newImagePreview" class="mt-3" style="display: none;">
                                <p class="mb-2"><strong>New Image Preview:</strong></p>
                                <div class="border rounded p-2 bg-info bg-opacity-10">
                                    <img id="previewImg" src="" alt="New image preview" 
                                         class="img-fluid rounded" style="max-height: 200px; object-fit: cover;">
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="clearNewImagePreview()">Remove New Image</button>
                                        <small class="text-muted d-block mt-1">This will replace the current image when you save.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <a href="{{ route('inventory.index') }}" class="btn btn-secondary px-4 me-2">
                                    Cancel
                                </a>
                                <button type="button" class="btn btn-outline-warning px-4" 
                                        onclick="resetToOriginal()" id="resetBtn">
                                    Reset Changes
                                </button>
                            </div>
                            <button type="submit" class="btn btn-warning px-4 shadow-sm" id="submitBtn">
                                <i class="bi bi-save"></i> Update Part
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editForm');
    const imageInput = document.getElementById('imageInput');
    const newImagePreview = document.getElementById('newImagePreview');
    const previewImg = document.getElementById('previewImg');
    const resetBtn = document.getElementById('resetBtn');
    const submitBtn = document.getElementById('submitBtn');
    const currentImageContainer = document.getElementById('currentImageContainer');
    
    let formModified = false;
    let isSubmitting = false;
    let currentImageRemoved = false;
    
    // Store original values for reset functionality
    const originalValues = {
        name: '{{ addslashes($carPart->name) }}',
        description: '{{ addslashes($carPart->description ?? '') }}',
        quantity: '{{ $carPart->quantity }}',
        part_number: '{{ addslashes($carPart->part_number ?? '') }}',
        category: '{{ $carPart->category ?? '' }}'
    };

    // Track form changes
    const formElements = form.querySelectorAll('input, textarea, select');
    formElements.forEach(element => {
        element.addEventListener('input', function() {
            formModified = true;
            updateResetButtonState();
        });
    });

    // Image preview functionality
    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            // Validate file size (2MB)
            if (file.size > 2048 * 1024) {
                alert('File size is too large. Please select an image smaller than 2MB.');
                this.value = '';
                newImagePreview.style.display = 'none';
                return;
            }

            // Validate file type
            const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert('Please select a valid image file (JPEG, PNG, GIF, SVG, or WebP).');
                this.value = '';
                newImagePreview.style.display = 'none';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                newImagePreview.style.display = 'block';
                newImagePreview.style.animation = 'fadeIn 0.3s ease-in';
            };
            reader.readAsDataURL(file);
            formModified = true;
            updateResetButtonState();
        } else {
            newImagePreview.style.display = 'none';
        }
    });

    // Clear new image preview
    window.clearNewImagePreview = function() {
        imageInput.value = '';
        newImagePreview.style.display = 'none';
        formModified = true;
        updateResetButtonState();
    };

    // Confirm remove current image
    window.confirmRemoveCurrentImage = function() {
        if (confirm('Are you sure you want to remove the current image? This action cannot be undone.')) {
            currentImageContainer.style.display = 'none';
            currentImageRemoved = true;
            formModified = true;
            updateResetButtonState();
            
            // Add hidden input to indicate image removal
            let removeInput = document.getElementById('removeCurrentImage');
            if (!removeInput) {
                removeInput = document.createElement('input');
                removeInput.type = 'hidden';
                removeInput.name = 'remove_current_image';
                removeInput.id = 'removeCurrentImage';
                removeInput.value = '1';
                form.appendChild(removeInput);
            }
        }
    };

    // Reset to original values
    window.resetToOriginal = function() {
        if (formModified && !confirm('This will reset all changes to the original values. Are you sure?')) {
            return;
        }
        
        // Reset form fields to original values
        form.querySelector('input[name="name"]').value = originalValues.name;
        form.querySelector('textarea[name="description"]').value = originalValues.description;
        form.querySelector('input[name="quantity"]').value = originalValues.quantity;
        form.querySelector('input[name="part_number"]').value = originalValues.part_number;
        form.querySelector('select[name="category"]').value = originalValues.category;
        
        // Clear new image
        imageInput.value = '';
        newImagePreview.style.display = 'none';
        
        // Restore current image if it was removed
        if (currentImageRemoved && currentImageContainer) {
            currentImageContainer.style.display = 'block';
            currentImageRemoved = false;
            const removeInput = document.getElementById('removeCurrentImage');
            if (removeInput) {
                removeInput.remove();
            }
        }
        
        formModified = false;
        updateResetButtonState();
        
        // Remove validation errors
        form.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        form.querySelectorAll('.text-danger small').forEach(el => {
            el.style.display = 'none';
        });
    };

    // Update reset button state
    function updateResetButtonState() {
        if (formModified) {
            resetBtn.classList.remove('btn-outline-warning');
            resetBtn.classList.add('btn-outline-danger');
            resetBtn.innerHTML = 'Reset Changes <small>(unsaved changes)</small>';
        } else {
            resetBtn.classList.remove('btn-outline-danger');
            resetBtn.classList.add('btn-outline-warning');
            resetBtn.innerHTML = 'Reset Changes';
        }
    }

    // Form submission handling
    form.addEventListener('submit', function(e) {
        if (isSubmitting) {
            e.preventDefault();
            return;
        }

        isSubmitting = true;
        formModified = false;
        
        // Update submit button to show loading state
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Updating...';
        submitBtn.disabled = true;
        
        // Re-enable if there's an error (form doesn't actually submit)
        setTimeout(() => {
            if (isSubmitting) {
                submitBtn.innerHTML = '<i class="bi bi-save"></i> Update Part';
                submitBtn.disabled = false;
                isSubmitting = false;
            }
        }, 10000); // 10 second timeout
    });

    // Warn before leaving if form is modified
    window.addEventListener('beforeunload', function(e) {
        if (formModified && !isSubmitting) {
            e.preventDefault();
            e.returnValue = 'You have unsaved changes. Are you sure you want to leave?';
        }
    });

    // Auto-focus on first input for better UX
    form.querySelector('input[name="name"]').focus();
    
    // Select all text in name field for quick editing
    form.querySelector('input[name="name"]').select();
});
</script>

<style>
/* Warning theme colors for edit form */
.form-control:focus, .form-select:focus {
    border-color: #ffc107;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
}

.current-image-container {
    transition: all 0.3s ease;
}

.current-image-container:hover {
    background-color: #f8f9fa !important;
    border-color: #6c757d !important;
}

#newImagePreview {
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}

/* Button hover effects */
.btn:hover {
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

/* Form validation enhancements */
.is-invalid {
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

/* Loading state */
.btn:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}

/* Highlight changes */
.form-control.changed {
    border-left: 3px solid #ffc107;
}
</style>
@endsection