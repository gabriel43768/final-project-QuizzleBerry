import express from "express"
import { registerUser, loginUser, getUserProfile, updateUserProfile } from "../controllers/userController.js"
import { authenticateToken } from "../middleware/auth.js"

const router = express.Router()

// Register a new user
router.post("/register", registerUser)

// Login user
router.post("/login", loginUser)

// Get user profile
router.get("/profile", authenticateToken, getUserProfile)

// Update user profile
router.put("/profile", authenticateToken, updateUserProfile)

export default router
