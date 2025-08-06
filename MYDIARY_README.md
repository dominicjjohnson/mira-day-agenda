# MyDiary Functionality - Mira Day Agenda Plugin

## Overview
The MyDiary functionality allows users to add seminars to their personal diary using browser cookies. This creates a personalized agenda experience for conference attendees.

## Features

### 🟡 Add to MyDiary Button
- **Appearance**: Yellow/orange gradient button
- **Text**: "Add to MyDiary"
- **Function**: Adds seminar ID to the "AddToDiary" cookie
- **Location**: Bottom of each seminar block

### 🔘 In Diary State
- **Appearance**: Light grey button with dark grey text
- **Text**: "In Diary"
- **Function**: Removes seminar ID from the "AddToDiary" cookie when clicked
- **Visual Feedback**: Button state changes immediately

## Technical Details

### Cookie Management
- **Cookie Name**: `AddToDiary`
- **Format**: JSON array of seminar IDs
- **Expiry**: 30 days
- **Scope**: Domain-wide

### Files Added/Modified

#### New Files:
1. `/assets/js/mydiary.js` - JavaScript functionality
2. `/assets/css/mydiary.css` - Button styling

#### Modified Files:
1. `mira-day-agenda.php` - Added asset enqueuing
2. `/assets/lib/display_functions.php` - Added button generation and HTML integration

### Button States

#### Add to MyDiary (Default)
```css
background: linear-gradient(135deg, #FFA500 0%, #FF8C00 100%);
color: #FFFFFF;
```

#### In Diary (Active)
```css
background: linear-gradient(135deg, #E8E8E8 0%, #D5D5D5 100%);
color: #666666;
```

## Usage

### For Users
1. Browse the agenda grid
2. Click "Add to MyDiary" on any seminar
3. Button changes to "In Diary" (grey)
4. Click "In Diary" to remove from personal diary
5. Diary persists across browser sessions for 30 days

### For Developers
The functionality is automatically included when the agenda grid shortcode is used:
```php
[agenda-grid day="2025-10-01" track1="track-1" all-tracks="all-tracks"]
```

## JavaScript API

### Debug Function
```javascript
// View current diary contents in console
debugMyDiary();
```

### Cookie Functions (Internal)
- `getDiaryItems()` - Returns array of seminar IDs
- `saveDiaryItems(items)` - Saves array to cookie
- `addToDiary(seminarId)` - Adds seminar to diary
- `removeFromDiary(seminarId)` - Removes seminar from diary

## Browser Compatibility
- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## Responsive Design
- Desktop: Full button with padding
- Tablet: Slightly smaller text
- Mobile: Compact button design

## Accessibility Features
- Focus indicators for keyboard navigation
- Semantic button elements
- Screen reader friendly titles
- High contrast states

## Testing

### Manual Testing Steps
1. Load page with agenda grid
2. Verify "Add to MyDiary" buttons appear on seminars
3. Click button - should change to "In Diary"
4. Refresh page - button should remain "In Diary"
5. Click "In Diary" - should change back to "Add to MyDiary"
6. Check browser developer tools - verify "AddToDiary" cookie

### Cookie Testing
```javascript
// Check cookie contents
document.cookie.split(';').find(c => c.trim().startsWith('AddToDiary='));

// Clear cookie for testing
document.cookie = 'AddToDiary=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
```

## Troubleshooting

### Common Issues
1. **Buttons not appearing**: Check if shortcode is loading properly
2. **State not persisting**: Verify cookies are enabled in browser
3. **Styling issues**: Check CSS file is loading correctly

### Debug Mode
When `DEVMODE` is true, assets include timestamps for cache busting.

## Version History
- **v1.13**: Initial MyDiary implementation
  - Added cookie-based diary functionality
  - Yellow/orange to grey button states
  - 30-day persistence
  - Responsive design
