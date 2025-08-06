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
        if (diary) {
            try {
                return JSON.parse(diary);
            } catch (e) {
                console.warn('Invalid diary cookie format, resetting');
                return [];
            }
        }
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

    // Update button appearance and text
    function updateButtonState(seminarId, inDiary) {
        let button = document.querySelector('.mydiary-btn[data-seminar-id="' + seminarId + '"]');
        if (button) {
            if (inDiary) {
                button.classList.remove('mydiary-add');
                button.classList.add('mydiary-in-diary');
                button.textContent = 'In Diary';
                button.setAttribute('title', 'Click to remove from diary');
            } else {
                button.classList.remove('mydiary-in-diary');
                button.classList.add('mydiary-add');
                button.textContent = 'Add to MyDiary';
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
        
        let button = event.target;
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
});
