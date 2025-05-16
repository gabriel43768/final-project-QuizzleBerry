import express from "express"
import cors from "cors"
import mysql from "mysql2/promise"
import dotenv from "dotenv"
import userRoutes from "./routes/users.js"
import quizRoutes from "./routes/quizzes.js"
import studyMaterialRoutes from "./routes/studymaterials.js"
import aiAnalysisRoutes from "./routes/aianalysis.js"

dotenv.config()

const app = express()
const PORT = process.env.PORT || 5000

// Middleware
app.use(cors())
app.use(express.json({ limit: "50mb" }))
app.use(express.urlencoded({ extended: true, limit: "50mb" }))

// Database connection
const pool = mysql.createPool({
  host: process.env.DB_HOST || "localhost",
  user: process.env.DB_USER || "root",
  password: process.env.DB_PASSWORD || "",
  database: process.env.DB_NAME || "quizzleberry5",
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
})

// Make db available to routes
app.locals.db = pool

// Routes
app.use("/api/users", userRoutes)
app.use("/api/quizzes", quizRoutes)
app.use("/api/study-materials", studyMaterialRoutes)
app.use("/api/ai-analysis", aiAnalysisRoutes)

// Root route
app.get("/", (req, res) => {
  res.send("Quizzleberry API is running")
})

// Error handling middleware
app.use((err, req, res, next) => {
  console.error(err.stack)
  res.status(500).json({
    success: false,
    message: "Something went wrong!",
    error: process.env.NODE_ENV === "development" ? err.message : undefined,
  })
})

// Start server
app.listen(PORT, () => {
  console.log(`Server running on port ${PORT}`)
})

export default app
