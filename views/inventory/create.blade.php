@extends('layout4')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Add New Car Part</h5>
                    <a href="{{ route('inventory.index') }}" class="btn btn-light btn-sm">← Back</a>
                </div>
                <div class="card-body">
                    <form action="{{ route('inventory.store') }}" method="POST" enctype="multipart/form-data" id="createForm">
                        @csrf

                        {{-- Name --}}
                        <div class="mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ old('name') }}" 
                                class="form-control shadow-sm @error('name') is-invalid @enderror"
                                placeholder="Enter part name" required>
                            @error('name')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="3" 
                                class="form-control shadow-sm @error('description') is-invalid @enderror"
                                placeholder="Describe the car part (optional)...">{{ old('description') }}</textarea>
                            @error('description')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Quantity --}}
                        <div class="mb-3">
                            <label class="form-label">Quantity <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" value="{{ old('quantity') }}" 
                                class="form-control shadow-sm @error('quantity') is-invalid @enderror"
                                min="0" placeholder="0" required>
                            @error('quantity')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Part Number --}}
                        <div class="mb-3">
                            <label class="form-label">Part Number</label>
                            <input type="text" name="part_number" value="{{ old('part_number') }}" 
                                class="form-control shadow-sm @error('part_number') is-invalid @enderror"
                                placeholder="Enter part number (optional)">
                            @error('part_number')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Category --}}
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select shadow-sm @error('category') is-invalid @enderror">
                                <option value="">-- Select Category --</option>
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        {{-- Image Upload --}}
                        <div class="mb-4">
                            <label class="form-label">Upload Image</label>
                            <input type="file" name="image" 
                                class="form-control shadow-sm @error('image') is-invalid @enderror"
                                accept="image/*" id="imageInput">
                            @error('image')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                            <small class="form-text text-muted">
                                Supported formats: JPEG, PNG, GIF, SVG, WebP. Max size: 2MB.
                            </small>
                            
                            {{-- Image Preview --}}
                            <div id="imagePreview" class="mt-3" style="display: none;">
                                <p class="mb-2"><strong>Image Preview:</strong></p>
                                <div class="border rounded p-2 bg-light">
                                    <img id="previewImg" src="" alt="Image preview" 
                                         class="img-fluid rounded" style="max-height: 200px; object-fit: cover;">
                                    <button type="button" class="btn btn-sm btn-outline-danger mt-2" 
                                            onclick="clearImagePreview()">Remove Image</button>
                                </div>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-outline-secondary px-4" 
                                    onclick="clearForm()" id="clearBtn">
                                Clear Form
                            </button>
                            <button type="submit" class="btn btn-success px-4 shadow-sm" id="submitBtn">
                                <i class="bi bi-plus-circle"></i> Add Part
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
    const form = document.getElementById('createForm');
    const imageInput = document.getElementById('imageInput');
    const imagePreview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const clearBtn = document.getElementById('clearBtn');
    const submitBtn = document.getElementById('submitBtn');
    
    let formModified = false;
    let isSubmitting = false;

    // Track form changes
    const formElements = form.querySelectorAll('input, textarea, select');
    formElements.forEach(element => {
        element.addEventListener('input', function() {
            formModified = true;
            updateClearButtonState();
        });
    });

    // Image preview functionality
    imageInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            // Validate file size (2MB = 2048KB)
            if (file.size > 2048 * 1024) {
                alert('File size is too large. Please select an image smaller than 2MB.');
                this.value = '';
                imagePreview.style.display = 'none';
                return;
            }

            // Validate file type
            const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                alert('Please select a valid image file (JPEG, PNG, GIF, SVG, or WebP).');
                this.value = '';
                imagePreview.style.display = 'none';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
                imagePreview.style.animation = 'fadeIn 0.3s ease-in';
            };
            reader.readAsDataURL(file);
            formModified = true;
            updateClearButtonState();
        } else {
            imagePreview.style.display = 'none';
        }
    });

    // Clear form functionality
    window.clearForm = function() {
        if (formModified && !confirm('This will clear all entered data. Are you sure?')) {
            return;
        }
        
        form.reset();
        clearImagePreview();
        formModified = false;
        updateClearButtonState();
        
        // Remove validation errors
        form.querySelectorAll('.is-invalid').forEach(el => {
            el.classList.remove('is-invalid');
        });
        form.querySelectorAll('.text-danger').forEach(el => {
            if (el.tagName === 'SMALL') {
                el.style.display = 'none';
            }
        });
    };

    // Clear image preview
    window.clearImagePreview = function() {
        imageInput.value = '';
        imagePreview.style.display = 'none';
        formModified = true;
        updateClearButtonState();
    };

    // Update clear button state
    function updateClearButtonState() {
        if (formModified) {
            clearBtn.classList.remove('btn-outline-secondary');
            clearBtn.classList.add('btn-outline-warning');
            clearBtn.innerHTML = 'Clear Form <small>(unsaved changes)</small>';
        } else {
            clearBtn.classList.remove('btn-outline-warning');
            clearBtn.classList.add('btn-outline-secondary');
            clearBtn.innerHTML = 'Clear Form';
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
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span>Adding...';
        submitBtn.disabled = true;
        
        // Re-enable if there's an error (form doesn't actually submit)
        setTimeout(() => {
            if (isSubmitting) {
                submitBtn.innerHTML = '<i class="bi bi-plus-circle"></i> Add Part';
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

    // Auto-save to localStorage (optional enhancement)
    function autoSave() {
        if (formModified) {
            const formData = {
                name: form.querySelector('[name="name"]').value,
                description: form.querySelector('[name="description"]').value,
                quantity: form.querySelector('[name="quantity"]').value,
                part_number: form.querySelector('[name="part_number"]').value,
                category: form.querySelector('[name="category"]').value,
                timestamp: Date.now()
            };
            
            try {
                localStorage.setItem('carpart_draft', JSON.stringify(formData));
            } catch (e) {
                // Ignore localStorage errors
            }
        }
    }

    // Auto-save every 30 seconds
    setInterval(autoSave, 30000);

    // Load draft on page load
    try {
        const draft = localStorage.getItem('carpart_draft');
        if (draft) {
            const data = JSON.parse(draft);
            // Only load if draft is less than 24 hours old
            if (Date.now() - data.timestamp < 24 * 60 * 60 * 1000) {
                if (confirm('We found a draft of your previous entry. Would you like to restore it?')) {
                    form.querySelector('[name="name"]').value = data.name || '';
                    form.querySelector('[name="description"]').value = data.description || '';
                    form.querySelector('[name="quantity"]').value = data.quantity || '';
                    form.querySelector('[name="part_number"]').value = data.part_number || '';
                    form.querySelector('[name="category"]').value = data.category || '';
                    formModified = true;
                    updateClearButtonState();
                }
            }
            localStorage.removeItem('carpart_draft'); // Clean up regardless
        }
    } catch (e) {
        // Ignore localStorage errors
    }

    // Focus on first input for better UX
    form.querySelector('[name="name"]').focus();
});
</script>

<style>
/* Maintain original styling while adding enhancements */
.form-control:focus, .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
}

#imagePreview {
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
</style>
@endsection