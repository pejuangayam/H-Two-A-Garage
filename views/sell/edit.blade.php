@extends('layout2')

@section('content')

<h1 class="mb-4 page-title">Edit Items</h1>

<div class="card">
    <div class="card-header">Edit Item</div>
    <div class="card-body">
        <form action="{{ route('sell.update', $sell->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="updated_at">Date</label>
                <input type="date" class="form-control" id="updated_at" name="updated_at" value="{{ $sell->updated_at }}" required>
            </div>

            <div class="form-group">
                <label for="items">Item Name</label>
                <input type="text" class="form-control" id="items" name="items" value="{{ $sell->items }}" required>
            </div>
            
            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity" value="{{ $sell->quantity }}" required>
            </div>
            
            <div class="form-group">
                <label for="real_price">True Price</label>
                <input type="number" step="0.01" class="form-control" id="real_price" name="real_price" value="{{ $sell->real_price }}" required>
            </div>
            
            <div class="form-group">
                <label for="sales_price">Sell Price</label>
                <input type="number" step="0.01" class="form-control" id="sales_price" name="sales_price" value="{{ $sell->sales_price }}" required>
            </div>

            <div class="form-group">
                <label for="revenue">Revenue</label>
                <input type="number" step="0.01" class="form-control" id="revenue" name="revenue" value="{{ $sell->sales_price }}" readonly required>
            </div>

            <div class="form-group">
                <label for="total">Total</label>
                <input type="number" step="0.01" class="form-control" id="total" name="total" value="{{ $sell->sales_price }}" readonly required>
            </div>

            <!-- Added margin-top to create space above buttons -->
            <div style="margin-top: 20px;">
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('sell.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const quantityInput = document.getElementById('quantity');
        const realPriceInput = document.getElementById('real_price');
        const salesPriceInput = document.getElementById('sales_price');
        const revenueInput = document.getElementById('revenue');
        const totalInput = document.getElementById('total');

        function calculateValues() {
            const quantity = parseFloat(quantityInput.value) || 0;
            const realPrice = parseFloat(realPriceInput.value) || 0;
            const salesPrice = parseFloat(salesPriceInput.value) || 0;
            
            // Calculate revenue per item
            const revenue = salesPrice - realPrice;
            revenueInput.value = revenue.toFixed(2);
            
            // Calculate total (sales price * quantity)
            const total = salesPrice * quantity;
            totalInput.value = total.toFixed(2);
        }

        quantityInput.addEventListener('input', calculateValues);
        realPriceInput.addEventListener('input', calculateValues);
        salesPriceInput.addEventListener('input', calculateValues);
    });
</script>

<style>
.page-title {
    color: #2c3e50;
    font-weight: 600;
    border-bottom: 2px solid #3498db;
    padding-bottom: 10px;
}
</style>
@endsection