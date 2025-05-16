// Get all quizzes (with optional category filter)
export const getAllQuizzes = async (req, res) => {
  try {
    const db = req.app.locals.db
    const categoryId = req.query.category

    let query = `
      SELECT q.*, c.name as category_name, u.username as creator_name,
      (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as question_count
      FROM quizzes q
      LEFT JOIN categories c ON q.category_id = c.id
      LEFT JOIN users u ON q.user_id = u.id
    `

    const queryParams = []

    if (categoryId) {
      query += " WHERE q.category_id = ?"
      queryParams.push(categoryId)
    }

    query += " ORDER BY q.created_at DESC"

    const [quizzes] = await db.query(query, queryParams)

    res.json({
      success: true,
      quizzes,
    })
  } catch (error) {
    console.error("Get quizzes error:", error)
    res.status(500).json({ success: false, message: "Error fetching quizzes" })
  }
}

// Get user's quizzes
export const getUserQuizzes = async (req, res) => {
  try {
    const db = req.app.locals.db
    const userId = req.user.id

    const [quizzes] = await db.query(
      `
      SELECT q.*, c.name as category_name,
      (SELECT COUNT(*) FROM questions WHERE quiz_id = q.id) as question_count
      FROM quizzes q
      LEFT JOIN categories c ON q.category_id = c.id
      WHERE q.user_id = ?
      ORDER BY q.created_at DESC
    `,
      [userId],
    )

    res.json({
      success: true,
      quizzes,
    })
  } catch (error) {
    console.error("Get my quizzes error:", error)
    res.status(500).json({ success: false, message: "Error fetching your quizzes" })
  }
}

// Get a single quiz with questions and answers
export const getQuizById = async (req, res) => {
  try {
    const db = req.app.locals.db
    const quizId = req.params.id

    // Get quiz details
    const [quizzes] = await db.query(
      `
      SELECT q.*, c.name as category_name, u.username as creator_name
      FROM quizzes q
      LEFT JOIN categories c ON q.category_id = c.id
      LEFT JOIN users u ON q.user_id = u.id
      WHERE q.id = ?
    `,
      [quizId],
    )

    if (quizzes.length === 0) {
      return res.status(404).json({ success: false, message: "Quiz not found" })
    }

    const quiz = quizzes[0]

    // Get questions
    const [questions] = await db.query(
      `
      SELECT * FROM questions
      WHERE quiz_id = ?
      ORDER BY id
    `,
      [quizId],
    )

    // Get answers for all questions
    const questionIds = questions.map((q) => q.id)

    if (questionIds.length > 0) {
      const [answers] = await db.query(
        `
        SELECT * FROM answers
        WHERE question_id IN (?)
        ORDER BY id
      `,
        [questionIds],
      )

      // Attach answers to their respective questions
      questions.forEach((question) => {
        question.answers = answers.filter((answer) => answer.question_id === question.id)
      })
    }

    // Attach questions to quiz
    quiz.questions = questions

    res.json({
      success: true,
      quiz,
    })
  } catch (error) {
    console.error("Get quiz error:", error)
    res.status(500).json({ success: false, message: "Error fetching quiz" })
  }
}

// Create a new quiz
export const createQuiz = async (req, res) => {
  const { title, description, category_id, questions } = req.body

  if (!title || !questions || !Array.isArray(questions) || questions.length === 0) {
    return res.status(400).json({
      success: false,
      message: "Please provide title and at least one question",
    })
  }

  const db = req.app.locals.db

  try {
    // Start transaction
    await db.query("START TRANSACTION")

    // Create quiz
    const [quizResult] = await db.query(
      "INSERT INTO quizzes (title, description, category_id, user_id) VALUES (?, ?, ?, ?)",
      [title, description, category_id || null, req.user.id],
    )

    const quizId = quizResult.insertId

    // Add questions and answers
    for (const question of questions) {
      if (!question.question_text || !question.question_type) {
        await db.query("ROLLBACK")
        return res.status(400).json({
          success: false,
          message: "Each question must have question_text and question_type",
        })
      }

      const [questionResult] = await db.query(
        "INSERT INTO questions (quiz_id, question_text, question_type) VALUES (?, ?, ?)",
        [quizId, question.question_text, question.question_type],
      )

      const questionId = questionResult.insertId

      // Add answers if provided
      if (question.answers && Array.isArray(question.answers)) {
        for (const answer of question.answers) {
          if (!answer.answer_text) {
            await db.query("ROLLBACK")
            return res.status(400).json({
              success: false,
              message: "Each answer must have answer_text",
            })
          }

          await db.query("INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)", [
            questionId,
            answer.answer_text,
            answer.is_correct || false,
          ])
        }
      }
    }

    // Commit transaction
    await db.query("COMMIT")

    res.status(201).json({
      success: true,
      message: "Quiz created successfully",
      quizId,
    })
  } catch (error) {
    // Rollback on error
    await db.query("ROLLBACK")
    console.error("Create quiz error:", error)
    res.status(500).json({ success: false, message: "Error creating quiz" })
  }
}

// Update a quiz
export const updateQuiz = async (req, res) => {
  const quizId = req.params.id
  const { title, description, category_id, questions } = req.body

  if (!title) {
    return res.status(400).json({ success: false, message: "Title is required" })
  }

  const db = req.app.locals.db

  try {
    // Check if quiz exists and belongs to user
    const [quizzes] = await db.query("SELECT * FROM quizzes WHERE id = ? AND user_id = ?", [quizId, req.user.id])

    if (quizzes.length === 0) {
      return res.status(404).json({
        success: false,
        message: "Quiz not found or you do not have permission to edit it",
      })
    }

    // Start transaction
    await db.query("START TRANSACTION")

    // Update quiz
    await db.query("UPDATE quizzes SET title = ?, description = ?, category_id = ? WHERE id = ?", [
      title,
      description,
      category_id || null,
      quizId,
    ])

    // Update questions if provided
    if (questions && Array.isArray(questions)) {
      // Delete existing questions and answers
      await db.query("DELETE FROM questions WHERE quiz_id = ?", [quizId])

      // Add new questions and answers
      for (const question of questions) {
        if (!question.question_text || !question.question_type) {
          await db.query("ROLLBACK")
          return res.status(400).json({
            success: false,
            message: "Each question must have question_text and question_type",
          })
        }

        const [questionResult] = await db.query(
          "INSERT INTO questions (quiz_id, question_text, question_type) VALUES (?, ?, ?)",
          [quizId, question.question_text, question.question_type],
        )

        const questionId = questionResult.insertId

        // Add answers if provided
        if (question.answers && Array.isArray(question.answers)) {
          for (const answer of question.answers) {
            if (!answer.answer_text) {
              await db.query("ROLLBACK")
              return res.status(400).json({
                success: false,
                message: "Each answer must have answer_text",
              })
            }

            await db.query("INSERT INTO answers (question_id, answer_text, is_correct) VALUES (?, ?, ?)", [
              questionId,
              answer.answer_text,
              answer.is_correct || false,
            ])
          }
        }
      }
    }

    // Commit transaction
    await db.query("COMMIT")

    res.json({
      success: true,
      message: "Quiz updated successfully",
    })
  } catch (error) {
    // Rollback on error
    await db.query("ROLLBACK")
    console.error("Update quiz error:", error)
    res.status(500).json({ success: false, message: "Error updating quiz" })
  }
}

// Delete a quiz
export const deleteQuiz = async (req, res) => {
  const quizId = req.params.id
  const db = req.app.locals.db

  try {
    // Check if quiz exists and belongs to user
    const [quizzes] = await db.query("SELECT * FROM quizzes WHERE id = ? AND user_id = ?", [quizId, req.user.id])

    if (quizzes.length === 0) {
      return res.status(404).json({
        success: false,
        message: "Quiz not found or you do not have permission to delete it",
      })
    }

    // Delete quiz (cascade will delete questions and answers)
    await db.query("DELETE FROM quizzes WHERE id = ?", [quizId])

    res.json({
      success: true,
      message: "Quiz deleted successfully",
    })
  } catch (error) {
    console.error("Delete quiz error:", error)
    res.status(500).json({ success: false, message: "Error deleting quiz" })
  }
}

// Submit quiz answers and record progress
export const submitQuizAnswers = async (req, res) => {
  const quizId = req.params.id
  const { answers } = req.body

  if (!answers || !Array.isArray(answers)) {
    return res.status(400).json({ success: false, message: "Please provide answers" })
  }

  const db = req.app.locals.db

  try {
    // Get quiz questions and correct answers
    const [questions] = await db.query(
      `
      SELECT q.id, q.question_text, q.question_type
      FROM questions q
      WHERE q.quiz_id = ?
    `,
      [quizId],
    )

    if (questions.length === 0) {
      return res.status(404).json({ success: false, message: "Quiz not found" })
    }

    const questionIds = questions.map((q) => q.id)

    const [correctAnswers] = await db.query(
      `
      SELECT question_id, id, answer_text, is_correct
      FROM answers
      WHERE question_id IN (?) AND is_correct = TRUE
    `,
      [questionIds],
    )

    // Calculate score
    let correctCount = 0
    const results = []

    for (const answer of answers) {
      const question = questions.find((q) => q.id === answer.question_id)

      if (!question) continue

      const correctAnswer = correctAnswers.find((a) => a.question_id === answer.question_id)

      if (!correctAnswer) continue

      const isCorrect = answer.answer_id === correctAnswer.id

      results.push({
        question_id: question.id,
        question_text: question.question_text,
        user_answer_id: answer.answer_id,
        correct_answer_id: correctAnswer.id,
        is_correct: isCorrect,
      })

      if (isCorrect) {
        correctCount++
      }
    }

    const score = questions.length > 0 ? (correctCount / questions.length) * 100 : 0

    // Record user progress
    await db.query("INSERT INTO user_progress (user_id, quiz_id, score) VALUES (?, ?, ?)", [req.user.id, quizId, score])

    res.json({
      success: true,
      score,
      correctCount,
      totalQuestions: questions.length,
      results,
    })
  } catch (error) {
    console.error("Submit quiz error:", error)
    res.status(500).json({ success: false, message: "Error submitting quiz" })
  }
}
