// Get all flashcards for a user
export const getUserFlashcards = async (req, res) => {
  try {
    const db = req.app.locals.db
    const userId = req.user.id
    const categoryId = req.query.category

    let query = `
      SELECT f.*, c.name as category_name
      FROM flashcards f
      LEFT JOIN categories c ON f.category_id = c.id
      WHERE f.user_id = ?
    `

    const queryParams = [userId]

    if (categoryId) {
      query += " AND f.category_id = ?"
      queryParams.push(categoryId)
    }

    query += " ORDER BY f.created_at DESC"

    const [flashcards] = await db.query(query, queryParams)

    res.json({
      success: true,
      flashcards,
    })
  } catch (error) {
    console.error("Get flashcards error:", error)
    res.status(500).json({ success: false, message: "Error fetching flashcards" })
  }
}

// Create a new flashcard
export const createFlashcard = async (req, res) => {
  const { front_text, back_text, category_id } = req.body

  if (!front_text || !back_text) {
    return res.status(400).json({ success: false, message: "Front and back text are required" })
  }

  try {
    const db = req.app.locals.db

    const [result] = await db.query(
      "INSERT INTO flashcards (user_id, front_text, back_text, category_id) VALUES (?, ?, ?, ?)",
      [req.user.id, front_text, back_text, category_id || null],
    )

    res.status(201).json({
      success: true,
      message: "Flashcard created successfully",
      flashcardId: result.insertId,
    })
  } catch (error) {
    console.error("Create flashcard error:", error)
    res.status(500).json({ success: false, message: "Error creating flashcard" })
  }
}

// Update a flashcard
export const updateFlashcard = async (req, res) => {
  const flashcardId = req.params.id
  const { front_text, back_text, category_id } = req.body

  if (!front_text || !back_text) {
    return res.status(400).json({ success: false, message: "Front and back text are required" })
  }

  try {
    const db = req.app.locals.db

    // Check if flashcard exists and belongs to user
    const [flashcards] = await db.query("SELECT * FROM flashcards WHERE id = ? AND user_id = ?", [
      flashcardId,
      req.user.id,
    ])

    if (flashcards.length === 0) {
      return res.status(404).json({
        success: false,
        message: "Flashcard not found or you do not have permission to edit it",
      })
    }

    await db.query("UPDATE flashcards SET front_text = ?, back_text = ?, category_id = ? WHERE id = ?", [
      front_text,
      back_text,
      category_id || null,
      flashcardId,
    ])

    res.json({
      success: true,
      message: "Flashcard updated successfully",
    })
  } catch (error) {
    console.error("Update flashcard error:", error)
    res.status(500).json({ success: false, message: "Error updating flashcard" })
  }
}

// Delete a flashcard
export const deleteFlashcard = async (req, res) => {
  const flashcardId = req.params.id

  try {
    const db = req.app.locals.db

    // Check if flashcard exists and belongs to user
    const [flashcards] = await db.query("SELECT * FROM flashcards WHERE id = ? AND user_id = ?", [
      flashcardId,
      req.user.id,
    ])

    if (flashcards.length === 0) {
      return res.status(404).json({
        success: false,
        message: "Flashcard not found or you do not have permission to delete it",
      })
    }

    await db.query("DELETE FROM flashcards WHERE id = ?", [flashcardId])

    res.json({
      success: true,
      message: "Flashcard deleted successfully",
    })
  } catch (error) {
    console.error("Delete flashcard error:", error)
    res.status(500).json({ success: false, message: "Error deleting flashcard" })
  }
}

// Get all study guides for a user
export const getUserStudyGuides = async (req, res) => {
  try {
    const db = req.app.locals.db
    const userId = req.user.id
    const categoryId = req.query.category

    let query = `
      SELECT sg.*, c.name as category_name,
      (SELECT COUNT(*) FROM study_guide_sections WHERE study_guide_id = sg.id) as section_count
      FROM study_guides sg
      LEFT JOIN categories c ON sg.category_id = c.id
      WHERE sg.user_id = ?
    `

    const queryParams = [userId]

    if (categoryId) {
      query += " AND sg.category_id = ?"
      queryParams.push(categoryId)
    }

    query += " ORDER BY sg.created_at DESC"

    const [studyGuides] = await db.query(query, queryParams)

    res.json({
      success: true,
      studyGuides,
    })
  } catch (error) {
    console.error("Get study guides error:", error)
    res.status(500).json({ success: false, message: "Error fetching study guides" })
  }
}

// Get a single study guide with sections
export const getStudyGuideById = async (req, res) => {
  try {
    const db = req.app.locals.db
    const studyGuideId = req.params.id

    // Get study guide details
    const [studyGuides] = await db.query(
      `
      SELECT sg.*, c.name as category_name
      FROM study_guides sg
      LEFT JOIN categories c ON sg.category_id = c.id
      WHERE sg.id = ? AND sg.user_id = ?
    `,
      [studyGuideId, req.user.id],
    )

    if (studyGuides.length === 0) {
      return res.status(404).json({ success: false, message: "Study guide not found" })
    }

    const studyGuide = studyGuides[0]

    // Get sections
    const [sections] = await db.query(
      `
      SELECT * FROM study_guide_sections
      WHERE study_guide_id = ?
      ORDER BY section_order
    `,
      [studyGuideId],
    )

    // Attach sections to study guide
    studyGuide.sections = sections

    res.json({
      success: true,
      studyGuide,
    })
  } catch (error) {
    console.error("Get study guide error:", error)
    res.status(500).json({ success: false, message: "Error fetching study guide" })
  }
}

// Create a new study guide
export const createStudyGuide = async (req, res) => {
  const { title, category_id, sections } = req.body

  if (!title || !sections || !Array.isArray(sections) || sections.length === 0) {
    return res.status(400).json({
      success: false,
      message: "Title and at least one section are required",
    })
  }

  const db = req.app.locals.db

  try {
    // Start transaction
    await db.query("START TRANSACTION")

    // Create study guide
    const [studyGuideResult] = await db.query(
      "INSERT INTO study_guides (title, user_id, category_id) VALUES (?, ?, ?)",
      [title, req.user.id, category_id || null],
    )

    const studyGuideId = studyGuideResult.insertId

    // Add sections
    for (let i = 0; i < sections.length; i++) {
      const section = sections[i]

      if (!section.title || !section.content) {
        await db.query("ROLLBACK")
        return res.status(400).json({
          success: false,
          message: "Each section must have a title and content",
        })
      }

      await db.query(
        "INSERT INTO study_guide_sections (study_guide_id, title, content, section_order) VALUES (?, ?, ?, ?)",
        [studyGuideId, section.title, section.content, i],
      )
    }

    // Commit transaction
    await db.query("COMMIT")

    res.status(201).json({
      success: true,
      message: "Study guide created successfully",
      studyGuideId,
    })
  } catch (error) {
    // Rollback on error
    await db.query("ROLLBACK")
    console.error("Create study guide error:", error)
    res.status(500).json({ success: false, message: "Error creating study guide" })
  }
}

// Update a study guide
export const updateStudyGuide = async (req, res) => {
  const studyGuideId = req.params.id
  const { title, category_id, sections } = req.body

  if (!title) {
    return res.status(400).json({ success: false, message: "Title is required" })
  }

  const db = req.app.locals.db

  try {
    // Check if study guide exists and belongs to user
    const [studyGuides] = await db.query("SELECT * FROM study_guides WHERE id = ? AND user_id = ?", [
      studyGuideId,
      req.user.id,
    ])

    if (studyGuides.length === 0) {
      return res.status(404).json({
        success: false,
        message: "Study guide not found or you do not have permission to edit it",
      })
    }

    // Start transaction
    await db.query("START TRANSACTION")

    // Update study guide
    await db.query("UPDATE study_guides SET title = ?, category_id = ? WHERE id = ?", [
      title,
      category_id || null,
      studyGuideId,
    ])

    // Update sections if provided
    if (sections && Array.isArray(sections)) {
      // Delete existing sections
      await db.query("DELETE FROM study_guide_sections WHERE study_guide_id = ?", [studyGuideId])

      // Add new sections
      for (let i = 0; i < sections.length; i++) {
        const section = sections[i]

        if (!section.title || !section.content) {
          await db.query("ROLLBACK")
          return res.status(400).json({
            success: false,
            message: "Each section must have a title and content",
          })
        }

        await db.query(
          "INSERT INTO study_guide_sections (study_guide_id, title, content, section_order) VALUES (?, ?, ?, ?)",
          [studyGuideId, section.title, section.content, i],
        )
      }
    }

    // Commit transaction
    await db.query("COMMIT")

    res.json({
      success: true,
      message: "Study guide updated successfully",
    })
  } catch (error) {
    // Rollback on error
    await db.query("ROLLBACK")
    console.error("Update study guide error:", error)
    res.status(500).json({ success: false, message: "Error updating study guide" })
  }
}

// Delete a study guide
export const deleteStudyGuide = async (req, res) => {
  const studyGuideId = req.params.id

  try {
    const db = req.app.locals.db

    // Check if study guide exists and belongs to user
    const [studyGuides] = await db.query("SELECT * FROM study_guides WHERE id = ? AND user_id = ?", [
      studyGuideId,
      req.user.id,
    ])

    if (studyGuides.length === 0) {
      return res.status(404).json({
        success: false,
        message: "Study guide not found or you do not have permission to delete it",
      })
    }

    // Delete study guide (cascade will delete sections)
    await db.query("DELETE FROM study_guides WHERE id = ?", [studyGuideId])

    res.json({
      success: true,
      message: "Study guide deleted successfully",
    })
  } catch (error) {
    console.error("Delete study guide error:", error)
    res.status(500).json({ success: false, message: "Error deleting study guide" })
  }
}

