// Document upload and AI processing functionality

document.addEventListener("DOMContentLoaded", () => {
    // Elements
    const createBtn = document.getElementById("create-quiz-btn")
    const modal = document.getElementById("create-quiz-modal")
    const closeBtn = document.getElementById("modal-close")
    const initialOptions = document.getElementById("initial-options")
    const uploadView = document.getElementById("upload-view")
    const backButtons = document.querySelectorAll(".back-button")
    const optionCards = document.querySelectorAll(".option-card")
    const pdfUpload = document.getElementById("pdf-upload")
    const browsePdfBtn = document.getElementById("browse-pdf")
    const analyzePdfBtn = document.getElementById("analyze-pdf")
    const aiProcessing = document.getElementById("ai-processing")
    const aiResults = document.getElementById("ai-results")
  
    // Check if user is logged in
    const token = localStorage.getItem("token")
  
    // Handle file selection
    if (pdfUpload) {
      pdfUpload.addEventListener("change", (e) => {
        if (e.target.files.length > 0) {
          const file = e.target.files[0]
          browsePdfBtn.textContent = `Selected: ${file.name}`
          analyzePdfBtn.style.display = "block"
        }
      })
    }
  
    // Handle analyze button click
    if (analyzePdfBtn) {
      analyzePdfBtn.addEventListener("click", () => {
        if (!token) {
          alert("Please log in to use this feature")
          return
        }
  
        if (!pdfUpload.files || pdfUpload.files.length === 0) {
          alert("Please select a file first")
          return
        }
  
        const file = pdfUpload.files[0]
        const formData = new FormData()
        formData.append("document", file)
  
        // Show processing spinner
        aiProcessing.style.display = "flex"
        analyzePdfBtn.style.display = "none"
  
        // Send file to backend for AI processing
        fetch("/api/ai/process-document", {
          method: "POST",
          headers: {
            Authorization: `Bearer ${token}`,
          },
          body: formData,
        })
          .then((response) => {
            if (!response.ok) {
              throw new Error("Error processing document")
            }
            return response.json()
          })
          .then((data) => {
            // Hide processing spinner
            aiProcessing.style.display = "none"
  
            // Show results
            aiResults.style.display = "block"
  
            // Update result counts
            if (data.results) {
              const flashcardCount = data.results.flashcards.count
              const sectionCount = data.results.studyGuide.count
              const questionCount = data.results.practiceTest.count
  
              document.querySelector('[data-result="flashcards"] .ai-result-count').textContent =
                `${flashcardCount} cards generated`
              document.querySelector('[data-result="study-guide"] .ai-result-count').textContent =
                `${sectionCount} sections organized`
              document.querySelector('[data-result="practice-test"] .ai-result-count').textContent =
                `${questionCount} questions created`
  
              // Store the IDs for later use
              localStorage.setItem("lastFlashcardSetId", data.results.flashcards.id)
              localStorage.setItem("lastStudyGuideId", data.results.studyGuide.id)
              localStorage.setItem("lastQuizId", data.results.practiceTest.id)
              localStorage.setItem("lastSummaryId", data.results.summary.id)
            }
  
            // Log activity
            logActivity("created_quiz", data.results.flashcards.id)
          })
          .catch((error) => {
            console.error("Error:", error)
            aiProcessing.style.display = "none"
            alert("Error processing document. Please try again.")
          })
      })
    }
  
    // Handle AI result card clicks
    const aiResultCards = document.querySelectorAll(".ai-result-card")
    if (aiResultCards) {
      aiResultCards.forEach((card) => {
        card.addEventListener("click", () => {
          const resultType = card.getAttribute("data-result")
          let id
  
          switch (resultType) {
            case "flashcards":
              id = localStorage.getItem("lastFlashcardSetId")
              window.location.href = `/flashcards.html?id=${id}`
              break
            case "study-guide":
              id = localStorage.getItem("lastStudyGuideId")
              window.location.href = `/study-guide.html?id=${id}`
              break
            case "practice-test":
              id = localStorage.getItem("lastQuizId")
              window.location.href = `/quiz.html?id=${id}`
              break
            case "summary":
              id = localStorage.getItem("lastSummaryId")
              window.location.href = `/summary.html?id=${id}`
              break
          }
        })
      })
    }
  
    // Log activity function
    function logActivity(activityType, quizId) {
      if (!token) return
  
      fetch("/api/activities", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
          activity_type: activityType,
          quiz_set_id: quizId,
        }),
      }).catch((error) => console.error("Error logging activity:", error))
    }
  })
  