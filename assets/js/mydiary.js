/**
 * MyDiary functionality for Mira Day Agenda
 * Handles adding/removing seminars to user's personal diary using cookies
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Cookie management functions
    function setCookie(name, value, days) {
        let expires = "";
        if (days) {
            let date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    }

    function getCookie(name) {
        let nameEQ = name + "=";
        let ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    // Get current diary items from cookie
    function getDiaryItems() {
        let diary = getCookie('AddToDiary');
        console.log('getDiaryItems - Raw cookie value:', diary);
        if (diary) {
            try {
                const parsed = JSON.parse(diary);
                console.log('getDiaryItems - Parsed cookie:', parsed);
                return parsed;
            } catch (e) {
                console.warn('Invalid diary cookie format, resetting. Error:', e);
                console.warn('Problematic cookie value:', diary);
                return [];
            }
        }
        console.log('getDiaryItems - No cookie found, returning empty array');
        return [];
    }

    // Save diary items to cookie
    function saveDiaryItems(items) {
        setCookie('AddToDiary', JSON.stringify(items), 30); // 30 days expiry
    }

    // Add seminar to diary
    function addToDiary(seminarId) {
        let diaryItems = getDiaryItems();
        if (!diaryItems.includes(seminarId)) {
            diaryItems.push(seminarId);
            saveDiaryItems(diaryItems);
        }
        updateButtonState(seminarId, true);
    }

    // Remove seminar from diary
    function removeFromDiary(seminarId) {
        let diaryItems = getDiaryItems();
        let index = diaryItems.indexOf(seminarId);
        if (index > -1) {
            diaryItems.splice(index, 1);
            saveDiaryItems(diaryItems);
        }
        updateButtonState(seminarId, false);
    }

    // Update button appearance and icon
    function updateButtonState(seminarId, inDiary) {
        let button = document.querySelector('.mydiary-btn[data-seminar-id="' + seminarId + '"]');
        if (button) {
            let icon = button.querySelector('.mydiary-icon');
            if (inDiary) {
                button.classList.remove('mydiary-add');
                button.classList.add('mydiary-in-diary');
                if (icon) {
                    icon.textContent = '−'; // Use minus symbol (Unicode U+2212)
                }
                button.setAttribute('title', 'Click to remove from diary');
            } else {
                button.classList.remove('mydiary-in-diary');
                button.classList.add('mydiary-add');
                if (icon) {
                    icon.textContent = '+';
                }
                button.setAttribute('title', 'Click to add to diary');
            }
        }
    }

    // Initialize all buttons based on current diary state
    function initializeButtons() {
        let diaryItems = getDiaryItems();
        let buttons = document.querySelectorAll('.mydiary-btn');
        
        buttons.forEach(function(button) {
            let seminarId = button.getAttribute('data-seminar-id');
            let inDiary = diaryItems.includes(seminarId);
            updateButtonState(seminarId, inDiary);
        });
    }

    // Handle button clicks
    function handleButtonClick(event) {
        event.preventDefault();
        event.stopPropagation();
        
        // Find the button element (could be clicked on button or its child span)
        let button = event.target;
        if (!button.classList.contains('mydiary-btn')) {
            button = button.closest('.mydiary-btn');
        }
        
        if (!button) {
            console.warn('No MyDiary button found');
            return;
        }
        
        let seminarId = button.getAttribute('data-seminar-id');
        
        if (!seminarId) {
            console.warn('No seminar ID found on button');
            return;
        }

        if (button.classList.contains('mydiary-add')) {
            addToDiary(seminarId);
        } else if (button.classList.contains('mydiary-in-diary')) {
            removeFromDiary(seminarId);
        }
    }

    // Attach event listeners to all MyDiary buttons
    function attachEventListeners() {
        let buttons = document.querySelectorAll('.mydiary-btn');
        buttons.forEach(function(button) {
            button.addEventListener('click', handleButtonClick);
        });
    }

    // Initialize the functionality
    initializeButtons();
    attachEventListeners();

    // Re-initialize if new content is loaded dynamically
    // This handles cases where the agenda grid is updated via AJAX
    let observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length > 0) {
                // Check if any new MyDiary buttons were added
                let newButtons = false;
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        if (node.classList && node.classList.contains('mydiary-btn')) {
                            newButtons = true;
                        } else if (node.querySelector && node.querySelector('.mydiary-btn')) {
                            newButtons = true;
                        }
                    }
                });
                
                if (newButtons) {
                    setTimeout(function() {
                        initializeButtons();
                        attachEventListeners();
                    }, 100);
                }
            }
        });
    });

    // Start observing changes to the document
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    // Debug function to log current diary contents
    window.debugMyDiary = function() {
        console.log('Current diary items:', getDiaryItems());
    };

    // Global refresh function for the refresh button
    window.refreshMyDiary = function() {
        console.log('Manual diary refresh triggered');
        const diaryContainers = document.querySelectorAll('.my-diary-container');
        if (diaryContainers.length > 0 && typeof window.populateMyDiary === 'function') {
            if (typeof myDiaryConfig !== 'undefined') {
                window.populateMyDiary(myDiaryConfig);
            } else {
                window.populateMyDiary({
                    style: 'grid',
                    showDetails: true,
                    showRemoveButtons: true,
                    showEmptyMessage: true
                });
            }
        }
    };

    // Add tab/visibility change detection for better UX
    function addTabFocusDetection() {
        // Window focus event
        window.addEventListener('focus', function() {
            console.log('Window gained focus - checking for diary displays');
            const diaryContainers = document.querySelectorAll('.my-diary-container');
            if (diaryContainers.length > 0 && typeof window.populateMyDiary === 'function') {
                setTimeout(function() {
                    // Trigger refresh if we have a global config
                    if (typeof myDiaryConfig !== 'undefined') {
                        window.populateMyDiary(myDiaryConfig);
                    } else {
                        // Use default config
                        window.populateMyDiary({
                            style: 'grid',
                            showDetails: true,
                            showRemoveButtons: true,
                            showEmptyMessage: true
                        });
                    }
                }, 200);
            }
        });

        // Page visibility change event
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                console.log('Tab became visible - checking for diary displays');
                const diaryContainers = document.querySelectorAll('.my-diary-container');
                if (diaryContainers.length > 0 && typeof window.populateMyDiary === 'function') {
                    setTimeout(function() {
                        if (typeof myDiaryConfig !== 'undefined') {
                            window.populateMyDiary(myDiaryConfig);
                        } else {
                            window.populateMyDiary({
                                style: 'grid',
                                showDetails: true,
                                showRemoveButtons: true,
                                showEmptyMessage: true
                            });
                        }
                    }, 300);
                }
            }
        });

        // Intersection Observer for better detection when diary comes into view
        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting && entry.intersectionRatio > 0.5) {
                        console.log('Diary container came into view - triggering refresh');
                        if (typeof window.populateMyDiary === 'function') {
                            setTimeout(function() {
                                if (typeof myDiaryConfig !== 'undefined') {
                                    window.populateMyDiary(myDiaryConfig);
                                } else {
                                    window.populateMyDiary({
                                        style: 'grid',
                                        showDetails: true,
                                        showRemoveButtons: true,
                                        showEmptyMessage: true
                                    });
                                }
                            }, 100);
                        }
                    }
                });
            }, {
                threshold: [0.5]
            });

            // Observe all diary containers
            setTimeout(function() {
                const diaryContainers = document.querySelectorAll('.my-diary-container');
                diaryContainers.forEach(function(container) {
                    observer.observe(container);
                });
            }, 500);
        }
    }

    // Initialize tab focus detection
    addTabFocusDetection();

    // Global function to populate diary display
    window.populateMyDiary = function(options) {
        const defaultOptions = {
            style: 'grid',
            showDetails: true,
            showRemoveButtons: true,
            showEmptyMessage: true
        };
        
        const config = Object.assign({}, defaultOptions, options);
        const diaryItems = getDiaryItems();
        const container = document.querySelector('.my-diary-content');
        const emptyDiv = document.querySelector('.my-diary-empty');
        
        console.log('PopulateMyDiary called with:', { 
            config, 
            diaryItems, 
            diaryItemsLength: diaryItems.length,
            container: !!container, 
            emptyDiv: !!emptyDiv,
            ajaxurl: typeof ajaxurl !== 'undefined' ? ajaxurl : 'undefined'
        });
        
        // Debug: Show current cookies
        console.log('All cookies:', document.cookie);
        
        if (!container) {
            console.warn('My Diary container (.my-diary-content) not found');
            return;
        }

        if (diaryItems.length === 0) {
            console.log('No diary items found, showing empty message');
            // Show empty message, hide content
            container.style.display = 'none';
            container.innerHTML = '';
            
            if (emptyDiv && config.showEmptyMessage) {
                emptyDiv.style.display = 'block';
                console.log('Showing existing empty div');
            } else if (config.showEmptyMessage) {
                // Create empty message if it doesn't exist
                console.log('Creating new empty message');
                const emptyHtml = '<div class="my-diary-empty"><p class="empty-message">Your diary is empty. Add sessions from the agenda to see them here.</p></div>';
                container.innerHTML = emptyHtml;
                container.style.display = 'block';
            }
            return;
        }

        console.log('Found', diaryItems.length, 'diary items, fetching session data');
        
        // Hide empty message, show content
        if (emptyDiv) {
            emptyDiv.style.display = 'none';
        }
        container.style.display = 'block';

        // Show loading state
        container.innerHTML = '<div class="my-diary-loading">Loading your diary sessions...</div>';

        // Check if we have WordPress AJAX available
        if (typeof ajaxurl !== 'undefined' && ajaxurl) {
            console.log('Using WordPress AJAX to fetch session data from:', ajaxurl);
            // Fetch session data for each diary item
            fetchSessionsData(diaryItems)
                .then(sessions => {
                    console.log('Fetched sessions successfully:', sessions);
                    renderDiarySessions(sessions, container, config);
                })
                .catch(error => {
                    console.error('Error loading diary sessions:', error);
                    // Fallback to mock data or simple display
                    console.log('Falling back to simple display');
                    renderSimpleDiarySessions(diaryItems, container, config);
                });
        } else {
            // Fallback when WordPress AJAX is not available
            console.warn('WordPress AJAX not available, using fallback display');
            renderSimpleDiarySessions(diaryItems, container, config);
        }
    };

    // Fetch session data from WordPress
    function fetchSessionsData(sessionIds) {
        return new Promise((resolve, reject) => {
            console.log('fetchSessionsData called with IDs:', sessionIds);
            // Use WordPress AJAX to fetch session data
            const data = new FormData();
            data.append('action', 'get_diary_sessions');
            data.append('session_ids', JSON.stringify(sessionIds));
            data.append('nonce', getDiaryNonce());

            const fetchUrl = ajaxurl || '/wp-admin/admin-ajax.php';
            console.log('Fetching from URL:', fetchUrl);

            fetch(fetchUrl, {
                method: 'POST',
                body: data
            })
            .then(response => {
                console.log('Fetch response status:', response.status, response.statusText);
                if (!response.ok) {
                    throw new Error(`Network response was not ok: ${response.status} ${response.statusText}`);
                }
                return response.json();
            })
            .then(result => {
                console.log('AJAX response:', result);
                if (result.success) {
                    resolve(result.data);
                } else {
                    reject(new Error(result.data || 'Failed to fetch sessions'));
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                reject(error);
            });
        });
    }

    // Simple fallback display when AJAX is not available
    function renderSimpleDiarySessions(sessionIds, container, config) {
        console.log('Rendering simple diary sessions for IDs:', sessionIds);
        
        let html = '<div class="diary-day-group">';
        html += '<h3 class="diary-day-header">My Saved Sessions</h3>';
        html += '<div class="diary-day-sessions">';
        
        sessionIds.forEach((sessionId, index) => {
            html += '<div class="diary-session" data-session-id="' + sessionId + '">';
            html += '<div class="diary-session-header">';
            html += '<div>';
            html += '<span class="diary-session-time">Session ' + (index + 1) + '</span>';
            html += '</div>';
            if (config.showRemoveButtons) {
                html += '<button class="diary-remove-btn" data-session-id="' + sessionId + '" title="Remove from diary">Remove</button>';
            }
            html += '</div>';
            html += '<div class="diary-session-title">Session ID: ' + sessionId + '</div>';
            if (config.showDetails) {
                html += '<div class="diary-session-details">Session details are loading... Please refresh the page if this persists. You can view the full session details on the main agenda.</div>';
            }
            html += '</div>';
        });
        
        html += '</div></div>';
        
        container.innerHTML = html;
        
        // Attach event listeners to remove buttons
        if (config.showRemoveButtons) {
            attachRemoveButtonListeners();
        }
        
        // Show a helpful message about refreshing
        setTimeout(function() {
            const refreshButton = document.querySelector('.my-diary-refresh-btn');
            if (refreshButton) {
                refreshButton.style.animation = 'pulse 2s infinite';
                refreshButton.title = 'Click to try loading session details again';
            }
        }, 2000);
    }

    // Get nonce for AJAX requests
    function getDiaryNonce() {
        // This would typically be localized from PHP
        return document.querySelector('meta[name="diary-nonce"]')?.getAttribute('content') || '';
    }

    // Render diary sessions grouped by day
    function renderDiarySessions(sessions, container, config) {
        if (!sessions || sessions.length === 0) {
            container.innerHTML = '<div class="my-diary-empty"><p>No valid sessions found in your diary.</p></div>';
            return;
        }

        // Group sessions by date
        const sessionsByDate = groupSessionsByDate(sessions);
        
        // Sort dates
        const sortedDates = Object.keys(sessionsByDate).sort();
        
        let html = '';
        let dayCounter = 1;
        
        sortedDates.forEach(date => {
            const dateInfo = sessionsByDate[date];
            const dateSessions = dateInfo.sessions;
            const dayTitle = dateInfo.title; // Use day title from taxonomy
            
            // Use taxonomy title if available, otherwise format the date
            let displayTitle;
            if (dayTitle && dayTitle.trim() !== '') {
                displayTitle = dayTitle;
            } else {
                const dateObj = new Date(date + 'T00:00:00');
                displayTitle = formatDate(dateObj);
            }
            
            // Sort sessions by time
            dateSessions.sort((a, b) => {
                const timeA = extractStartTime(a.time);
                const timeB = extractStartTime(b.time);
                return timeA.localeCompare(timeB);
            });
            
            html += '<div class="diary-day-group">';
            html += `<h3 class="diary-day-header">${displayTitle}</h3>`;
            html += '<div class="diary-day-sessions">';
            
            dateSessions.forEach(session => {
                html += renderSessionCard(session, config);
            });
            
            html += '</div></div>';
            dayCounter++;
        });
        
        container.innerHTML = html;
        
        // Attach event listeners to remove buttons
        if (config.showRemoveButtons) {
            attachRemoveButtonListeners();
        }
        
        // Attach modal event listeners
        attachModalEventListeners();
    }

    // Group sessions by date
    function groupSessionsByDate(sessions) {
        const grouped = {};
        
        sessions.forEach(session => {
            const date = session.date || extractDateFromSession(session);
            const dateTitle = session.date_title || ''; // Get the day title from taxonomy
            if (!grouped[date]) {
                grouped[date] = {
                    sessions: [],
                    title: dateTitle // Store the day title from taxonomy
                };
            }
            grouped[date].sessions.push(session);
        });
        
        return grouped;
    }

    // Extract date from session data
    function extractDateFromSession(session) {
        // Try to extract date from various possible fields
        if (session.session_start) {
            return session.session_start.split(' ')[0];
        }
        if (session.date) {
            return session.date;
        }
        // Default to today
        return new Date().toISOString().split('T')[0];
    }

    // Format date for display
    function formatDate(date) {
        const options = { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        };
        return date.toLocaleDateString('en-US', options);
    }

    // Extract start time from time range
    function extractStartTime(timeRange) {
        if (!timeRange) return '00:00';
        return timeRange.split(' - ')[0] || timeRange;
    }

    // Render individual session card
    function renderSessionCard(session, config) {
        const showDetails = config.showDetails;
        const showRemoveButtons = config.showRemoveButtons;
        const modalId = 'diary-modal-' + session.id;
        
        console.log('Rendering session card:', {
            sessionId: session.id,
            title: session.title,
            showDetails: showDetails,
            hasContent: !!session.content,
            contentLength: session.content ? session.content.length : 0
        });
        
        let html = `<div class="diary-session" data-session-id="${session.id}">`;
        
        // Session header with track info only (time is displayed in title)
        html += '<div class="diary-session-header">';
        html += '<div>';
        if (session.track) {
            html += `<span class="diary-session-track">${session.track}</span>`;
        }
        html += '</div>';
        
        if (showRemoveButtons) {
            html += `<button class="diary-remove-btn" data-session-id="${session.id}" title="Remove from diary">Remove</button>`;
        }
        html += '</div>';
        
        // Session title with time prefix
        let titleWithTime = session.title || 'Untitled Session';
        if (session.time) {
            titleWithTime = `${session.time} - ${titleWithTime}`;
        }
        html += `<div class="diary-session-title">${titleWithTime}</div>`;
        
        // Session details
        if (showDetails && session.content) {
            const fullContent = stripHtmlTags(session.content);
            const maxLength = 150;
            
            console.log('Session content processing:', {
                originalLength: session.content.length,
                strippedLength: fullContent.length,
                willTruncate: fullContent.length > maxLength
            });
            
            if (fullContent.length > maxLength) {
                // Content is long, show truncated with "more..." link
                const shortContent = fullContent.substring(0, maxLength) + '...';
                html += `<div class="diary-session-details">`;
                html += shortContent;
                html += ` <i class="fas fa-info-circle" aria-hidden="true"></i> `;
                html += `<a href="#" class="more-details-link" data-modal="${modalId}">more....</a>`;
                html += `</div>`;
                
                // Add modal for full content
                html += createSessionModal(modalId, session);
            } else {
                // Content is short, show it all
                html += `<div class="diary-session-details">${fullContent}</div>`;
            }
        } else if (showDetails && !session.content) {
            console.warn('Session has no content to display:', session.id);
            html += `<div class="diary-session-details">No details available for this session.</div>`;
        } else {
            console.log('Details hidden due to config.showDetails =', showDetails);
        }
        
        // Session speakers
        if (showDetails && session.speakers) {
            html += `<div class="diary-session-speakers">Speakers: ${session.speakers}</div>`;
        }
        
        html += '</div>';
        console.log('Generated HTML for session', session.id, ':', html.substring(0, 200) + '...');
        return html;
    }

    // Create modal HTML for session details
    function createSessionModal(modalId, session) {
        let modalHtml = `
        <div id="${modalId}" class="modal diary-modal" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.5);">
            <div class="modal-content" style="position:relative;background-color:#fefefe;margin:5% auto;padding:0;border:1px solid #888;width:80%;max-width:700px;border-radius:8px;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
                <span class="close-modal" data-modal="${modalId}" style="position:absolute;top:10px;right:15px;font-size:1.5em;cursor:pointer;z-index:10000;">&times;</span>
                <div class="modal-body" style="margin:20px;padding-right:30px;">
                    <div class="modal-session-title" style="font-size:1.2em;font-weight:bold;margin-bottom:10px;color:#333;">
                        ${session.title || 'Session Details'}
                    </div>
                    <div class="modal-session-time" style="color:#666;margin-bottom:15px;">
                        ${session.time ? `<strong>Time:</strong> ${session.time}` : ''}
                        ${session.track ? ` | <strong>Track:</strong> ${session.track}` : ''}
                    </div>
                    <div class="modal-session-content" style="line-height:1.6;color:#333;">
                        ${session.content || 'No additional details available.'}
                    </div>
                    ${session.speakers ? `<div class="modal-session-speakers" style="margin-top:15px;padding-top:15px;border-top:1px solid #eee;"><strong>Speakers:</strong> ${session.speakers}</div>` : ''}
                </div>
            </div>
        </div>`;
        
        return modalHtml;
    }

    // Strip HTML tags from content
    function stripHtmlTags(str) {
        if (!str) return '';
        return str.replace(/<[^>]*>/g, '');
    }

    // Truncate text to specified length
    function truncateText(text, maxLength) {
        if (!text || text.length <= maxLength) return text;
        return text.substring(0, maxLength) + '...';
    }

    // Attach event listeners to remove buttons
    function attachRemoveButtonListeners() {
        const removeButtons = document.querySelectorAll('.diary-remove-btn');
        removeButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const sessionId = this.getAttribute('data-session-id');
                if (sessionId) {
                    removeFromDiary(sessionId);
                    // Refresh the diary display
                    setTimeout(() => {
                        if (window.populateMyDiary) {
                            // Get current config from the container
                            const container = document.querySelector('.my-diary-container');
                            const style = container ? container.className.match(/my-diary-(\w+)/)?.[1] || 'grid' : 'grid';
                            window.populateMyDiary({
                                style: style,
                                showDetails: true,
                                showRemoveButtons: true,
                                showEmptyMessage: true
                            });
                        }
                    }, 100);
                }
            });
        });
    }

    // Attach modal event listeners
    function attachModalEventListeners() {
        // More details links
        const moreLinks = document.querySelectorAll('.more-details-link');
        moreLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const modalId = this.getAttribute('data-modal');
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'block';
                    // Prevent body scroll when modal is open
                    document.body.style.overflow = 'hidden';
                }
            });
        });

        // Close modal buttons
        const closeButtons = document.querySelectorAll('.close-modal');
        closeButtons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const modalId = this.getAttribute('data-modal');
                const modal = document.getElementById(modalId);
                if (modal) {
                    modal.style.display = 'none';
                    // Restore body scroll
                    document.body.style.overflow = 'auto';
                }
            });
        });

        // Close modal when clicking outside of it
        const modals = document.querySelectorAll('.diary-modal');
        modals.forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });
        });

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const openModals = document.querySelectorAll('.diary-modal[style*="display: block"], .diary-modal[style*="display:block"]');
                openModals.forEach(modal => {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                });
            }
        });

        // Handle orientation change on mobile for better modal positioning
        window.addEventListener('orientationchange', function() {
            const openModals = document.querySelectorAll('.diary-modal[style*="display: block"], .diary-modal[style*="display:block"]');
            if (openModals.length > 0) {
                // Small delay to ensure proper reflow after orientation change
                setTimeout(function() {
                    // Modals will automatically reposition due to CSS centering
                    // Force a repaint to ensure proper positioning
                    openModals.forEach(modal => {
                        const content = modal.querySelector('.modal-content');
                        if (content) {
                            content.style.display = 'none';
                            content.offsetHeight; // Trigger reflow
                            content.style.display = 'block';
                        }
                    });
                }, 150);
            }
        });

        // Add touch handling for better mobile experience
        const moreDetailsLinks = document.querySelectorAll('.more-details-link');
        moreDetailsLinks.forEach(link => {
            // Prevent double-tap zoom on mobile
            link.addEventListener('touchend', function(e) {
                e.preventDefault();
                // Trigger click after preventing default
                setTimeout(() => {
                    this.click();
                }, 10);
            });
        });
    }
});
