// Set up PDF.js
document.addEventListener("DOMContentLoaded", () => {
    // Check if pdfjsLib is already defined, if not, define it (this might be redundant if it's loaded correctly elsewhere)
    if (typeof pdfjsLib === "undefined") {
      pdfjsLib = window.pdfjsLib // Try to get it from the window object
      if (typeof pdfjsLib === "undefined") {
        console.error("PDF.js library not found. Ensure it is properly included in your HTML.")
        return // Exit if PDF.js is not found
      }
    }
  
    if (typeof pdfjsLib !== "undefined") {
      pdfjsLib.GlobalWorkerOptions.workerSrc = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.4.120/pdf.worker.min.js"
    }
  
    // Modal functionality
    const modal = document.getElementById("create-quiz-modal")
    const createBtn = document.getElementById("create-quiz-btn")
    const closeBtn = document.getElementById("modal-close")
    const modalBody = document.getElementById("modal-body")
    const initialOptions = document.getElementById("initial-options")
    const uploadView = document.getElementById("upload-view")
    const uploadTitle = document.getElementById("upload-title")
    const backButtons = document.querySelectorAll(".back-button")
    const optionCards = document.querySelectorAll(".option-card")
    const pdfUploadArea = document.getElementById("pdf-upload-area")
    const pdfUpload = document.getElementById("pdf-upload")
    const browsePdfBtn = document.getElementById("browse-pdf")
    const pdfPreview = document.getElementById("pdf-preview")
    const pdfPreviewContent = document.getElementById("pdf-preview-content")
    const pdfPageCount = document.getElementById("pdf-page-count")
    const analyzePdfBtn = document.getElementById("analyze-pdf")
    const aiProcessing = document.getElementById("ai-processing")
    const aiResults = document.getElementById("ai-results")
  
    let currentOption = ""
    let uploadedFile = null
  
    // Open modal
    if (createBtn) {
      createBtn.addEventListener("click", () => {
        modal.classList.add("active")
        // Reset to initial view
        showInitialOptions()
      })
    }
  
    // Close modal
    if (closeBtn) {
      closeBtn.addEventListener("click", () => {
        modal.classList.remove("active")
        resetViews()
      })
    }
  
    // Close modal when clicking outside
    if (modal) {
      modal.addEventListener("click", (e) => {
        if (e.target === modal) {
          modal.classList.remove("active")
          resetViews()
        }
      })
    }
  
    // Show initial options
    function showInitialOptions() {
      if (initialOptions && uploadView) {
        initialOptions.style.display = "block"
        uploadView.style.display = "none"
        resetViews()
      }
    }
  
    // Reset views
    function resetViews() {
      if (pdfPreview) pdfPreview.style.display = "none"
      if (analyzePdfBtn) analyzePdfBtn.style.display = "none"
      if (aiProcessing) aiProcessing.style.display = "none"
      if (aiResults) aiResults.style.display = "none"
      if (pdfPreviewContent) pdfPreviewContent.innerHTML = ""
      uploadedFile = null
    }
  
    // Handle option card clicks
    if (optionCards) {
      optionCards.forEach((card) => {
        card.addEventListener("click", () => {
          currentOption = card.getAttribute("data-option")
          initialOptions.style.display = "none"
          uploadView.style.display = "block"
  
          // Set the title based on the selected option
          const formattedOption = currentOption.replace("-", " ")
          uploadTitle.textContent = `Upload content for ${formattedOption.charAt(0).toUpperCase() + formattedOption.slice(1)}`
        })
      })
    }
  
    // Handle back buttons
    if (backButtons) {
      backButtons.forEach((button) => {
        button.addEventListener("click", showInitialOptions)
      })
    }
  
    // Handle file browse button
    if (browsePdfBtn && pdfUpload) {
      browsePdfBtn.addEventListener("click", () => {
        pdfUpload.click()
      })
    }
  
    // Handle file selection
    if (pdfUpload) {
      pdfUpload.addEventListener("change", (e) => {
        if (e.target.files.length > 0) {
          uploadedFile = e.target.files[0]
          browsePdfBtn.textContent = `Selected: ${uploadedFile.name}`
  
          // Show the analyze button
          analyzePdfBtn.style.display = "block"
  
          // Preview the PDF if it's a PDF file
          if (uploadedFile.type === "application/pdf") {
            previewPDF(uploadedFile)
          } else if (uploadedFile.type === "text/plain") {
            previewTextFile(uploadedFile)
          } else if (uploadedFile.type === "application/vnd.openxmlformats-officedocument.wordprocessingml.document") {
            // For DOCX files, just show a message
            pdfPreview.style.display = "block"
            pdfPageCount.textContent = "DOCX file"
            pdfPreviewContent.innerHTML =
              '<p>DOCX preview not available. Click "Analyze with AI" to process the document.</p>'
          }
        }
      })
    }
  
// Tab functionality
document.addEventListener("DOMContentLoaded", () => {
    const tabTriggers = document.querySelectorAll(".tab-trigger")
    const tabContents = document.querySelectorAll(".tab-content")
  
    tabTriggers.forEach((trigger) => {
      trigger.addEventListener("click", () => {
        // Remove active class from all triggers and contents
        tabTriggers.forEach((t) => t.classList.remove("active"))
        tabContents.forEach((c) => c.classList.remove("active"))
  
        // Add active class to clicked trigger and corresponding content
        trigger.classList.add("active")
        const tabId = trigger.getAttribute("data-tab")
        document.getElementById(`${tabId}-tab`).classList.add("active")
      })
    })
  
    // User menu toggle
    const userInfoToggle = document.getElementById("user-info-toggle")
    const userMenu = document.getElementById("user-menu")
  
    if (userInfoToggle && userMenu) {
      userInfoToggle.addEventListener("click", () => {
        userMenu.classList.toggle("active")
      })
  
      // Close user menu when clicking outside
      document.addEventListener("click", (e) => {
        if (!userInfoToggle.contains(e.target) && !userMenu.contains(e.target)) {
          userMenu.classList.remove("active")
        }
      })
    }
  
    // Edit profile button
    const editProfileBtn = document.getElementById("edit-profile-btn")
  
    if (editProfileBtn) {
      editProfileBtn.addEventListener("click", () => {
        // Switch to settings tab
        tabTriggers.forEach((t) => t.classList.remove("active"))
        tabContents.forEach((c) => c.classList.remove("active"))
  
        const settingsTab = document.querySelector('[data-tab="settings"]')
        settingsTab.classList.add("active")
        document.getElementById("settings-tab").classList.add("active")
  
        // Focus on first input
        document.getElementById("display-name").focus()
      })
    }
  
    // Edit profile link in user menu
    const editProfileLink = document.getElementById("edit-profile-link")
  
    if (editProfileLink && userMenu) {
      editProfileLink.addEventListener("click", (e) => {
        e.preventDefault()
  
        // Close user menu
        userMenu.classList.remove("active")
  
        // Switch to settings tab
        tabTriggers.forEach((t) => t.classList.remove("active"))
        tabContents.forEach((c) => c.classList.remove("active"))
  
        const settingsTab = document.querySelector('[data-tab="settings"]')
        settingsTab.classList.add("active")
        document.getElementById("settings-tab").classList.add("active")
  
        // Focus on first input
        document.getElementById("display-name").focus()
      })
    }
  
    // Form submission
    const accountSettingsForm = document.getElementById("account-settings-form")
  
    if (accountSettingsForm) {
      accountSettingsForm.addEventListener("submit", (e) => {
        e.preventDefault()
  
        // Get form values
        const displayName = document.getElementById("display-name").value
        const email = document.getElementById("email").value
        const avatarInitial = document.getElementById("avatar-initial").value.charAt(0).toUpperCase()
  
        // Update profile information
        document.querySelector(".profile-name").textContent = displayName
        document.querySelector(".profile-email").textContent = email
        document.querySelector(".profile-avatar").textContent = avatarInitial
  
        // Update sidebar user info
        const sidebarUserName = document.querySelector(".sidebar-footer .user-name")
        const sidebarUserAvatar = document.querySelector(".sidebar-footer .user-avatar")
  
        if (sidebarUserName) sidebarUserName.textContent = displayName
        if (sidebarUserAvatar) sidebarUserAvatar.textContent = avatarInitial
  
        // Show success message
        alert("Profile updated successfully!")
  
        // Switch back to achievements tab
        tabTriggers.forEach((t) => t.classList.remove("active"))
        tabContents.forEach((c) => c.classList.remove("active"))
  
        const achievementsTab = document.querySelector('[data-tab="achievements"]')
        achievementsTab.classList.add("active")
        document.getElementById("achievements-tab").classList.add("active")
      })
    }
  })
  
  

    // Preview PDF file
    function previewPDF(file) {
      if (!pdfjsLib) return
  
      const fileReader = new FileReader()
  
      fileReader.onload = function () {
        const typedarray = new Uint8Array(this.result)
  
        // Load the PDF
        pdfjsLib.getDocument(typedarray).promise.then((pdf) => {
          pdfPreview.style.display = "block"
          pdfPageCount.textContent = `${pdf.numPages} pages`
          pdfPreviewContent.innerHTML = ""
  
          // Only load the first 3 pages for preview
          const pagesToLoad = Math.min(pdf.numPages, 3)
  
          for (let i = 1; i <= pagesToLoad; i++) {
            pdf.getPage(i).then((page) => {
              page.getTextContent().then((textContent) => {
                const pageDiv = document.createElement("div")
                pageDiv.className = "pdf-preview-page"
  
                const pageHeader = document.createElement("h5")
                pageHeader.textContent = `Page ${i}`
                pageDiv.appendChild(pageHeader)
  
                const pageText = document.createElement("p")
                let lastY = textContent.items[0].transform[5]
                let text = ""
  
                for (let j = 0; j < textContent.items.length; j++) {
                  const item = textContent.items[j]
  
                  // Add a new line if the y position changes significantly
                  if (j > 0 && Math.abs(item.transform[5] - lastY) > 5) {
                    text += "\n"
                  }
  
                  text += item.str + " "
                  lastY = item.transform[5]
                }
  
                pageText.textContent = text
                pageDiv.appendChild(pageText)
  
                pdfPreviewContent.appendChild(pageDiv)
              })
            })
          }
  
          if (pdf.numPages > 3) {
            const morePages = document.createElement("p")
            morePages.textContent = `... and ${pdf.numPages - 3} more pages`
            morePages.style.textAlign = "center"
            morePages.style.fontStyle = "italic"
            morePages.style.color = "var(--text-light)"
            pdfPreviewContent.appendChild(morePages)
          }
        })
      }
  
      fileReader.readAsArrayBuffer(file)
    }
  
    // Preview text file
    function previewTextFile(file) {
      const fileReader = new FileReader()
  
      fileReader.onload = function () {
        pdfPreview.style.display = "block"
        pdfPageCount.textContent = "Text file"
  
        const text = this.result
        const lines = text.split("\n")
  
        // Only show the first 100 lines for preview
        const linesToShow = Math.min(lines.length, 100)
        let previewText = ""
  
        for (let i = 0; i < linesToShow; i++) {
          previewText += lines[i] + "\n"
        }
  
        if (lines.length > 100) {
          previewText += "\n... and more lines"
        }
  
        pdfPreviewContent.innerHTML = `<pre>${previewText}</pre>`
      }
  
      fileReader.readAsText(file)
    }
  
    // Handle analyze button click
    if (analyzePdfBtn) {
      analyzePdfBtn.addEventListener("click", () => {
        // Hide the upload area and preview
        pdfUploadArea.style.display = "none"
        pdfPreview.style.display = "none"
        analyzePdfBtn.style.display = "none"
  
        // Show the processing spinner
        aiProcessing.style.display = "flex"
  
        // Simulate AI processing
        setTimeout(() => {
          // Hide the processing spinner
          aiProcessing.style.display = "none"
  
          // Show the AI results
          aiResults.style.display = "block"
  
          // Update the result counts based on the file size
          const fileSize = uploadedFile.size
          const flashcardCount = Math.floor(fileSize / 10000) + 5 // Simulate card count based on file size
          const sectionCount = Math.floor(fileSize / 50000) + 2
          const questionCount = Math.floor(fileSize / 15000) + 3
  
          document.querySelector('[data-result="flashcards"] .ai-result-count').textContent =
            `${flashcardCount} cards generated`
          document.querySelector('[data-result="study-guide"] .ai-result-count').textContent =
            `${sectionCount} sections organized`
          document.querySelector('[data-result="practice-test"] .ai-result-count').textContent =
            `${questionCount} questions created`
        }, 3000) // Simulate 3 seconds of processing
      })
    }
  
    // Handle AI result card clicks
    const aiResultCards = document.querySelectorAll(".ai-result-card")
    if (aiResultCards) {
      aiResultCards.forEach((card) => {
        card.addEventListener("click", () => {
          const resultType = card.getAttribute("data-result")
          alert(
            `Opening ${resultType} generated from your document. This would show the AI-generated content in a real implementation.`,
          )
        })
      })
    }
  
    // File drag and drop functionality
    if (pdfUploadArea) {
      ;["dragenter", "dragover", "dragleave", "drop"].forEach((eventName) => {
        pdfUploadArea.addEventListener(eventName, preventDefaults, false)
      })
      ;["dragenter", "dragover"].forEach((eventName) => {
        pdfUploadArea.addEventListener(eventName, highlight, false)
      })
      ;["dragleave", "drop"].forEach((eventName) => {
        pdfUploadArea.addEventListener(eventName, unhighlight, false)
      })
  
      pdfUploadArea.addEventListener("drop", handleDrop, false)
    }
  
    function preventDefaults(e) {
      e.preventDefault()
      e.stopPropagation()
    }
  
    function highlight() {
      pdfUploadArea.style.borderColor = "var(--primary)"
      pdfUploadArea.style.backgroundColor = "rgba(151, 71, 255, 0.1)"
    }
  
    function unhighlight() {
      pdfUploadArea.style.borderColor = "var(--primary-light)"
      pdfUploadArea.style.backgroundColor = "rgba(151, 71, 255, 0.05)"
    }
  
    function handleDrop(e) {
      const dt = e.dataTransfer
      const files = dt.files
  
      if (files.length > 0) {
        uploadedFile = files[0]
        pdfUpload.files = files
        browsePdfBtn.textContent = `Selected: ${uploadedFile.name}`
  
        // Show the analyze button
        analyzePdfBtn.style.display = "block"
  
        // Preview the file
        if (uploadedFile.type === "application/pdf") {
          previewPDF(uploadedFile)
        } else if (uploadedFile.type === "text/plain") {
          previewTextFile(uploadedFile)
        } else if (uploadedFile.type === "application/vnd.openxmlformats-officedocument.wordprocessingml.document") {
          // For DOCX files, just show a message
          pdfPreview.style.display = "block"
          pdfPageCount.textContent = "DOCX file"
          pdfPreviewContent.innerHTML =
            '<p>DOCX preview not available. Click "Analyze with AI" to process the document.</p>'
        }
      }
    }
  })
  
  