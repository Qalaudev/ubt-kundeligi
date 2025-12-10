# Quiz Platform with QR Code System

A complete Laravel-based exam-preparation platform where students take topic-based quizzes by scanning QR codes.

## Features

### Admin Panel
- Create, edit, and delete topics
- Add questions to topics with multiple answer choices
- Mark correct answers
- Generate QR codes for topics
- Export QR codes as PNG images

### Student Features
- Scan QR code to access quiz
- Take quiz with multiple choice questions
- Submit answers and view results
- See correct/incorrect answer counts

## Installation

1. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

2. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Database Configuration**
   - Update `.env` with your database credentials
   - Run migrations:
   ```bash
   php artisan migrate
   ```

4. **Storage Link**
   ```bash
   php artisan storage:link
   ```

5. **Build Frontend Assets**
   ```bash
   npm run build
   # Or for development:
   npm run dev
   ```

## Running the Application

1. **Start Laravel Server**
   ```bash
   php artisan serve
   ```

2. **Start Vite Dev Server** (for development)
   ```bash
   npm run dev
   ```

3. **Access the Application**
   - Admin Dashboard: `http://localhost:8000/admin`
   - Quiz Page: `http://localhost:8000/quiz/{topic_id}`

## API Endpoints

### Admin Endpoints

- `GET /api/topics` - List all topics
- `POST /api/topics` - Create a new topic
- `GET /api/topics/{id}` - Get topic details
- `PUT /api/topics/{id}` - Update topic
- `DELETE /api/topics/{id}` - Delete topic
- `POST /api/topics/{id}/generate-qr` - Generate QR code for topic

- `POST /api/questions` - Create a question
- `PUT /api/questions/{id}` - Update question
- `DELETE /api/questions/{id}` - Delete question

### Student Endpoints

- `GET /api/quiz/{topic_id}` - Get quiz data
- `POST /api/quiz/{topic_id}/submit` - Submit quiz answers

## Database Structure

### Topics
- `id`
- `title`
- `qr_code_path` (nullable)
- `timestamps`

### Questions
- `id`
- `topic_id` (FK)
- `question_text`
- `timestamps`

### Answers
- `id`
- `question_id` (FK)
- `answer_text`
- `is_correct` (boolean)
- `timestamps`

### Results
- `id`
- `topic_id` (FK)
- `user_id` (nullable, FK)
- `correct_count`
- `wrong_count`
- `timestamps`

## Usage

### Creating a Quiz

1. Go to `/admin`
2. Click "New Topic" and create a topic
3. Click "Manage Questions" on the topic
4. Add questions with multiple answers (mark at least one as correct)
5. Click "Generate QR" to create a QR code
6. Students can scan the QR code to access the quiz

### Taking a Quiz

1. Scan the QR code or visit `/quiz/{topic_id}`
2. Answer all questions
3. Click "Submit Quiz"
4. View results with correct/incorrect counts

## Technologies Used

- **Backend**: Laravel 12
- **Frontend**: Vue 3, Vite, Tailwind CSS
- **QR Code**: simplesoftwareio/simple-qrcode
- **Database**: MySQL/SQLite/PostgreSQL (configurable)

## Project Structure

```
app/
├── Http/Controllers/Api/
│   ├── Admin/
│   │   ├── TopicController.php
│   │   └── QuestionController.php
│   └── Student/
│       └── QuizController.php
├── Models/
│   ├── Topic.php
│   ├── Question.php
│   ├── Answer.php
│   └── Result.php
└── Services/
    └── QrCodeService.php

resources/
├── js/
│   ├── components/
│   │   ├── AdminDashboard.vue
│   │   └── StudentQuiz.vue
│   └── app.js
└── views/
    ├── admin.blade.php
    └── quiz.blade.php

routes/
├── api.php
└── web.php
```

## Notes

- QR codes are stored in `storage/app/public/qr/`
- Make sure `APP_URL` in `.env` is set correctly for QR code generation
- The system doesn't require authentication by default (can be added if needed)
