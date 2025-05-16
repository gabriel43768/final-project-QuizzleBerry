# Quizzleberry Backend API

This is the backend API for the Quizzleberry quiz application, built with Express.js and MySQL.

## Features

- User authentication (register, login, profile management)
- Quiz management (create, read, update, delete)
- Study materials (flashcards, study guides)
- AI-powered document analysis for generating study materials
- Progress tracking

## Prerequisites

- Node.js (v14 or higher)
- MySQL (via XAMPP or standalone)

## Setup

1. Clone the repository
2. Install dependencies:
   \`\`\`
   npm install
   \`\`\`
3. Create a `.env` file based on `.env.example` and configure your environment variables
4. Initialize the database:
   \`\`\`
   npm run init-db
   \`\`\`
5. Start the server:
   \`\`\`
   npm run dev
   \`\`\`

## API Endpoints

### Authentication

- `POST /api/users/register` - Register a new user
- `POST /api/users/login` - Login user
- `GET /api/users/profile` - Get user profile
- `PUT /api/users/profile` - Update user profile

### Quizzes

- `GET /api/quizzes` - Get all quizzes
- `GET /api/quizzes/my-quizzes` - Get user's quizzes
- `GET /api/quizzes/:id` - Get a single quiz with questions and answers
- `POST /api/quizzes` - Create a new quiz
- `PUT /api/quizzes/:id` - Update a quiz
- `DELETE /api/quizzes/:id` - Delete a quiz
- `POST /api/quizzes/:id/submit` - Submit quiz answers and record progress

### Study Materials

- `GET /api/study-materials/flashcards` - Get all flashcards for a user
- `POST /api/study-materials/flashcards` - Create a new flashcard
- `PUT /api/study-materials/flashcards/:id` - Update a flashcard
- `DELETE /api/study-materials/flashcards/:id` - Delete a flashcard
- `GET /api/study-materials/study-guides` - Get all study guides for a user
- `GET /api/study-materials/study-guides/:id` - Get a single study guide with sections
- `POST /api/study-materials/study-guides` - Create a new study guide
- `PUT /api/study-materials/study-guides/:id` - Update a study guide
- `DELETE /api/study-materials/study-guides/:id` - Delete a study guide

### AI Analysis

- `POST /api/ai-analysis/upload` - Upload document for AI analysis
- `GET /api/ai-analysis/:id/status` - Get analysis status
- `GET /api/ai-analysis/:id/results` - Get analysis results
- `POST /api/ai-analysis/:id/save` - Save generated content to user's library

## Database Schema

The database consists of the following tables:

- `users` - User accounts
- `categories` - Quiz and study material categories
- `quizzes` - Quiz metadata
- `questions` - Quiz questions
- `answers` - Question answers
- `flashcards` - User flashcards
- `study_guides` - Study guide metadata
- `study_guide_sections` - Study guide content sections
- `ai_analysis` - Document analysis metadata
- `ai_generated_content` - Content generated from AI analysis
- `user_progress` - User quiz completion and scores

## License

MIT
