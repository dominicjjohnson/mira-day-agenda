# Technical Configuration Backup - Working My Diary Setup
**Date**: August 7, 2025  
**Status**: ✅ WORKING CONFIGURATION

## WordPress Environment 🌐
- **WordPress Version**: Latest
- **WP Bakery Version**: 8.6.1
- **PHP Version**: Compatible with XAMPP
- **Server**: XAMPP Local Development
- **Base URL**: http://127.0.0.1/plug/

## Plugin Configuration 🔌

### Mira Day Agenda Plugin
**Version**: 1.29  
**Main File**: `mira-day-agenda.php`
**Status**: ✅ Active and Working

### Critical Settings
```php
// AJAX Handler Registration (WORKING)
add_action('wp_ajax_get_diary_sessions', 'handle_get_diary_sessions');
add_action('wp_ajax_nopriv_get_diary_sessions', 'handle_get_diary_sessions');

// Script Localization (WORKING)
wp_localize_script('mira-mydiary-script', 'mira_diary_ajax', array(
    'ajaxurl' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('diary_nonce')
));

// Output Buffer Management (WORKING)
while (ob_get_level()) {
    ob_end_clean();
}
ob_start();
// ... AJAX processing
ob_clean();
wp_send_json_success($sessions);
```

## JavaScript Configuration 📜

### Working AJAX Function
```javascript
// Safe AJAX URL Detection (WORKING)
function getAjaxUrl() {
    if (typeof mira_diary_ajax !== 'undefined' && mira_diary_ajax.ajaxurl) {
        return mira_diary_ajax.ajaxurl;
    }
    if (typeof window.ajaxurl !== 'undefined') {
        return window.ajaxurl;
    }
    return '/wp-admin/admin-ajax.php';
}

// Test Function (WORKING)
function testDiaryAjax() {
    console.log('=== AJAX TEST START ===');
    // ... uses real session IDs: ["2732", "2729", "2726"]
}
```

## WP Bakery Configuration 🏗️

### Working Element Registration
```php
// Agenda Grid Element (WORKING)
add_action('vc_before_init', function() {
    if (function_exists('vc_map') && current_user_can('edit_posts')) {
        vc_map(array(
            'name'     => 'Agenda Grid',
            'base'     => 'agenda-grid',
            'category' => 'Content',
            'params'   => array(
                // ... working parameter structure
            ),
        ));
    }
}, 20);

// Display My Diary Element (WORKING)
vc_map(array(
    'name'     => 'Display My Diary',
    'base'     => 'display-my-diary',
    'category' => 'Content',
    'params'   => array(
        // ... working parameter structure
    ),
));
```

## Database Schema 💾

### Working Taxonomies
- **Date Taxonomy**: `date` - Conference dates (2025-10-21, 2025-10-22)
- **Track Taxonomy**: `track` - Session tracks (Energy & Sustainability, etc.)
- **Type Taxonomy**: `type` - Session types

### Working Post Types
- **Seminars**: `seminars` - Main session content
- **Custom Fields**: `session-start`, `session-end`, `session-time`

### Sample Working Data
```json
{
  "id": "2732",
  "title": "Lunch Break",
  "content": "<p><a href=\"https://www.healthcare-estates.com/registration-2025/\" target=\"_blank\" rel=\"noopener noreferrer\">BOOK YOUR EVENT PASS</a></p>",
  "time": "13:00 - 14:00",
  "date": "2025-10-22",
  "track": "allcolumns",
  "permalink": "http://127.0.0.1/plug/seminar/lunch-break-2/"
}
```

## File Structure 📂

### Working Directory Layout
```
mira-day-agenda/
├── mira-day-agenda.php (Main plugin file)
├── assets/
│   ├── js/
│   │   ├── mydiary.js (Working AJAX functionality)
│   │   ├── solar-agenda-grid.js
│   │   └── fix-unload-policy.js
│   ├── css/
│   │   └── solar-agenda-grid.css
│   └── lib/
│       ├── wp_bakery_admin.php (Working elements)
│       ├── wp_bakery_admin_simple.php (Backup)
│       └── display_functions.php (Working display)
└── Documentation/
    ├── SESSION_SUMMARY.md
    ├── AJAX_FIX_SUMMARY.md
    └── GIT_COMMIT_SUMMARY.md
```

## Testing Endpoints 🧪

### Working AJAX Test
```bash
curl "http://127.0.0.1/plug/wp-admin/admin-ajax.php" \
  -d "action=get_diary_sessions&diary_sessions=[\"2732\",\"2729\"]"
```
**Expected Response**: Clean JSON with session data

### Working Frontend Test
```javascript
// Browser Console
testDiaryAjax()
// Expected: "✓ AJAX Success! Retrieved 3 sessions"
```

## Security Configuration 🔒

### Working Nonce System
```php
// Server Side
'nonce' => wp_create_nonce('diary_nonce')

// Client Side (Optional - currently commented out)
// wp_verify_nonce($_POST['nonce'], 'diary_nonce')
```

### Safe Parameter Handling
```php
// Accepts both parameter names
$session_data = isset($_POST['diary_sessions']) ? $_POST['diary_sessions'] : $_POST['session_ids'];
$session_ids = json_decode(stripslashes($session_data), true);
```

## Error Handling 🚨

### Working Error Responses
```php
// Clean error responses
if (!is_array($session_ids) || empty($session_ids)) {
    ob_clean();
    wp_send_json_error('No valid session IDs provided');
    return;
}
```

### JavaScript Error Handling
```javascript
.catch(error => {
    console.error('AJAX error:', error);
    alert('AJAX error: ' + error.message);
});
```

## Performance Optimizations ⚡

### Working Optimizations
- Output buffer management prevents contamination
- Conditional script loading (`wp_doing_ajax()`)
- Efficient taxonomy queries with caching
- Minimal AJAX payload with essential data only

## Backup & Recovery 💾

### Git Repositories (BACKED UP)
- **Plugin**: `dominicjjohnson/mira-day-agenda` - Commit `61473e2`
- **Theme**: `dominicjjohnson/theme.miramedia-base` - Commit `9a48325`

### Configuration Files
- All working configurations documented in this file
- Test files preserved for future debugging
- Session summary with complete troubleshooting history

**Configuration Status**: ✅ FULLY DOCUMENTED AND BACKED UP
