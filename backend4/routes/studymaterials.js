import express from "express"
import {
  getUserFlashcards,
  createFlashcard,
  updateFlashcard,
  deleteFlashcard,
  getUserStudyGuides,
  getStudyGuideById,
  createStudyGuide,
  updateStudyGuide,
  deleteStudyGuide,
} from "../controllers/studyMaterialController.js"
import { authenticateToken } from "../middleware/auth.js"

const router = express.Router()

// Get all flashcards for a user
router.get("/flashcards", authenticateToken, getUserFlashcards)

// Create a new flashcard
router.post("/flashcards", authenticateToken, createFlashcard)

// Update a flashcard
router.put("/flashcards/:id", authenticateToken, updateFlashcard)

// Delete a flashcard
router.delete("/flashcards/:id", authenticateToken, deleteFlashcard)

// Get all study guides for a user
router.get("/study-guides", authenticateToken, getUserStudyGuides)

// Get a single study guide with sections
router.get("/study-guides/:id", authenticateToken, getStudyGuideById)

// Create a new study guide
router.post("/study-guides", authenticateToken, createStudyGuide)

// Update a study guide
router.put("/study-guides/:id", authenticateToken, updateStudyGuide)

// Delete a study guide
router.delete("/study-guides/:id", authenticateToken, deleteStudyGuide)

export default router
