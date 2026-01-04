# At Your Service - Color Scheme & Styling Guide

## WordPress-Native Color Palette

The plugin now uses WordPress-standard colors for a consistent, professional appearance.

### Color Variables

#### Primary Colors
```css
--ays-primary-color: #2271b1;        /* WordPress admin blue */
--ays-primary-hover: #135e96;        /* Darker blue for hover states */
```

#### Secondary Colors
```css
--ays-secondary-color: #f0f0f1;      /* WordPress light gray background */
--ays-secondary-dark: #dcdcde;       /* WordPress medium gray */
```

#### Text Colors
```css
--ays-text-color: #1d2327;           /* WordPress dark text (primary) */
--ays-text-light: #50575e;           /* WordPress light text (secondary) */
```

#### System Status Colors
```css
--ays-success-color: #00a32a;        /* WordPress green */
--ays-warning-color: #dba617;        /* WordPress yellow */
--ays-error-color: #d63638;          /* WordPress red */
--ays-info-color: #72aee6;           /* WordPress light blue */
```

#### Structural Colors
```css
--ays-background-color: #ffffff;     /* White background */
--ays-border-color: #c3c4c7;         /* WordPress border gray */
```

---

## Usage Guide

### Cards

Use cards for grouping related content:

```html
<!-- Basic Card -->
<div class="ays-card">
    <div class="ays-card-header">
        Card Title
    </div>
    <div class="ays-card-body">
        Card content goes here...
    </div>
</div>
```

**Visual**: White background with light gray header, subtle shadow

### Collapsible Details/Summary

Use for expandable content sections:

```html
<details class="ays-details">
    <summary>Click to expand</summary>
    <div class="ays-details-content">
        Hidden content that appears when expanded...
    </div>
</details>
```

**Features**:
- Animated arrow indicator (▶ rotates to ▼ when open)
- Consistent with WordPress admin styling
- Smooth transitions

### Buttons

```html
<!-- Primary Action -->
<button class="ays-btn ays-btn-primary">Save Changes</button>

<!-- Secondary Action -->
<button class="ays-btn ays-btn-secondary">Cancel</button>

<!-- Status Buttons -->
<button class="ays-btn ays-btn-success">Approve</button>
<button class="ays-btn ays-btn-warning">Review</button>
<button class="ays-btn ays-btn-error">Delete</button>
```

**Visual**: 
- Primary: WordPress blue background, white text
- Hover states: Darker shade with smooth transition

### Alerts/Notices

```html
<!-- Info Notice -->
<div class="ays-alert ays-alert-info">
    This is an informational message.
</div>

<!-- Success Notice -->
<div class="ays-alert ays-alert-success">
    Operation completed successfully!
</div>

<!-- Warning Notice -->
<div class="ays-alert ays-alert-warning">
    Please review this before proceeding.
</div>

<!-- Error Notice -->
<div class="ays-alert ays-alert-error">
    An error occurred. Please try again.
</div>
```

**Visual**: Light colored backgrounds with matching colored borders

### Forms

```html
<div class="ays-form-group">
    <label class="ays-form-label" for="input-id">Field Label</label>
    <input type="text" class="ays-form-control" id="input-id" placeholder="Enter value">
</div>
```

**Features**:
- Clean, minimal design
- Blue focus ring when active
- Consistent spacing

### Tables

```html
<table class="ays-table">
    <thead>
        <tr>
            <th>Column 1</th>
            <th>Column 2</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Data 1</td>
            <td>Data 2</td>
        </tr>
    </tbody>
</table>
```

**Features**:
- Light gray header background
- Hover effect on rows
- Clean borders

### Badges

```html
<span class="ays-badge ays-badge-primary">New</span>
<span class="ays-badge ays-badge-success">Active</span>
<span class="ays-badge ays-badge-warning">Pending</span>
<span class="ays-badge ays-badge-error">Canceled</span>
<span class="ays-badge ays-badge-info">Draft</span>
```

**Visual**: Small, colored labels for status indicators

---

## Migration Guide

### Replacing Old Colors

If you have existing inline styles or hardcoded colors, replace them with CSS variables:

**Before:**
```css
.my-element {
    color: #007bff;
    background-color: #dadcdd;
    border-color: #dee2e6;
}
```

**After:**
```css
.my-element {
    color: var(--ays-primary-color);
    background-color: var(--ays-secondary-color);
    border-color: var(--ays-border-color);
}
```

### Updating Existing Components

#### Old Card Style → New Card Style
**Before:**
```html
<div style="background: #f5f5f5; padding: 20px; border: 1px solid #ccc;">
    Content
</div>
```

**After:**
```html
<div class="ays-card">
    <div class="ays-card-body">
        Content
    </div>
</div>
```

#### Old Details/Summary → New Details/Summary
**Before:**
```html
<details style="background: white; border: 1px solid #ddd; padding: 10px;">
    <summary style="cursor: pointer; font-weight: bold;">Title</summary>
    Content
</details>
```

**After:**
```html
<details class="ays-details">
    <summary>Title</summary>
    <div class="ays-details-content">
        Content
    </div>
</details>
```

---

## Spacing Utilities

Use consistent spacing throughout:

```css
--ays-spacing-xs: 0.3125rem;  /* 5px - Minimal spacing */
--ays-spacing-sm: 0.625rem;   /* 10px - Small spacing */
--ays-spacing-md: 1.25rem;    /* 20px - Standard spacing */
--ays-spacing-lg: 1.875rem;   /* 30px - Large spacing */
--ays-spacing-xl: 2.5rem;     /* 40px - Extra large spacing */
```

**Example Usage:**
```css
.my-container {
    padding: var(--ays-spacing-md);
    margin-bottom: var(--ays-spacing-lg);
}
```

---

## Border Radius

Consistent rounded corners:

```css
--ays-border-radius-sm: 0.25rem;  /* 4px - Subtle rounding */
--ays-border-radius-md: 0.5rem;   /* 8px - Standard rounding */
--ays-border-radius-lg: 1rem;     /* 16px - Large rounding */
```

---

## Typography

Font sizes and line heights:

```css
--ays-font-size-base: 1rem;       /* 16px - Body text */
--ays-font-size-lg: 1.25rem;      /* 20px - Large text */
--ays-font-size-xl: 1.75rem;      /* 28px - Headings */
--ays-line-height-base: 1.5;      /* Standard line height */
```

---

## Best Practices

### 1. Always Use Variables
Never use hardcoded colors. Always reference CSS variables for consistency.

### 2. Semantic Color Usage
- Use `--ays-primary-color` for main actions and links
- Use `--ays-success-color` for positive actions
- Use `--ays-error-color` for destructive actions
- Use `--ays-warning-color` for cautionary messages
- Use `--ays-info-color` for informational elements

### 3. Consistent Component Usage
Use the provided utility classes rather than creating custom styles. This ensures:
- Visual consistency
- Easier maintenance
- Better WordPress integration

### 4. Accessibility
The WordPress color palette is designed for accessibility:
- All text colors meet WCAG AA contrast requirements
- Focus states are clearly visible
- Color is never the only indicator of meaning

---

## Examples in Context

### Booking Form Card
```html
<div class="ays-card">
    <div class="ays-card-header">
        New Booking
    </div>
    <div class="ays-card-body">
        <div class="ays-form-group">
            <label class="ays-form-label" for="service">Service Type</label>
            <select class="ays-form-control" id="service">
                <option>Select a service...</option>
            </select>
        </div>
        <div class="ays-form-group">
            <label class="ays-form-label" for="date">Date</label>
            <input type="date" class="ays-form-control" id="date">
        </div>
        <button class="ays-btn ays-btn-primary">Create Booking</button>
    </div>
</div>
```

### Invoice Details Collapsible
```html
<details class="ays-details">
    <summary>Invoice #12345 <span class="ays-badge ays-badge-success">Paid</span></summary>
    <div class="ays-details-content">
        <table class="ays-table">
            <tr>
                <td>Customer:</td>
                <td>John Doe</td>
            </tr>
            <tr>
                <td>Amount:</td>
                <td>$150.00</td>
            </tr>
        </table>
    </div>
</details>
```

---

## Color Comparison

### Old vs New Color Scheme

| Element | Old Color | New Color | Reason |
|---------|-----------|-----------|--------|
| Primary | #007bff (Bootstrap Blue) | #2271b1 (WordPress Blue) | Better WordPress integration |
| Secondary | #dadcdd | #f0f0f1 | Matches WordPress admin |
| Text | #000000 (Pure Black) | #1d2327 (WordPress Dark) | Easier on eyes, better contrast ratio |
| Borders | #dee2e6 | #c3c4c7 | Consistent with WP admin |
| Success | Not defined | #00a32a | WordPress standard green |
| Warning | Not defined | #dba617 | WordPress standard yellow |
| Error | Not defined | #d63638 | WordPress standard red |

---

## Troubleshooting

### Colors Not Applying?

1. **Check CSS is loaded**: Verify the stylesheet is enqueued in your theme/plugin
2. **CSS specificity**: Ensure no other styles are overriding the variables
3. **Browser cache**: Clear cache and hard reload (Ctrl+F5)
4. **Variable scope**: CSS variables must be used within the document root

### Classes Not Working?

1. **Class name**: Ensure you're using the exact class name (e.g., `ays-card` not `ays-cards`)
2. **Multiple classes**: Use space-separated for multiple classes: `class="ays-btn ays-btn-primary"`
3. **CSS file loaded**: Confirm the main stylesheet is loaded on your page

---

## Future Enhancements

Potential additions for future versions:

1. **Dark Mode Support**: Alternative color palette for dark themes
2. **Color Customization**: Admin interface to customize color variables
3. **Additional Components**: Tabs, modals, tooltips with consistent styling
4. **Utility Classes**: Margin/padding utilities, text color utilities
5. **Animation Library**: Consistent transitions and animations

---

## Support

For questions or issues with the color scheme:
- Check the plugin documentation
- Review WordPress design guidelines: https://developer.wordpress.org/block-editor/
- Ensure you're using the latest plugin version

Last Updated: January 4, 2026
Version: 1.2.3
