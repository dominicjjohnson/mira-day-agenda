# Unload Policy Fix for WP Bakery Page Builder

## Problem
Modern browsers (Chrome, Firefox, Safari) implement a Permissions Policy that restricts the use of `beforeunload` and `unload` events to prevent malicious websites from trapping users. This causes the error:

```
[Violation] Permissions policy violation: unload is not allowed in this document.
```

WP Bakery Page Builder uses these events to warn users about unsaved changes, which triggers this violation.

## Solution
This plugin implements a comprehensive fix that:

1. **Removes existing beforeunload listeners** - Cleans up any existing WP Bakery beforeunload event listeners
2. **Prevents new listeners** - Overrides `addEventListener` and jQuery's `.on()` method to block beforeunload/unload events
3. **Preserves functionality** - Maintains WP Bakery's data change tracking without the problematic event listeners
4. **Sets headers** - Adds Permissions Policy headers for admin pages to allow unload events when needed

## Files
- `assets/js/fix-unload-policy.js` - Main fix script
- `test-unload-fix.html` - Test page to verify the fix works

## Implementation
The fix is automatically loaded on:
- All frontend pages (to prevent issues with WP Bakery frontend editor)
- All admin pages (to fix backend editor issues)
- Pages with WP Bakery editor active

## Technical Details

### JavaScript Overrides
```javascript
// Block new beforeunload/unload listeners
window.addEventListener = function(type, listener, options) {
    if (type === 'beforeunload' || type === 'unload') {
        console.warn('Blocked attempt to add ' + type + ' event listener');
        return;
    }
    return originalAddEventListener.call(this, type, listener, options);
};
```

### WP Bakery Compatibility
```javascript
// Override vc.setDataChanged to work without beforeunload
window.vc.setDataChanged = function() {
    // Maintains undo functionality but skips beforeunload listener
    this.data_changed = true;
};
```

### PHP Headers
```php
// Add permissive policy for admin pages
header('Permissions-Policy: unload=*, beforeunload=*');
```

## Testing
1. Open the test page: `test-unload-fix.html`
2. Open browser console
3. Click the test buttons
4. Verify that beforeunload listeners are blocked
5. Check that console shows "Blocked attempt to add beforeunload event listener"

## Browser Compatibility
- Chrome 88+ ✅
- Firefox 84+ ✅
- Safari 14+ ✅
- Edge 88+ ✅

## Notes
- This fix does not affect the actual save/undo functionality of WP Bakery
- Users will still be able to save their work normally
- The fix only removes the browser warning about unsaved changes
- For production sites, consider updating WP Bakery to the latest version which may have this issue resolved

## Troubleshooting
If you still see the error:
1. Clear browser cache
2. Check that the fix script is loading (see console logs)
3. Verify that DEVMODE is enabled for debugging output
4. Check that no other plugins are re-adding beforeunload listeners

## Version History
- v1.20: Initial unload policy fix implementation
