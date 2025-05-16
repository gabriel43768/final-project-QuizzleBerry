import jwt from "jsonwebtoken"

export const authenticateToken = (req, res, next) => {
  const token = req.headers.authorization?.split(" ")[1]

  if (!token) {
    return res.status(401).json({ success: false, message: "No token provided" })
  }

  try {
    const decoded = jwt.verify(token, process.env.JWT_SECRET || "your_jwt_secret")
    req.user = decoded
    next()
  } catch (error) {
    return res.status(401).json({ success: false, message: "Invalid token" })
  }
}
