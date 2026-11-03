
// content.js

// Prevent multiple injections
if (window.collegeHelpAIInjected) {
    //  College Help AI content script already injected 
} else {
    window.collegeHelpAIInjected = true;

    class CollegeHelpAIContent {

        static get CONFIG() {
            return {
                codeApp: 'CHAI25_',
            };
        }

        // Convert markdown to HTML
        markdownToHtml(markdown) {
            if (!markdown) return '';

            let html = markdown
                // Headers
                .replace(/^### (.*$)/gim, '<h3>$1</h3>')
                .replace(/^## (.*$)/gim, '<h2>$1</h2>')
                .replace(/^# (.*$)/gim, '<h1>$1</h1>')

                // Bold
                .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                .replace(/__(.*?)__/g, '<strong>$1</strong>')

                // Italic
                .replace(/\*(.*?)\*/g, '<em>$1</em>')
                .replace(/_(.*?)_/g, '<em>$1</em>')

                // Code blocks
                .replace(/```(.*?)```/gs, '<pre><code>$1</code></pre>')
                .replace(/`(.*?)`/g, '<code>$1</code>')

                // Links
                .replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank">$1</a>')

                // Lists
                .replace(/^\* (.*$)/gim, '<li>$1</li>')
                .replace(/^- (.*$)/gim, '<li>$1</li>')
                .replace(/^\+ (.*$)/gim, '<li>$1</li>')
                .replace(/^\d+\. (.*$)/gim, '<li>$1</li>')

                // Line breaks
                .replace(/\n\n/g, '</p><p>')
                .replace(/\n/g, '<br>');

            // Wrap lists in ul tags
            html = html.replace(/(<li>.*<\/li>)/gs, (match) => {
                if (!match.includes('<ul>') && !match.includes('<ol>')) {
                    return '<ul>' + match + '</ul>';
                }
                return match;
            });

            // Wrap content in paragraphs if it doesn't start with a block element
            if (!html.match(/^<(h[1-6]|div|p|ul|ol|pre|blockquote)/)) {
                html = '<p>' + html + '</p>';
            }

            return html;
        }
        constructor() {
            this.answerBlock = null;
            this.screenshotButton = null;
            this.isDragging = false;
            this.position = { x: 0, y: 0 };
            this.offset = { x: 0, y: 0 };
            this.settings = null;

            this.initialize();
        }

        async initialize() {
            // Load settings
            this.settings = await this.loadSettings();
            // Create screenshot button based on settings
            this.initializeScreenshotButton();

            // Listen for messages from background script
            chrome.runtime.onMessage.addListener(this.handleMessages.bind(this));
        }

        async loadSettings() {
            try {
                const response = await chrome.runtime.sendMessage({
                    action: CollegeHelpAIContent.CONFIG.codeApp + 'getSettings',
                });

                if (response && response.success) {
                    return response.data;
                }

                // Default settings if request fails
                return {
                    position: { right: '20px', top: '50%', transform: 'translateY(-50%)' },
                    camouflageMode: false,
                    language: chrome.i18n.getUILanguage(),
                    displayQuestion: false,
                    screenshotButtonEnabled: true,

                };
            } catch (error) {
                console.error('Error loading settings:', error);
                // Return defaults on error
                return {
                    position: { right: '20px', top: '50%', transform: 'translateY(-50%)' },
                    camouflageMode: false,
                    language: chrome.i18n.getUILanguage(),
                    displayQuestion: false,
                    screenshotButtonEnabled: true,
                };
            }
        }

        handleMessages(message, sender, sendResponse) {
            switch (message.type) {
                case 'ping': 
                    sendResponse({ success: true });
                    return true; // Keep message channel open for async response
                
                case 'updateSettings':
                    this.settings = message.settings;
                    this.updateScreenshotButtonVisibility();
                    sendResponse({ success: true });
                    return true;
                case 'displayAnswer':
                    this.showAnswer(message.data);
                    break;
                case 'initiateScreenshot':
                    this.initiateScreenshot();
                    break;
                // case 'showLoginToast':
                // case 'showLoginPrompt':
                //     this.showLoginPrompt();
                //     break;
                case 'showToast':
                    this.showToast(message.text, message.status, message.duration);
                    break;
                case 'hideAnswer':
                    this.hideAnswer();
                    break;
                case 'showLoading':
                    this.showLoading();
                    break;
                case 'hideLoading':
                    this.hideLoading();
                    break;
                case 'updateSettings':
                    this.updateSettings(message.settings);
                    break;
            }
        }

        createAnswerBlock() {
            const block = document.createElement('div');
            block.className = 'collegehelp-answer-block';

            if (this.settings && this.settings.camouflageMode) {
                block.classList.add('camouflage');
            }

            // Set position
            if (this.settings && this.settings.position) {
                if (this.settings.position.left !== undefined && this.settings.position.top !== undefined) {
                    block.style.left = `${this.settings.position.left}px`;
                    block.style.top = `${this.settings.position.top}px`;
                } else {
                    Object.assign(block.style, this.settings.position);
                }
            } else {
                // Default position if settings not loaded
                block.style.right = '20px';
                block.style.top = '50%';
                block.style.transform = 'translateY(-50%)';
            }

            // Attach shadow DOM to the block
            const shadow = block.attachShadow({ mode: 'open' });

            // Add styles to shadow DOM
            const style = document.createElement('style');
            style.textContent = `
           
.collegehelp-header {
    background: #4c5e87;
    color: white;
    border-radius: 8px 8px 0 0;
    cursor: move;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 2px 10px;
    font-size: 12px;
    font-family: sans-serif;
}

.collegehelp-close {
    background: none;
    border: none;
    color: white;
    cursor: pointer;
    padding: 4px;
    font-size: 18px;
    line-height: 1;
}

.collegehelp-content {
    padding: 10px;
    max-height: 400px;
    overflow-y: auto;
}

.collegehelp-question {
    font-size: 14px;
    color: #666;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #eee;
}

.collegehelp-answer {
    font-size: 14px;
    line-height: 1.5;
    color: #333;
    max-height: 150px;
}

.collegehelp-answer h1, .collegehelp-answer h2, .collegehelp-answer h3 {
    margin: 10px 0 5px 0;
    color: #4c5e87;
}

.collegehelp-answer h1 { font-size: 18px; }
.collegehelp-answer h2 { font-size: 16px; }
.collegehelp-answer h3 { font-size: 14px; }

.collegehelp-answer p {
    margin: 8px 0;
}

.collegehelp-answer ul, .collegehelp-answer ol {
    margin: 8px 0;
    padding-left: 20px;
}

.collegehelp-answer li {
    margin: 4px 0;
}

.collegehelp-answer code {
    background-color: #f5f5f5;
    padding: 2px 4px;
    border-radius: 3px;
    font-family: monospace;
    font-size: 13px;
}

.collegehelp-answer pre {
    background-color: #f5f5f5;
    padding: 10px;
    border-radius: 5px;
    overflow-x: auto;
    margin: 8px 0;
}

.collegehelp-answer pre code {
    background: none;
    padding: 0;
}

.collegehelp-answer strong {
    font-weight: bold;
}

.collegehelp-answer em {
    font-style: italic;
}

.collegehelp-answer a {
    color: #4c5e87;
    text-decoration: underline;
}

.collegehelp-answer a:hover {
    color: #69b8db;
}

.collegehelp-error {
    font-size: 12px;
    color: #d9534f;
    margin-top: 2px;
    word-wrap: break-word;
}
.collegehelp-footer {
        padding: 5px 10px;
    border-top: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.collegehelp-language {
    font-size: 12px;
    color: #666;
}

.collegehelp-actions {
    display: flex;
    gap: 8px;
}

.collegehelp-button {
        padding: 3px 5px;
    border-radius: 4px;
    border: 1px solid #4c5e87;
    background: none;
    color: #4c5e87;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.2s ease;
}

.collegehelp-button:hover {
    background: #4c5e87;
    color: white;
}
        `;

            // Create header
            const header = document.createElement('div');
            header.className = 'collegehelp-header';
            const logo = chrome.runtime.getURL('assets/logo.png');

            header.innerHTML = `
                    <span><img src="${logo}" alt="" style="width: 20px; vertical-align: middle; margin-right: 5px;">${chrome.i18n.getMessage('shortname')}</span>
                    <button class="collegehelp-close">&times;</button>
                `;

            // Create content
            const content = document.createElement('div');
            content.className = 'collegehelp-content';

            // Create footer
            const footer = document.createElement('div');
            footer.className = 'collegehelp-footer';

            // Append elements to shadow DOM
            shadow.appendChild(style);
            shadow.appendChild(header);
            shadow.appendChild(content);
            shadow.appendChild(footer);

            // Add event listeners
            this.initializeDragAndDrop(header, block);

            // We need to use shadow root to query elements
            shadow.querySelector('.collegehelp-close').addEventListener('click', () => this.hideAnswer());

            return block;
        }

        showAnswer(data) {
            this.hideLoading();
            if (!this.answerBlock) {
                this.answerBlock = this.createAnswerBlock();
                document.body.appendChild(this.answerBlock);
            }
            // Access the shadow DOM
            const shadowRoot = this.answerBlock.shadowRoot;
            const content = shadowRoot.querySelector('.collegehelp-content');
            const footer = shadowRoot.querySelector('.collegehelp-footer');

            //  check if seccess 
            if (!data.success) {
                content.innerHTML = `<div class="collegehelp-error">${data.error}</div>`;
            } else {
                // Convert markdown to HTML for the answer
                const convertedAnswer = this.markdownToHtml(data.answer);

                // Display question and answer
                content.innerHTML = `
                ${this.settings.displayQuestion ? `<div class="collegehelp-question">${data.question}</div>` : ''}
                <div class="collegehelp-answer">${convertedAnswer}</div>
            `;
            }

            // Update footer
            footer.innerHTML = `
            <!--span class="collegehelp-language">${this.settings.language.toUpperCase()}</span-->
            <div class="collegehelp-actions">
                <button class="collegehelp-button" data-action="retry">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;">
                        <path d="M21.5 2v6h-6M2.5 22v-6h6M2 11.5a10 10 0 0 1 18.8-4.3M22 12.5a10 10 0 0 1-18.8 4.2"/>
                    </svg>
                    ${chrome.i18n.getMessage('retryButton')}
                </button>
                <button class="collegehelp-button" data-action="copy">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;">
                        <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path>
                    </svg>
                    ${chrome.i18n.getMessage('copyButton')}
                </button>
            </div>
        `;

            // Add action listeners
            footer.querySelector('[data-action="retry"]').addEventListener('click', () => this.retryAnswer());
            footer.querySelector('[data-action="copy"]').addEventListener('click', () => this.copyAnswer());

            // Render math if present
            if (window.MathJax && window.MathJax.typesetPromise) {
                // Use setTimeout to ensure content is fully rendered before typesetting
                setTimeout(() => {
                    try {
                        // Get the answer element which contains the math formulas
                        const answerElement = content.querySelector('.collegehelp-answer');
                        if (answerElement) {
                            MathJax.typesetPromise([answerElement]).catch((err) => {
                                console.warn('MathJax rendering issue:', err);
                            });
                        }
                    } catch (error) {
                        console.warn('MathJax not available:', error);
                    }
                }, 100);
            }
        }

        hideAnswer() {
            if (this.answerBlock) {
                this.answerBlock.remove();
                this.answerBlock = null;
            }
        }

        initializeDragAndDrop(handle, block) {
            handle.addEventListener('mousedown', (e) => {
                if (e.target.classList.contains('collegehelp-close')) return;

                this.isDragging = true;
                this.offset.x = e.clientX - block.offsetLeft;
                this.offset.y = e.clientY - block.offsetTop;

                block.style.transition = 'none';
            });

            document.addEventListener('mousemove', (e) => {
                if (!this.isDragging) return;

                this.position.x = e.clientX - this.offset.x;
                this.position.y = e.clientY - this.offset.y;

                // Keep block within viewport
                const maxX = window.innerWidth - block.offsetWidth;
                const maxY = window.innerHeight - block.offsetHeight;

                this.position.x = Math.max(0, Math.min(this.position.x, maxX));
                this.position.y = Math.max(0, Math.min(this.position.y, maxY));

                block.style.left = `${this.position.x}px`;
                block.style.top = `${this.position.y}px`;
            });

            document.addEventListener('mouseup', () => {
                if (!this.isDragging) return;

                this.isDragging = false;
                block.style.transition = '';

                // Save position
                this.savePosition(this.position);
            });
        }

        // async savePosition(position) {
        //     await chrome.storage.local.set({ position });
        // }

        async savePosition(position) {
            try {
                this.settings.position = {
                    left: position.x,
                    top: position.y
                };
                await chrome.runtime.sendMessage({
                    action: CollegeHelpAIContent.CONFIG.codeApp + 'updateSettings',
                    settings: {
                        ...this.settings,
                    }
                });

            } catch (error) {
                console.error(chrome.i18n.getMessage('errorSavingPosition'), error);
            }
        }
        async retryAnswer() {
            try {
                this.initiateScreenshot();
            } catch (error) {
                console.error(chrome.i18n.getMessage('errorRetryingAnswer'), error);
                // toast message
                this.showToast(chrome.i18n.getMessage('errorRetryingAnswer'), 'error', 3000);
            }
        }

        copyAnswer() {
            const answerElement = this.answerBlock.shadowRoot.querySelector('.collegehelp-answer');
            const answerText = answerElement.textContent || answerElement.innerText;
            navigator.clipboard.writeText(answerText);

            // Show copy feedback
            const copyButton = this.answerBlock.shadowRoot.querySelector('[data-action="copy"]');
            const originalHTML = copyButton.innerHTML;
            copyButton.innerHTML = `
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle; margin-right: 4px;">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                Copied!
            `;
            setTimeout(() => {
                copyButton.innerHTML = originalHTML;
            }, 2000);
        }
        getZoomLevel() {
            return window.devicePixelRatio || 1;
        }

        // Clean up all screenshot-related elements
        cleanupScreenshotOverlay() {
            // Remove all screenshot overlays
            const overlays = document.querySelectorAll('.collegehelp-screenshot-overlay');
            overlays.forEach(overlay => overlay.remove());

            // Remove any stray selection boxes
            const selections = document.querySelectorAll('.collegehelp-selection-box');
            selections.forEach(selection => selection.remove());

            // Remove any instruction elements
            const instructions = document.querySelectorAll('.collegehelp-instructions');
            instructions.forEach(instruction => instruction.remove());

            // Remove any elements with instruction text (fallback)
            const instructionsByText = document.querySelectorAll('div');
            instructionsByText.forEach(element => {
                if (element.textContent && element.textContent.includes(chrome.i18n.getMessage('screenshotInstructions'))) {
                    element.remove();
                }
            });


        }

        initiateScreenshot() {
            this.hideAnswer();

            // Clean up any existing overlays first
            this.cleanupScreenshotOverlay();

            const overlay = document.createElement('div');
            overlay.className = 'collegehelp-screenshot-overlay';
            overlay.style.position = 'fixed';
            overlay.style.top = 0;
            overlay.style.left = 0;
            overlay.style.width = '100%';
            overlay.style.height = '100%';
            overlay.style.zIndex = '2147483647';
            overlay.style.isolation = 'isolate';
            overlay.style.display = 'block';
            overlay.style.cursor = 'crosshair';
            overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.5)';

            const selection = document.createElement('div');
            selection.className = 'collegehelp-selection-box';
            selection.style.position = 'absolute';
            selection.style.border = '1px dashed #4c5e87';
            selection.style.background = 'transparent';
            selection.style.pointerEvents = 'none';
            // selection.style.transition = 'all 0.2s ease';
            selection.style.boxShadow = '0 0 0 9999px rgba(0, 0, 0, 0.1)';
            selection.style.zIndex = '2147483647';
            selection.style.isolation = 'isolate';
            selection.style.display = 'none'; // Initially hidden

            // Create corner elements (4 corners)
            const createCorner = (position) => {
                const corner = document.createElement('div');
                corner.style.position = 'absolute';
                corner.style.width = '20px';
                corner.style.height = '20px';
                corner.style.borderColor = '#4c5e87';
                corner.style.borderWidth = '2px';
                corner.style.borderStyle = 'solid';

                // Position the corner and set the appropriate borders
                switch (position) {
                    case 'top-left':
                        corner.style.top = '0';
                        corner.style.left = '0';
                        corner.style.borderRight = 'none';
                        corner.style.borderBottom = 'none';
                        break;
                    case 'top-right':
                        corner.style.top = '0';
                        corner.style.right = '0';
                        corner.style.borderLeft = 'none';
                        corner.style.borderBottom = 'none';
                        break;
                    case 'bottom-left':
                        corner.style.bottom = '0';
                        corner.style.left = '0';
                        corner.style.borderRight = 'none';
                        corner.style.borderTop = 'none';
                        break;
                    case 'bottom-right':
                        corner.style.bottom = '0';
                        corner.style.right = '0';
                        corner.style.borderLeft = 'none';
                        corner.style.borderTop = 'none';
                        break;
                }

                return corner;
            };

            // Add the four corners to the selection
            selection.appendChild(createCorner('top-left'));
            selection.appendChild(createCorner('top-right'));
            selection.appendChild(createCorner('bottom-left'));
            selection.appendChild(createCorner('bottom-right'));
            document.body.appendChild(overlay);
            overlay.appendChild(selection);

            let startX, startY;
            let isSelecting = false;

            // Add escape key handler to cancel screenshot
            const handleEscape = (e) => {
                if (e.key === 'Escape') {
                    this.cleanupScreenshotOverlay();
                    document.removeEventListener('keydown', handleEscape);
                }
            };
            document.addEventListener('keydown', handleEscape);

            // Add instructions
            const instructions = document.createElement('div');
            instructions.className = 'collegehelp-instructions';
            instructions.style.position = 'fixed';
            instructions.style.top = '20px';
            instructions.style.left = '50%';
            instructions.style.transform = 'translateX(-50%)';
            instructions.style.background = 'rgba(0, 0, 0, 0.8)';
            instructions.style.color = 'white';
            instructions.style.padding = '10px 20px';
            instructions.style.borderRadius = '5px';
            instructions.style.fontSize = '14px';
            instructions.style.zIndex = '2147483647';
            instructions.style.isolation = 'isolate';
            instructions.style.fontFamily = 'Arial, sans-serif';
            instructions.textContent = chrome.i18n.getMessage('screenshotInstructions');
            overlay.appendChild(instructions);

            overlay.addEventListener('mousedown', (e) => {
                isSelecting = true;
                startX = e.clientX;
                startY = e.clientY;
                selection.style.display = 'block'; // Show the selection box
                overlay.style.backgroundColor = 'transparent';
            });

            overlay.addEventListener('mousemove', (e) => {
                if (!isSelecting) return;

                const currentX = e.clientX;
                const currentY = e.clientY;

                const width = currentX - startX;
                const height = currentY - startY;

                selection.style.width = `${Math.abs(width)}px`;
                selection.style.height = `${Math.abs(height)}px`;
                selection.style.left = `${width > 0 ? startX : currentX}px`;
                selection.style.top = `${height > 0 ? startY : currentY}px`;
            });

            overlay.addEventListener('mouseup', async (e) => {
                // Clean up escape key handler
                document.removeEventListener('keydown', handleEscape);

                if (!isSelecting) {
                    // If user just clicked without dragging, remove overlay
                    this.cleanupScreenshotOverlay();
                    return;
                }

                isSelecting = false;
                // delete overlay element
                

                // Get the selection dimensions
                const rect = selection.getBoundingClientRect();

                // Check if the selection is too small (less than 10x10 pixels)
                if (rect.width < 10 || rect.height < 10) {
                    this.cleanupScreenshotOverlay();
                    return;
                }

                // Capture the selected area
                try {
                    let data_imag = {
                        width: rect.width,
                        height: rect.height,
                        x: rect.left,
                        y: rect.top,
                        devicePixelRatio: this.getZoomLevel()
                    };

                    let request = {
                        isImage: true,
                        tool: 'base64',
                        type: 'global',
                        fromUrl: window.location.href || null,
                        croppedImage: data_imag
                    };

                    // Remove overlay IMMEDIATELY and directly before showing loading
                    // Since selection is a child of overlay, removing overlay removes everything
                    if (overlay && overlay.parentNode) {
                        overlay.parentNode.removeChild(overlay);
                    }
                    
                    // Force DOM update to ensure overlay is fully removed
                    await new Promise(resolve => setTimeout(resolve, 50));

                    // Create a screenshot
                    await this.captureScreenshot(request);

                } catch (error) {
                    console.error(chrome.i18n.getMessage('errorCapturingScreenshot'), error);
                    // Make sure overlay is removed even on error
                    this.cleanupScreenshotOverlay();
                    this.showToast('Error capturing screenshot', 'error', 3000);
                }
            });
        }

        async captureScreenshot(request) {
            this.showLoading();

            const response = await chrome.runtime.sendMessage({
                action: CollegeHelpAIContent.CONFIG.codeApp + 'captureScreenshot',
                request
            });

            if (response && response.success) {
                this.showAnswer(response);
            } else {
                this.showAnswer(response);
            }
        }

        updateSettings(newSettings) {
            this.settings = { ...this.settings, ...newSettings };

            if (this.answerBlock) {
                if (this.settings.camouflageMode) {
                    this.answerBlock.classList.add('camouflage');
                } else {
                    this.answerBlock.classList.remove('camouflage');
                }
            }
        }

        // showLoginPrompt() {
        //     // You can customize this to match your UI
        //     alert('Please log in to use collegehelp AI');
        // }

        // type: success, error, info
        showToast(message, type = "success", duration = 3000) {
            const toast = document.createElement('div');
            toast.className = 'collegehelp-toast';
            toast.classList.add(type);
            // colors adapted to the type
            toast.style.backgroundColor = '#4caf50';
            if (type === 'success') {
                toast.style.backgroundColor = '#4caf50';
            } else if (type === 'error') {
                toast.style.backgroundColor = '#f44336';
            } else if (type === 'info') {
                toast.style.backgroundColor = '#2196F3';
            }
            toast.style.zIndex = '2147483647';
            toast.style.isolation = 'isolate';
            toast.style.position = 'fixed';
            toast.style.bottom = '20px';
            toast.style.right = '20px';

            toast.style.color = 'rgb(255, 255, 255)';
            toast.style.padding = '7px 15px';
            toast.style.borderRadius = '10px';
            toast.style.fontSize = '12px';
            toast.style.boxShadow = '0 2px 10px rgba(0, 0, 0, 0.2)';
            toast.style.fontFamily = 'Arial, sans-serif';
            toast.style.fontWeight = 'bold';
            toast.style.textAlign = 'center';
            toast.style.maxWidth = '300px';
            toast.style.wordWrap = 'break-word';
            toast.style.lineHeight = '1.5';
            toast.style.pointerEvents = 'none'; // Prevent interaction with the toast

            // Add fade-in animation
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.5s ease-in-out';
            requestAnimationFrame(() => {
                toast.style.opacity = '1';
            });

            // Remove toast with fade-out animation
            if (duration !== null && duration !== undefined) {
                setTimeout(() => {
                    toast.style.opacity = '0';
                    toast.addEventListener('transitionend', () => {
                        toast.remove();
                    }, { once: true });
                }, duration);
            }

            toast.innerText = message;

            document.body.appendChild(toast);

            if (duration !== null && duration !== undefined) {
                setTimeout(() => {
                    toast.remove();
                }, duration);
            }

        }
        showLoading() {
            // Remove existing loading if present
            const existingLoading = document.querySelector('.collegehelp-loading');
            if (existingLoading) {
                existingLoading.remove();
            }

            const loading = document.createElement('div');
            loading.className = 'collegehelp-loading';
            loading.innerHTML = `
            <div style="display: flex; align-items: center; gap: 10px;">
                <svg xmlns="http://www.w3.org/2000/svg" style="flex-shrink: 0;" width="25px" height="25px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
                    <circle cx="50" cy="50" fill="none" stroke="#fff" stroke-width="10" r="35" stroke-dasharray="164.93361431346415 56.97787143782138">
                        <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" values="0 50 50;360 50 50" keyTimes="0;1"></animateTransform>
                    </circle>
                </svg>
                <span style="font-size: 13px; font-weight: 500; white-space: nowrap;">${chrome.i18n.getMessage('searchingSolution')}</span>
            </div>
        `;
            loading.style.position = 'fixed';
            loading.style.bottom = '20px';
            loading.style.left = '50%';
            loading.style.transform = 'translateX(-50%)';
            loading.style.backgroundColor = 'rgba(76, 94, 135, 0.95)';
            loading.style.color = 'rgb(255, 255, 255)';
            loading.style.padding = '12px 20px';
            loading.style.borderRadius = '25px';
            loading.style.zIndex = '2147483647';
            loading.style.isolation = 'isolate';
            loading.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.3)';
            loading.style.fontFamily = 'Arial, sans-serif';
            document.body.appendChild(loading);
        }

        hideLoading() {
            const loading = document.querySelector('.collegehelp-loading');
            if (loading) {
                loading.remove();
            }
        }

        initializeScreenshotButton() {
           
            if (this.settings && this.settings.screenshotButtonEnabled) {
                this.createScreenshotButton();
            }
        }

        createScreenshotButton() {
            if (this.screenshotButton) {
                return;
            }

            // Check if button already exists in DOM (from previous injection)
            const existingButton = document.querySelector('.collegehelp-screenshot-btn');
            if (existingButton) {
                existingButton.remove();
            }

            const button = document.createElement('div');
            button.className = 'collegehelp-screenshot-btn';
            button.title = 'Take Screenshot';
            
            const logoUrl = chrome.runtime.getURL('assets/icons/icon-16.png');
            button.innerHTML = `
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="position: relative;">
                    <circle cx="12" cy="12" r="4" fill="rgba(255, 255, 255, 0.3)"></circle>
                    <path d="M21,10a1,1,0,0,1-1-1V4H15a1,1,0,0,1,0-2h5a2,2,0,0,1,2,2V9A1,1,0,0,1,21,10Zm1,10V15a1,1,0,0,0-2,0v5H15a1,1,0,0,0,0,2h5A2,2,0,0,0,22,20ZM4,9V4H9A1,1,0,0,0,9,2H4A2,2,0,0,0,2,4V9A1,1,0,0,0,4,9Zm6,12a1,1,0,0,0-1-1H4V15a1,1,0,0,0-2,0v5a2,2,0,0,0,2,2H9A1,1,0,0,0,10,21Z" fill="white"></path>
                    <image href="${logoUrl}" x="8" y="8" width="8" height="8" />
                </svg>
            `;

            button.addEventListener('click', () => this.handleScreenshotClick());
            document.body.appendChild(button);
            this.screenshotButton = button;
     }

        removeScreenshotButton() {
            if (this.screenshotButton) {
                this.screenshotButton.remove();
                this.screenshotButton = null;
            }
        }

        updateScreenshotButtonVisibility() {
            if (this.settings && this.settings.screenshotButtonEnabled) {
                if (!this.screenshotButton) {
                    this.createScreenshotButton();
                }
            } else {
                this.removeScreenshotButton();
            }
        }

        handleScreenshotClick() {
            // Call initiateScreenshot directly to show the selection overlay
            this.initiateScreenshot();
        }
    }

    // Initialize content script
    let _jqgsgqd = new CollegeHelpAIContent();

    // _jqgsgqd.showToast('collegehelp AI is ready!', 'success', 3000);

} // End of injection prevention block