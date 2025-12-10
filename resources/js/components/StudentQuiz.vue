<template>
    <div class="container mx-auto p-4 md:p-6 max-w-4xl">
        <div v-if="!submitted">
            <h1 class="text-2xl md:text-3xl font-bold mb-4 md:mb-6 text-center">{{ topic?.title || 'Тест' }}</h1>
            
            <div v-if="loading" class="text-center py-8">
                <p class="text-base md:text-lg">Тест жүктелуде...</p>
            </div>

            <div v-else-if="questions.length === 0" class="text-center py-8">
                <p class="text-base md:text-lg">Бұл тест үшін сұрақтар жоқ.</p>
            </div>

            <div v-else>
                <!-- Timer -->
                <div v-if="timeLimit" class="bg-white p-4 rounded-lg shadow mb-4 md:mb-6 border-2" :class="timerWarning ? 'border-red-500' : timerCritical ? 'border-orange-500' : 'border-blue-500'">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 md:w-16 md:h-16 rounded-full flex items-center justify-center text-lg md:text-2xl font-bold" :class="timerWarning ? 'bg-red-500 text-white' : timerCritical ? 'bg-orange-500 text-white' : 'bg-blue-500 text-white'">
                                <span>{{ formattedTime }}</span>
                            </div>
                            <div>
                                <p class="text-sm md:text-base font-semibold text-gray-800">Қалған Уақыт</p>
                                <p class="text-xs text-gray-600">Жалпы уақыт: {{ timeLimit }} минут</p>
                            </div>
                        </div>
                        <div v-if="timerWarning" class="text-center">
                            <p class="text-sm md:text-base font-bold text-red-600 animate-pulse">⚠️ Ескерту: 5 минуттан аз уақыт қалды!</p>
                        </div>
                    </div>
                </div>

                <!-- Progress Indicator -->
                <div class="bg-white p-3 md:p-4 rounded-lg shadow mb-4 md:mb-6">
                    <div class="flex flex-wrap gap-1 md:gap-2 justify-center">
                        <div 
                            v-for="(question, index) in questions" 
                            :key="question.id"
                            @click="goToQuestion(index)"
                            :class="[
                                'w-6 h-6 md:w-8 md:h-8 rounded border-2 cursor-pointer transition-all flex items-center justify-center text-xs md:text-sm font-semibold',
                                index === currentQuestionIndex 
                                    ? 'bg-blue-600 border-blue-700 text-white scale-110' 
                                    : hasAnswer(question.id)
                                        ? 'bg-green-500 border-green-600 text-white'
                                        : 'bg-gray-100 border-gray-300 text-gray-600 hover:bg-gray-200'
                            ]"
                            :title="`Сұрақ ${index + 1}`"
                        >
                            {{ index + 1 }}
                        </div>
                    </div>
                    <div class="mt-3 text-center">
                        <p class="text-sm md:text-base text-gray-600">
                            Сұрақ <span class="font-bold text-blue-600">{{ currentQuestionIndex + 1 }}</span> / <span class="font-bold">{{ questions.length }}</span>
                        </p>
                    </div>
                </div>

                <!-- Current Question -->
                <div class="bg-white p-4 md:p-6 rounded-lg shadow mb-4 md:mb-6">
                    <div v-if="currentQuestion">
                        <h3 class="text-lg md:text-xl font-semibold mb-3 md:mb-4">
                            {{ currentQuestionIndex + 1 }}. {{ currentQuestion.question_text }}
                        </h3>
                        <p class="text-xs md:text-sm text-gray-600 mb-4">Барлық дұрыс жауаптарды таңдаңыз (бірнеше таңдауға болады)</p>
                        <div class="space-y-2 md:space-y-3">
                            <label 
                                v-for="answer in currentQuestion.answers" 
                                :key="answer.id" 
                                class="flex items-center p-3 md:p-4 border-2 rounded-lg hover:bg-gray-50 cursor-pointer transition-colors"
                                :class="isAnswerSelected(answer.id) ? 'border-blue-500 bg-blue-50' : 'border-gray-200'"
                            >
                                <input 
                                    type="checkbox" 
                                    :value="answer.id"
                                    v-model="answers[currentQuestion.id]"
                                    class="mr-3 w-5 h-5 md:w-6 md:h-6 flex-shrink-0 cursor-pointer"
                                >
                                <span class="text-sm md:text-base break-words flex-1">{{ answer.answer_text }}</span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="flex justify-between items-center gap-3 md:gap-4">
                    <button 
                        @click="previousQuestion"
                        :disabled="currentQuestionIndex === 0"
                        class="px-4 md:px-6 py-2 md:py-3 bg-gray-500 text-white rounded-lg text-sm md:text-base hover:bg-gray-600 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors flex-1 sm:flex-none"
                    >
                        ← Алдыңғы
                    </button>
                    
                    <div class="flex gap-2 flex-1 sm:flex-none justify-center">
                        <button 
                            v-if="currentQuestionIndex < questions.length - 1"
                            @click="nextQuestion"
                            class="px-4 md:px-6 py-2 md:py-3 bg-blue-500 text-white rounded-lg text-sm md:text-base hover:bg-blue-600 transition-colors flex-1 sm:flex-none"
                        >
                            Келесі →
                        </button>
                        <button 
                            v-else
                            @click="submitQuiz"
                            :disabled="!canSubmit"
                            class="px-4 md:px-6 py-2 md:py-3 bg-green-500 text-white rounded-lg text-sm md:text-base hover:bg-green-600 disabled:bg-gray-400 disabled:cursor-not-allowed transition-colors flex-1 sm:flex-none"
                        >
                            Тестті Жіберу ✓
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="py-6 md:py-8">
            <h2 class="text-2xl md:text-3xl font-bold mb-4 md:mb-6 text-center">Тест Нәтижелері</h2>
            
            <!-- Summary Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white p-4 rounded shadow text-center">
                    <p class="text-sm text-gray-600 mb-1">Жалпы Сұрақтар</p>
                    <p class="text-2xl font-bold text-gray-800">{{ total }}</p>
                </div>
                <div class="bg-white p-4 rounded shadow text-center">
                    <p class="text-sm text-gray-600 mb-1">Дұрыс</p>
                    <p class="text-2xl font-bold text-green-600">{{ correct }}</p>
                </div>
                <div class="bg-white p-4 rounded shadow text-center">
                    <p class="text-sm text-gray-600 mb-1">Қате</p>
                    <p class="text-2xl font-bold text-red-600">{{ wrong }}</p>
                </div>
                <div class="bg-white p-4 rounded shadow text-center">
                    <p class="text-sm text-gray-600 mb-1">Ұпайлар</p>
                    <p class="text-2xl font-bold text-blue-600">{{ points }} / {{ maxPoints }}</p>
                </div>
            </div>
            
            <!-- Detailed Results Table -->
            <div v-if="questionResults && questionResults.length > 0" class="bg-white p-4 md:p-6 rounded shadow mb-6 overflow-x-auto">
                <h3 class="text-lg md:text-xl font-semibold mb-4">Толық Нәтижелер</h3>
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse min-w-[600px]">
                        <thead>
                            <tr>
                                <th v-for="result in questionResults" :key="result.question_id" 
                                    class="border border-gray-300 p-2 md:p-3 bg-gray-100 font-semibold text-xs md:text-sm">
                                    {{ result.question_number }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Row 1: Correct Answer Options -->
                            <tr>
                                <td v-for="result in questionResults" :key="result.question_id" 
                                    class="border border-gray-300 p-2 md:p-3 text-center text-xs md:text-sm">
                                    {{ result.correct_answers_text || '-' }}
                                </td>
                            </tr>
                            <!-- Row 2: Color-coded status cell -->
                            <tr>
                                <td v-for="result in questionResults" :key="result.question_id" 
                                    :class="[
                                        'border border-gray-300 p-3 md:p-4 text-center font-semibold text-sm md:text-base',
                                        result.all_correct ? 'bg-green-200 text-green-800' : 'bg-red-100 text-red-800'
                                    ]">
                                    <span v-if="result.all_correct">✓</span>
                                    <span v-else>✗</span>
                                </td>
                            </tr>
                            <!-- Row 3: Points earned -->
                            <tr>
                                <td v-for="result in questionResults" :key="result.question_id" 
                                    class="border border-gray-300 p-2 md:p-3 text-center font-semibold text-xs md:text-sm">
                                    {{ result.points }} {{ result.points === 1 ? 'ұпай' : 'ұпай' }}
                                </td>
                            </tr>
                            <!-- Row 4: Total Points in lower right -->
                            <tr>
                                <td :colspan="questionResults.length - 1" class="border border-gray-300"></td>
                                <td class="border border-gray-300 p-2 md:p-3 text-right font-semibold text-sm md:text-base">
                                    Ұпайлар: {{ points }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Swal from 'sweetalert2';

export default {
    name: 'StudentQuiz',
    data() {
        return {
            topic: null,
            questions: [],
            answers: {},
            currentQuestionIndex: 0,
            loading: true,
            submitted: false,
            correct: 0,
            wrong: 0,
            total: 0,
            points: 0,
            maxPoints: 0,
            questionResults: [],
            timeLimit: null, // in minutes
            timeRemaining: null, // in seconds
            timerInterval: null,
            warningShown: false
        };
    },
    computed: {
        currentQuestion() {
            if (this.questions.length > 0 && this.currentQuestionIndex >= 0 && this.currentQuestionIndex < this.questions.length) {
                return this.questions[this.currentQuestionIndex];
            }
            return null;
        },
        canSubmit() {
            // Check if at least one answer is selected for each question
            return this.questions.every(question => {
                const selected = this.answers[question.id];
                return selected && selected.length > 0;
            });
        },
        formattedTime() {
            if (this.timeRemaining === null) return '--:--';
            const minutes = Math.floor(this.timeRemaining / 60);
            const seconds = this.timeRemaining % 60;
            return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
        },
        timerWarning() {
            return this.timeRemaining !== null && this.timeRemaining <= 300 && this.timeRemaining > 0; // 5 minutes = 300 seconds
        },
        timerCritical() {
            return this.timeRemaining !== null && this.timeRemaining <= 60 && this.timeRemaining > 0; // 1 minute
        }
    },
    mounted() {
        this.loadQuiz();
    },
    beforeUnmount() {
        this.stopTimer();
    },
    methods: {
        async loadQuiz() {
            try {
                const topicId = window.location.pathname.split('/').pop();
                const response = await window.axios.get(`/api/quiz/${topicId}`);
                this.topic = response.data.topic;
                this.questions = response.data.questions;
                // Initialize answers as arrays for multiple selection
                this.questions.forEach(question => {
                    this.answers[question.id] = [];
                });
                
                // Initialize timer if time limit is set
                if (response.data.topic.time_limit) {
                    this.timeLimit = response.data.topic.time_limit;
                    this.timeRemaining = this.timeLimit * 60; // Convert to seconds
                    this.startTimer();
                }
                
                this.loading = false;
            } catch (error) {
                console.error('Error loading quiz:', error);
                this.loading = false;
                await Swal.fire({
                    icon: 'error',
                    title: 'Тестті Жүктеу Қатесі',
                    text: 'Тестті жүктеу қатесі. URL мекенжайын тексеріңіз.',
                    confirmButtonColor: '#ef4444'
                });
            }
        },
        startTimer() {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
            }
            
            this.timerInterval = setInterval(() => {
                if (this.timeRemaining > 0) {
                    this.timeRemaining--;
                    
                    // Show warning at 5 minutes (300 seconds)
                    if (this.timeRemaining === 300 && !this.warningShown) {
                        this.warningShown = true;
                        Swal.fire({
                            icon: 'warning',
                            title: 'Уақыт Ескертуі!',
                            text: 'Тестті аяқтауға 5 минут қалды.',
                            confirmButtonColor: '#f59e0b',
                            timer: 5000
                        });
                    }
                    
                    // Auto-submit when time runs out
                    if (this.timeRemaining === 0) {
                        this.stopTimer();
                        this.autoSubmitQuiz();
                    }
                }
            }, 1000);
        },
        stopTimer() {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
                this.timerInterval = null;
            }
        },
        async autoSubmitQuiz() {
            await Swal.fire({
                icon: 'info',
                title: 'Уақыт Аяқталды!',
                text: 'Сіздің уақытыңыз аяқталды. Тест сіздің қазіргі жауаптарыңызбен автоматты түрде жіберіледі.',
                confirmButtonColor: '#3b82f6',
                allowOutsideClick: false,
                allowEscapeKey: false
            });
            
            // Submit with current answers (even if incomplete)
            await this.submitQuiz(true);
        },
        hasAnswer(questionId) {
            const selected = this.answers[questionId];
            return selected && selected.length > 0;
        },
        isAnswerSelected(answerId) {
            if (!this.currentQuestion) return false;
            const selected = this.answers[this.currentQuestion.id];
            return selected && selected.includes(answerId);
        },
        goToQuestion(index) {
            if (index >= 0 && index < this.questions.length) {
                this.currentQuestionIndex = index;
                // Scroll to top of question
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },
        previousQuestion() {
            if (this.currentQuestionIndex > 0) {
                this.currentQuestionIndex--;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },
        nextQuestion() {
            if (this.currentQuestionIndex < this.questions.length - 1) {
                this.currentQuestionIndex++;
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        },
        async submitQuiz(autoSubmit = false) {
            // Stop timer
            this.stopTimer();
            
            if (!autoSubmit) {
                if (!this.canSubmit) {
                    await Swal.fire({
                        icon: 'warning',
                        title: 'Толық Емес Тест',
                        text: 'Жібермес бұрын барлық сұрақтарға жауап беріңіз.',
                        confirmButtonColor: '#3b82f6'
                    });
                    return;
                }

                const result = await Swal.fire({
                    icon: 'question',
                    title: 'Тестті Жіберу?',
                    text: 'Сіз жауаптарыңызды жібергіңіз келе ме?',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Иә, жіберу!',
                    cancelButtonText: 'Болдырмау'
                });

                if (!result.isConfirmed) {
                    // Restart timer if user cancels
                    if (this.timeLimit && this.timeRemaining > 0) {
                        this.startTimer();
                    }
                    return;
                }
            } else {
                // Auto-submit: allow submission even if not all questions are answered
                // This is handled by the backend - it will process whatever answers are provided
            }

            try {
                const topicId = window.location.pathname.split('/').pop();
                // Convert answers to array format: each question can have multiple answer_ids
                const answersArray = Object.entries(this.answers).map(([questionId, answerIds]) => ({
                    question_id: parseInt(questionId),
                    answer_ids: Array.isArray(answerIds) ? answerIds.map(id => parseInt(id)) : [parseInt(answerIds)]
                }));

                const response = await window.axios.post(`/api/quiz/${topicId}/submit`, {
                    answers: answersArray
                });

                this.correct = response.data.correct;
                this.wrong = response.data.wrong;
                this.total = response.data.total;
                this.points = response.data.points || 0;
                this.maxPoints = response.data.max_points || 0;
                this.questionResults = response.data.question_results || [];
                this.submitted = true;

                // Scroll to results
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } catch (error) {
                console.error('Error submitting quiz:', error);
                await Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Тестті жіберу қатесі. Қайталап көріңіз.',
                    confirmButtonColor: '#ef4444'
                });
            }
        }
    }
};
</script>
