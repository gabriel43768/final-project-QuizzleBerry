import express from "express"
import multer from "multer"
import path from "path"
import fs from "fs"
import {
  uploadDocument,
  getAnalysisStatus,
  getAnalysisResults,
  saveGeneratedContent,
} from "../controllers/aiAnalysisController.js"
import { authenticateToken } from "../middleware/auth.js"

const router = express.Router()

// Configure multer for file uploads
const storage = multer.diskStorage({
  destination: (req, file, cb) => {
    const uploadDir = path.join(process.cwd(), "uploads")
    if (!fs.existsSync(uploadDir)) {
      fs.mkdirSync(uploadDir, { recursive: true })
    }
    cb(null, uploadDir)
  },
  filename: (req, file, cb) => {
    const uniqueSuffix = Date.now() + "-" + Math.round(Math.random() * 1e9)
    cb(null, file.fieldname + "-" + uniqueSuffix + path.extname(file.originalname))
  },
})

const upload = multer({
  storage,
  limits: { fileSize: 10 * 1024 * 1024 }, // 10MB limit
  fileFilter: (req, file, cb) => {
    const allowedTypes = [".pdf", ".docx", ".txt", ".md"]
    const ext = path.extname(file.originalname).toLowerCase()
    if (allowedTypes.includes(ext)) {
      cb(null, true)
    } else {
      cb(new Error("Invalid file type. Only PDF, DOCX, TXT, and MD files are allowed."))
    }
  },
})

// Upload document for AI analysis
router.post("/upload", authenticateToken, upload.single("document"), uploadDocument)

// Get analysis status
router.get("/:id/status", authenticateToken, getAnalysisStatus)

// Get analysis results
router.get("/:id/results", authenticateToken, getAnalysisResults)

// Save generated content to user's library
router.post("/:id/save", authenticateToken, saveGeneratedContent)

export default router
