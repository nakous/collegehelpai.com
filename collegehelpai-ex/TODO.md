# TODO - Future Improvements

## Version 1.3.0 - Planned Features

### ✅ COMPLETED (Dec 3, 2025)
- [x] **Screenshot Floating Button** - Bouton flottant de capture d'écran sur toutes les pages
  - [x] Bouton circulaire avec icône SVG en bas-droite
  - [x] Toggle ON/OFF dans popup settings
  - [x] CSS encapsulé avec style #4c5e87 + transparence
  - [x] Hover effect avec scale + opacité
  - [x] Setting persistant dans Chrome Storage
  - [x] Broadcast aux content scripts en temps réel
  - [x] Traductions i18n (EN/FR)
  - [x] Documentation complète (FEATURE_SCREENSHOT_BUTTON.md)

### 🎨 UI/UX Improvements

#### 1. Answer Box Redesign
- [ ] Modernize answer display with better typography
- [ ] Add subtle shadows and rounded corners for depth
- [ ] Improve spacing and padding for readability
- [ ] Add smooth animations when answer appears
- [ ] Better color contrast for dark/light themes
- [ ] Add copy-to-clipboard button with animation
- [ ] Syntax highlighting for code snippets in answers
- [ ] Collapsible sections for long answers
- [ ] Add "thumbs up/down" feedback buttons

#### 2. Loading State Enhancement
- [ ] Replace current loader with more visible spinner
- [ ] Add percentage or progress indicator
- [ ] Show "Processing your question..." text
- [ ] Pulsing animation for better visibility
- [ ] Loading skeleton screens instead of blank space
- [ ] Add timeout indicator (e.g., "This is taking longer than usual...")
- [ ] Show estimated time remaining for long requests
- [ ] Animated dots or progress bar

### 🎭 Camouflage Mode Improvements

#### 3. Camouflage Feature Check
- [ ] Verify camouflage toggle functionality works correctly
- [ ] Test camouflage mode across different websites
- [ ] Add visual indicator when camouflage is active
- [ ] Save camouflage preference to storage
- [ ] Add keyboard shortcut to toggle camouflage (e.g., Ctrl+Shift+C)
- [ ] Implement better stealth CSS (more natural appearance)
- [ ] Add "quick hide" button in answer box
- [ ] Option to auto-hide after X seconds
- [ ] Add different camouflage themes (calculator, notepad, etc.)

### ⚡ Code Optimization

#### 4. Performance Improvements
- [ ] Lazy load MathJax (tex-mml-chtml.js) only when needed
- [ ] Debounce API calls to prevent spam
- [ ] Cache frequent responses locally
- [ ] Reduce bundle size by removing unused dependencies
- [ ] Optimize image assets (compress PNG icons)
- [ ] Minify CSS files separately
- [ ] Use Web Workers for heavy computations
- [ ] Implement request queueing for multiple questions
- [ ] Add service worker caching strategy

#### 5. Code Refactoring
- [ ] Split background.js into modules (auth.js, api.js, storage.js, etc.)
- [ ] Create separate class files for User, Settings, APIService
- [ ] Use ES6 modules instead of global namespace
- [ ] Add TypeScript definitions for better type safety
- [ ] Reduce code duplication in popup.js
- [ ] Create utility functions file (utils.js)
- [ ] Implement error boundary pattern
- [ ] Add unit tests for critical functions
- [ ] Document all public methods with JSDoc

#### 6. Storage Optimization
- [ ] Implement LRU cache for storage cleanup
- [ ] Compress stored data when size exceeds threshold
- [ ] Add storage usage indicator in popup
- [ ] Periodic cleanup of old/expired data
- [ ] Use IndexedDB for large data instead of chrome.storage

### 🔒 Security & Reliability

#### 7. Enhanced Error Handling
- [ ] Add retry logic for failed API calls (with exponential backoff)
- [ ] Better error messages for users (avoid technical jargon)
- [ ] Log errors to analytics (optional, with user consent)
- [ ] Add error recovery mechanisms
- [ ] Validate all API responses before processing
- [ ] Add CSRF token validation

#### 8. Session Management
- [ ] Implement token refresh before expiration
- [ ] Add "Remember me" option
- [ ] Auto-logout after inactivity period
- [ ] Multiple device session management
- [ ] Show active sessions in settings

### 📱 New Features

#### 9. Additional Functionality
- [ ] Add history of asked questions
- [ ] Bookmarks/favorites for answers
- [ ] Export answers as PDF/Markdown
- [ ] Dark mode toggle
- [ ] Multi-language support improvements
- [ ] Voice input option
- [ ] OCR for image-based questions
- [ ] Keyboard shortcuts panel (show available shortcuts)
- [ ] Quick access panel (pin extension to sidebar)

#### 10. Analytics & Monitoring
- [ ] Add usage statistics dashboard
- [ ] Track most common errors
- [ ] Monitor API response times
- [ ] Credit usage trends visualization
- [ ] Performance metrics (load time, response time)

### 🧪 Testing & Quality

#### 11. Testing Infrastructure
- [ ] Add Jest unit tests
- [ ] Integration tests for API calls
- [ ] E2E tests with Puppeteer
- [ ] Test coverage report
- [ ] Automated browser compatibility tests
- [ ] Performance benchmarks

### 📚 Documentation

#### 12. Improve Documentation
- [ ] Add inline code comments
- [ ] Create developer setup guide
- [ ] Document API endpoints
- [ ] Add troubleshooting guide
- [ ] Create user manual with screenshots
- [ ] Add changelog tracking

---

## Priority Levels

**High Priority (v1.3.0)**
- Answer box redesign
- Loading state visibility
- Camouflage mode verification
- Code splitting and optimization

**Medium Priority (v1.4.0)**
- Advanced camouflage features
- History and bookmarks
- Storage optimization
- Enhanced error handling

**Low Priority (v1.5.0+)**
- Voice input
- OCR support
- Analytics dashboard
- Testing infrastructure

---

## Technical Debt

- [ ] Remove commented-out code
- [ ] Standardize naming conventions
- [ ] Update all dependencies to latest versions
- [ ] Remove unused CSS rules
- [ ] Consolidate duplicate styles
- [ ] Add eslint and prettier configuration
- [ ] Setup CI/CD pipeline

---

**Last Updated:** October 21, 2025
**Current Version:** 1.2.0
**Next Target:** 1.3.0 (UI/UX Focus)
