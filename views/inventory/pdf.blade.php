<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $carPart->name }} - Details</title>
    <style>
        * {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            margin: 0;
            padding: 25px;
            color: #2c3e50;
            line-height: 1.7;
            background-color: #ffffff;
        }
        
        .header {
            text-align: center;
            margin-bottom: 35px;
            border-bottom: 3px solid #3498db;
            padding-bottom: 25px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #2c3e50;
            margin: 0 0 15px 0;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .header .subtitle {
            color: #6c757d;
            font-size: 16px;
            margin: 0;
            font-weight: 500;
        }
        
        .content {
            display: table;
            width: 100%;
            margin-top: 25px;
            border-spacing: 0;
        }
        
        .image-section {
            display: table-cell;
            width: 45%;
            vertical-align: top;
            padding-right: 25px;
        }
        
        .details-section {
            display: table-cell;
            width: 55%;
            vertical-align: top;
        }
        
        .car-image {
            max-width: 100%;
            height: auto;
            border: 3px solid #e9ecef;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            background: #fff;
        }
        
        .no-image {
            width: 100%;
            height: 250px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: 2px dashed #adb5bd;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
            font-size: 16px;
            font-weight: 500;
        }
        
        .details-grid {
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            border-radius: 12px;
            padding: 25px;
            border: 1px solid #e9ecef;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
            margin-bottom: 8px;
        }
        
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }
        
        .detail-label {
            font-weight: 600;
            color: #495057;
            flex: 1;
            font-size: 15px;
        }
        
        .detail-value {
            flex: 1.5;
            text-align: right;
            font-weight: 500;
            color: #2c3e50;
            font-size: 15px;
        }
        
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-left: 8px;
        }
        
        .status-in-stock {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .status-out-of-stock {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .category-badge {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: #ffffff;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .description-section {
            margin-top: 30px;
            padding: 25px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 2px solid #e9ecef;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .description-title {
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 18px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 8px;
            display: inline-block;
        }
        
        .description-text {
            color: #495057;
            line-height: 1.8;
            font-size: 15px;
        }
        
        .footer {
            margin-top: 45px;
            text-align: center;
            font-size: 13px;
            color: #6c757d;
            border-top: 2px solid #e9ecef;
            padding-top: 20px;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 20px;
            border-radius: 8px;
        }
        
        .footer p {
            margin: 5px 0;
            font-weight: 500;
        }
        
        .quantity-highlight {
            font-size: 20px;
            font-weight: 700;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        
        .quantity-in-stock {
            color: #28a745;
        }
        
        .quantity-out-of-stock {
            color: #dc3545;
        }
        
        .price-highlight {
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
        }
        
        .part-number {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f8f9fa;
            padding: 4px 8px;
            border-radius: 6px;
            border: 1px solid #dee2e6;
            font-size: 14px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        
        /* Modern styling additions */
        .info-card {
            background: #ffffff;
            border-left: 4px solid #3498db;
            padding: 15px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .timestamp {
            font-size: 13px;
            color: #6c757d;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $carPart->name }}</h1>
        <p class="subtitle">Inventory Item Details Report</p>
    </div>

    <div class="content">
        <div class="image-section">
            @if($imagePath && file_exists($imagePath))
                <img src="data:image/{{ pathinfo($carPart->image_path, PATHINFO_EXTENSION) }};base64,{{ base64_encode(file_get_contents($imagePath)) }}" 
                     alt="{{ $carPart->name }}" 
                     class="car-image">
            @else
                <div class="no-image">
                    📦 No Image Available
                </div>
            @endif
        </div>

        <div class="details-section">
            <div class="details-grid">
                <div class="detail-row">
                    <span class="detail-label">Stock Quantity:</span>
                    <span class="detail-value">
                        <span class="quantity-highlight {{ $carPart->quantity > 0 ? 'quantity-in-stock' : 'quantity-out-of-stock' }}">
                            {{ $carPart->quantity }} units
                        </span>
                        <br>
                        <span class="status-badge {{ $carPart->quantity > 0 ? 'status-in-stock' : 'status-out-of-stock' }}">
                            {{ $carPart->quantity > 0 ? 'In Stock' : 'Out of Stock' }}
                        </span>
                    </span>
                </div>

                @if($carPart->price)
                <div class="detail-row">
                    <span class="detail-label">Unit Price:</span>
                    <span class="detail-value price-highlight">RM {{ number_format($carPart->price, 2) }}</span>
                </div>
                @endif

                @if($carPart->part_number)
                <div class="detail-row">
                    <span class="detail-label">Part Number:</span>
                    <span class="detail-value">
                        <span class="part-number">{{ $carPart->part_number }}</span>
                    </span>
                </div>
                @endif

                @if($carPart->category)
                <div class="detail-row">
                    <span class="detail-label">Category:</span>
                    <span class="detail-value">
                        <span class="category-badge">{{ $carPart->category }}</span>
                    </span>
                </div>
                @endif

                <div class="detail-row">
                    <span class="detail-label">Date Added:</span>
                    <span class="detail-value timestamp">{{ $carPart->created_at->format('F j, Y \a\t g:i A') }}</span>
                </div>

                @if($carPart->updated_at && $carPart->updated_at != $carPart->created_at)
                <div class="detail-row">
                    <span class="detail-label">Last Modified:</span>
                    <span class="detail-value timestamp">{{ $carPart->updated_at->format('F j, Y \a\t g:i A') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    @if($carPart->description)
    <div class="description-section">
        <div class="description-title">Item Description</div>
        <div class="description-text">{{ $carPart->description }}</div>
    </div>
    @endif

    <div class="footer">
        <p><strong>Generated:</strong> {{ $generatedAt }}</p>
        <p>Car Parts Inventory Management System</p>
        <p style="margin-top: 10px; font-size: 12px;">This document contains confidential business information</p>
    </div>
</body>
</html>