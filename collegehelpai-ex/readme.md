
# quizSlover  AI - Chrome Extension developement 

## 1. Existing Backend
- **WordPress**: [collegehelpai.com](https://collegehelpai.com/)  
- **Custom Plugin** (not yet created)  : get answers from the API , store user data, and manage sessions, etc.
- API :
   - /quizSlover-help-ai/v1/fetch-answer
   -/quizSlover-help-ai/v1/user-data


## 2. Extension Creation

- The extension supports the following languages: **EN, FR, IT, ES, AR, RU**  
- manage sessions user data, settings, and manage sessions, etc. 
- build project i want minify to secure my code 
- support to other browsers like Firefox and Edge.
- for mathjax i have lib in my file tex-mml-chtml.js
- for all parametres i need its in one file to manage it easyly


### Options Popup
There are two user cases: connected or not.  

#### Connected User
- **Extension Logo**: displayed in the header  
- **Name**: displayed with a link to the profile  
- **Plan**: displayed; if no plan is available, show "Free"  
- **Credits**: displayed; if `credit == -1`, show "Unlimited"  
- **Settings Icon**: in the header open small popup with settings
- **Website Icon**: to navigate to the website  
- **"Take Screenshot" Button**  
- **Extension Version**: displayed in the footer  

#### Anonymous User
- **Extension Logo**: displayed in the header  
- **"Login" Button**: opens the login page URL  
- **"Register" Button**: opens the registration page URL  
- **Website Icon**: to navigate to the website  
- **Extension Version**: displayed in the footer  

## 3. Technical Improvements

### User Session Management
- Use **Chrome Storage API** to store session data locally (prevents session loss after browser restart).  
- use website session for secure login with WordPress .

### Keyboard Shortcuts Optimization
- Allow users to customize shortcuts via **`chrome.commands API`**.  
- Add **visual confirmation** after a shortcut is executed (e.g., toast notification).  

## 4. Background Features
- **User Session Management**:  
  - Check user session, and validation.
  - Retrieve user data  
  - Handle logout  
  - Store data locally  
  - Verify token expiration  
- **API**: fetch answers  
- **Shortcut Commands**:  
  - **"Ask quizSlover "** (`CTRL + SHIFT + A`): displayed only if the user selects text  
  - **"Take Screenshot"** (`CTRL + SHIFT + S`): captures a screenshot of the question  
  - **Context Menu Option** **"Ask quizSlover "**:  
    - When the user right-clicks on selected text  
    - When right-clicking on an image (PNG/JPG only)  

## 5. content.js
- **Answer Display Block**:  
    - in header  have a bar to move block to other position , and keep in storage last position , defalt value of position is right center
    - close botton
  - Shows the asked question  
  - Displays the answer in **`answer-content`**  
  - Shows the response language  
  - Includes "Retry" and "Copy" buttons  
- **`answer-content`** uses **MathJax** to properly format mathematical expressions  
if Camouflage =  true the block show as transparant block when small opacity , when hover in this block maje it more visibele

## 6. Settings Page
- **Camouflage Mode Option**  
- **Option to display questions oe screenshot in a box**  
- **Response language**: matches the question's language (FR, EN, ES, IT, AR)  , by defaut the  lang of browser
- **Ability to customize keyboard shortcuts** via **`chrome.commands`**  , show th existing shortcuts
- **Login settings**:  
  - Quick logout option  
  - go to profil (open website/profil)


## 7. Take Screenshot
When the user clicks on **"Take Screenshot"**, an overlay appears in the browser allowing them to select the area they want to capture.  
- The overlay has a **black background with 0.3 opacity** and is positioned at the **top z-index**.  
- The user clicks and holds the mouse button, then moves the cursor to define the selection area.  
- Once the mouse button is released, a function is triggered to capture the selected zone and send it for processing.  


## 8. project structure
- **`manifest.json`**: contains the extension's metadata
- **`background.js`**: manages the extension's background processes
- **`content.js`**: handles the content script
- **`popup.html`**: the extension's popup page , content popup settings
- **`lib`**:  contains the MathJax library and other dependencies
- **`assets`**:  contains the extension's images and icons
- **`_locales`**: contains the extension's translations

** Structure **
```
quizSloverAI/
│
├── README.md
├── manifest.json             # Extension configuration/metadata
├── background.js            # Background service worker
├── popup.html              # Main popup interface
│
├── lib/                    # Libraries and core scripts
│   ├── tex-mml-chtml.js    # MathJax library
│   ├── content.js          # Content script for webpage interaction
│   ├── content.css         # Content styling
│   ├── popup.css          # Popup interface styling
│   └── popup.js           # Popup functionality
│
├── assets/                 # Static resources
│   ├── icons/             # Extension icons
│   │   ├── icon-16.png
│   │   ├── icon-48.png
│   │   └── icon-128.png
│   └── images/            # Other images used in the extension
│
└── _locales/              # Internationalization
    ├── en/               # English
    ├── fr/               # French
    ├── it/               # Italian
    ├── es/               # Spanish
    ├── ar/               # Arabic
    └── ru/               # Russian
        └── messages.json  # Translation files in each language folder
```

 


project colors :

#69b8db
#4c5e87
#FFFFFF