import bcrypt from "bcrypt"
import jwt from "jsonwebtoken"

// Register a new user
export const registerUser = async (req, res) => {
  const { username, email, password } = req.body

  if (!username || !email || !password) {
    return res.status(400).json({ success: false, message: "Please provide all required fields" })
  }

  try {
    const db = req.app.locals.db

    // Check if user already exists
    const [existingUsers] = await db.query("SELECT * FROM users WHERE username = ? OR email = ?", [username, email])

    if (existingUsers.length > 0) {
      return res.status(409).json({ success: false, message: "Username or email already exists" })
    }

    // Hash password
    const salt = await bcrypt.genSalt(10)
    const hashedPassword = await bcrypt.hash(password, salt)

    // Insert new user
    const [result] = await db.query("INSERT INTO users (username, email, password) VALUES (?, ?, ?)", [
      username,
      email,
      hashedPassword,
    ])

    // Generate JWT token
    const token = jwt.sign({ id: result.insertId, username }, process.env.JWT_SECRET || "your_jwt_secret", {
      expiresIn: "24h",
    })

    res.status(201).json({
      success: true,
      message: "User registered successfully",
      token,
      user: {
        id: result.insertId,
        username,
        email,
      },
    })
  } catch (error) {
    console.error("Registration error:", error)
    res.status(500).json({ success: false, message: "Error registering user" })
  }
}

// Login user
export const loginUser = async (req, res) => {
  const { email, password } = req.body

  if (!email || !password) {
    return res.status(400).json({ success: false, message: "Please provide email and password" })
  }

  try {
    const db = req.app.locals.db

    // Find user by email
    const [users] = await db.query("SELECT * FROM users WHERE email = ?", [email])

    if (users.length === 0) {
      return res.status(401).json({ success: false, message: "Invalid credentials" })
    }

    const user = users[0]

    // Check password
    const isMatch = await bcrypt.compare(password, user.password)

    if (!isMatch) {
      return res.status(401).json({ success: false, message: "Invalid credentials" })
    }

    // Generate JWT token
    const token = jwt.sign({ id: user.id, username: user.username }, process.env.JWT_SECRET || "your_jwt_secret", {
      expiresIn: "24h",
    })

    res.json({
      success: true,
      message: "Login successful",
      token,
      user: {
        id: user.id,
        username: user.username,
        email: user.email,
        avatar: user.avatar,
      },
    })
  } catch (error) {
    console.error("Login error:", error)
    res.status(500).json({ success: false, message: "Error logging in" })
  }
}

// Get user profile
export const getUserProfile = async (req, res) => {
  try {
    const userId = req.user.id
    const db = req.app.locals.db

    // Get user data
    const [users] = await db.query("SELECT id, username, email, avatar, created_at FROM users WHERE id = ?", [userId])

    if (users.length === 0) {
      return res.status(404).json({ success: false, message: "User not found" })
    }

    // Get user stats
    const [quizCount] = await db.query("SELECT COUNT(*) as count FROM quizzes WHERE user_id = ?", [userId])

    const [completedQuizzes] = await db.query("SELECT COUNT(*) as count FROM user_progress WHERE user_id = ?", [userId])

    // Get streak data (consecutive days with completed quizzes)
    const [streakData] = await db.query(
      `
      SELECT DATEDIFF(MAX(completed_at), MIN(completed_at)) + 1 as streak_days
      FROM (
        SELECT DISTINCT DATE(completed_at) as completed_date, completed_at
        FROM user_progress
        WHERE user_id = ?
        ORDER BY completed_date DESC
        LIMIT 30
      ) as daily_completions
      WHERE DATEDIFF(
        LEAD(completed_date) OVER (ORDER BY completed_date DESC),
        completed_date
      ) = -1 OR completed_date = CURDATE()
    `,
      [userId],
    )

    const streak = streakData[0]?.streak_days || 0

    res.json({
      success: true,
      user: users[0],
      stats: {
        quizCount: quizCount[0].count,
        completedQuizzes: completedQuizzes[0].count,
        streak,
      },
    })
  } catch (error) {
    console.error("Profile error:", error)
    res.status(500).json({ success: false, message: "Error fetching profile" })
  }
}

// Update user profile
export const updateUserProfile = async (req, res) => {
  try {
    const userId = req.user.id
    const { username, email, avatar } = req.body
    const updateFields = {}

    if (username) updateFields.username = username
    if (email) updateFields.email = email
    if (avatar) updateFields.avatar = avatar

    if (Object.keys(updateFields).length === 0) {
      return res.status(400).json({ success: false, message: "No fields to update" })
    }

    const db = req.app.locals.db

    // Update user
    const [result] = await db.query("UPDATE users SET ? WHERE id = ?", [updateFields, userId])

    if (result.affectedRows === 0) {
      return res.status(404).json({ success: false, message: "User not found" })
    }

    // Get updated user data
    const [users] = await db.query("SELECT id, username, email, avatar, created_at FROM users WHERE id = ?", [userId])

    res.json({
      success: true,
      message: "Profile updated successfully",
      user: users[0],
    })
  } catch (error) {
    console.error("Update profile error:", error)
    res.status(500).json({ success: false, message: "Error updating profile" })
  }
}
