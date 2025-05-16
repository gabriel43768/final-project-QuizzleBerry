import express from "express"
import {
  getAllQuizzes,
  getUserQuizzes,
  getQuizById,
  createQuiz,
  updateQuiz,
  deleteQuiz,
  submitQuizAnswers,
} from "../controllers/quizController.js"
import { authenticateToken } from "../middleware/auth.js"

const router = express.Router()

// Get all quizzes (with optional category filter)
router.get("/", getAllQuizzes)

// Get user's quizzes
router.get("/my-quizzes", authenticateToken, getUserQuizzes)

// Get a single quiz with questions and answers
router.get("/:id", getQuizById)

// Create a new quiz
router.post("/", authenticateToken, createQuiz)

// Update a quiz
router.put("/:id", authenticateToken, updateQuiz)

// Delete a quiz
router.delete("/:id", authenticateToken, deleteQuiz)

// Submit quiz answers and record progress
router.post("/:id/submit", authenticateToken, submitQuizAnswers)

export default router
