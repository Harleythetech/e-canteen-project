# QR Code System

## Overview

QR codes are used for order pickup verification. When a student's order is paid, a QR code appears on their order status page. Staff scan this code at the counter to mark the order as completed.

---

## QR Code Generation

QR codes are generated server-side by `QrCodeService` (`app/Services/QrCodeService.php`) using the **BaconQrCode** library.

```php
public function generateSvg(string $data, int $size = 200): string
```

- **Input**: The order number string (e.g., `ORD-ABCDE1234`)
- **Output**: An SVG string that can be embedded directly in HTML with `{!! $qrSvg !!}`
- **Size**: 200×200 pixels by default

The SVG format means the QR code scales perfectly on any screen size without pixelation.

---

## When QR Codes Are Shown

QR codes are displayed on the `OrderStatus` page when the order status is `paid`, `preparing`, or `ready`:

```php
$qrSvg = in_array($this->order->status, ['paid', 'preparing', 'ready'])
    ? $qrCodeService->generateSvg($this->order->order_number)
    : null;
```

The QR code is **not shown** for `pending`, `completed`, or `cancelled` orders.

---

## QR Code Scanning (Staff)

The staff dashboard has a QR scanner panel powered by **html5-qrcode** (a JavaScript library).

### Camera Scanner
- Activates the device camera (requires HTTPS in production)
- Scans at 10 FPS with a 200×200 pixel scan box
- Uses the rear camera (`facingMode: 'environment'`) on mobile
- Has a 3-second cooldown between scans to prevent duplicate processing
- Stops automatically when navigating away (`livewire:navigated` event)

### Manual Lookup
A text input + button for entering order numbers manually when camera isn't available.

### Processing Flow

When a QR code is scanned, `Staff\Dashboard::processQrCode()` is called:

```
Scan order number
    │
    ├── Order not found → error toast
    │
    ├── Order status = ready
    │       └── transitionTo('completed') → success toast
    │
    ├── Order status = completed
    │       └── info toast (already done)
    │
    └── Any other status
            └── error toast (not ready for pickup)
```

### Scan Result Display

Results appear in the scanner panel for 4 seconds:
- **Green** — Order completed successfully
- **Red** — Order not found or not ready
- **Yellow** — Order already completed

---

## Security

The QR code encodes only the **order number** (e.g., `ORD-ABCDE1234`), not any sensitive data. The server validates the order number on scan and checks authorization via the `OrderPolicy`.

Students cannot fake a QR code because:
1. Order numbers are randomly generated (`ORD-` + 9 random uppercase alphanumeric characters)
2. The server looks up the order by number and verifies its status
3. Only `ready` orders can be completed via QR scan
