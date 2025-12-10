<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white shadow-md">
            <div class="container mx-auto px-4 py-4">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Админ Панелі</h1>
                    <div class="flex items-center gap-4 w-full sm:w-auto">
                        <div class="flex items-center gap-2">
                            <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                                <span class="text-white text-sm font-semibold">{{ userInitial }}</span>
                            </div>
                            <div class="hidden sm:block">
                                <p class="text-sm font-medium text-gray-800">{{ currentUser.name }}</p>
                                <p class="text-xs text-gray-500">{{ currentUser.email }}</p>
                            </div>
                        </div>
                        <form @submit.prevent="logout" class="w-full sm:w-auto">
                            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition-colors w-full sm:w-auto">
                                Шығу
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white border-b">
            <div class="container mx-auto px-4">
                <div class="flex gap-2">
                    <button
                        @click="activeTab = 'topics'"
                        :class="[
                            'px-6 py-3 font-medium transition-colors',
                            activeTab === 'topics'
                                ? 'border-b-2 border-blue-600 text-blue-600'
                                : 'text-gray-600 hover:text-gray-800'
                        ]"
                    >
                        Тақырыптар
                    </button>
                    <button
                        @click="activeTab = 'users'"
                        :class="[
                            'px-6 py-3 font-medium transition-colors',
                            activeTab === 'users'
                                ? 'border-b-2 border-blue-600 text-blue-600'
                                : 'text-gray-600 hover:text-gray-800'
                        ]"
                    >
                        Пайдаланушылар
                    </button>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 py-6">
            <!-- Topics Tab -->
            <div v-if="activeTab === 'topics'">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
                    <h2 class="text-xl md:text-2xl font-semibold text-gray-800">Тест Тақырыптары</h2>
                    <button @click="showTopicForm = true" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition-colors w-full sm:w-auto">
                        + Жаңа Тақырып
                    </button>
                </div>

                <!-- Topic Form -->
                <div v-if="showTopicForm" class="bg-white p-4 rounded-lg shadow mb-6">
                    <input v-model="newTopic.title" type="text" placeholder="Тақырып Атауы" class="w-full p-2 mb-3 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <div class="mb-3">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Уақыт Шегі (минут)</label>
                        <input v-model.number="newTopic.time_limit" type="number" min="1" max="600" placeholder="мысалы, 40 минут үшін 40" class="w-full p-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Студенттердің осы тестті аяқтау уақытын белгілеңіз (міндетті емес)</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-2">
                        <button @click="createTopic" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 transition-colors w-full sm:w-auto">Жасау</button>
                        <button @click="showTopicForm = false" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition-colors w-full sm:w-auto">Болдырмау</button>
                    </div>
                </div>

                <!-- Topics Grid (Smaller Cards) -->
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
                    <div v-for="topic in topics" :key="topic.id" class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow p-4">
                        <div class="flex flex-col">
                            <h3 class="text-base font-semibold text-gray-800 mb-1 truncate">{{ topic.title }}</h3>
                            <p class="text-sm text-gray-600 mb-3">{{ topic.questions?.length || 0 }} сұрақ</p>
                            <div class="flex flex-col gap-2">
                                <button @click="selectTopic(topic)" class="bg-blue-500 text-white px-3 py-1.5 rounded text-sm hover:bg-blue-600 transition-colors">
                                    Басқару
                                </button>
                                <button @click="generateQr(topic.id)" class="bg-purple-500 text-white px-3 py-1.5 rounded text-sm hover:bg-purple-600 transition-colors">
                                    QR Код
                                </button>
                                <button @click="deleteTopic(topic.id)" class="bg-red-500 text-white px-3 py-1.5 rounded text-sm hover:bg-red-600 transition-colors">
                                    Жою
                                </button>
                            </div>
                            <div v-if="topic.qr_code_path" class="mt-3 pt-3 border-t">
                                <p class="text-xs text-gray-600 mb-2">QR Код:</p>
                                <img :src="`/${topic.qr_code_path}`" alt="QR Code" class="w-20 h-20 mx-auto">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Questions Section (when topic selected) -->
                <div v-if="selectedTopic" class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
                        <h2 class="text-xl font-semibold text-gray-800">Сұрақтар: {{ selectedTopic.title }}</h2>
                        <div class="flex gap-2">
                            <button @click="selectedTopic = null" class="bg-gray-500 text-white px-3 py-1.5 rounded text-sm hover:bg-gray-600 transition-colors">
                                Жабу
                            </button>
                            <button @click="showQuestionForm = true" class="bg-blue-500 text-white px-3 py-1.5 rounded text-sm hover:bg-blue-600 transition-colors">
                                + Жаңа Сұрақ
                            </button>
                        </div>
                    </div>

                    <!-- Question Form (Create) -->
                    <div v-if="showQuestionForm && !editingQuestion" class="bg-gray-50 p-4 rounded mb-4 border">
                        <h3 class="font-semibold mb-3 text-base">Жаңа Сұрақ Жасау</h3>
                        <textarea v-model="newQuestion.question_text" placeholder="Сұрақ Мәтіні" class="w-full p-2 mb-3 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y min-h-[80px]"></textarea>
                        <div class="space-y-2 mb-4">
                            <div v-for="(answer, index) in newQuestion.answers" :key="index" class="flex flex-col sm:flex-row gap-2 items-start sm:items-center">
                                <input v-model="answer.answer_text" type="text" placeholder="Жауап" class="flex-1 w-full p-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                <label class="flex items-center gap-2 whitespace-nowrap">
                                    <input v-model="answer.is_correct" type="checkbox" class="w-4 h-4">
                                    <span class="text-sm">Дұрыс</span>
                                </label>
                                <button @click="removeAnswer(index)" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 transition-colors text-xs">Жою</button>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <button @click="addAnswer" class="bg-gray-500 text-white px-3 py-1.5 rounded text-sm hover:bg-gray-600 transition-colors">+ Жауап Қосу</button>
                            <button @click="createQuestion" class="bg-green-500 text-white px-3 py-1.5 rounded text-sm hover:bg-green-600 transition-colors">Жасау</button>
                            <button @click="cancelQuestionForm" class="bg-gray-500 text-white px-3 py-1.5 rounded text-sm hover:bg-gray-600 transition-colors">Болдырмау</button>
                        </div>
                    </div>

                    <!-- Question Form (Edit) -->
                    <div v-if="showQuestionForm && editingQuestion" class="bg-blue-50 p-4 rounded mb-4 border-2 border-blue-300">
                        <h3 class="font-semibold mb-3 text-base">Сұрақты Өңдеу</h3>
                        <textarea v-model="editingQuestion.question_text" placeholder="Сұрақ Мәтіні" class="w-full p-2 mb-3 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-y min-h-[80px]"></textarea>
                        <div class="space-y-2 mb-4">
                            <div v-for="(answer, index) in editingQuestion.answers" :key="index" class="flex flex-col sm:flex-row gap-2 items-start sm:items-center">
                                <input v-model="answer.answer_text" type="text" placeholder="Жауап" class="flex-1 w-full p-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                                <label class="flex items-center gap-2 whitespace-nowrap">
                                    <input v-model="answer.is_correct" type="checkbox" class="w-4 h-4">
                                    <span class="text-sm">Дұрыс</span>
                                </label>
                                <button @click="removeEditAnswer(index)" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 transition-colors text-xs">Жою</button>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-2">
                            <button @click="addEditAnswer" class="bg-gray-500 text-white px-3 py-1.5 rounded text-sm hover:bg-gray-600 transition-colors">+ Жауап Қосу</button>
                            <button @click="updateQuestion" class="bg-green-500 text-white px-3 py-1.5 rounded text-sm hover:bg-green-600 transition-colors">Жаңарту</button>
                            <button @click="cancelQuestionForm" class="bg-gray-500 text-white px-3 py-1.5 rounded text-sm hover:bg-gray-600 transition-colors">Болдырмау</button>
                        </div>
                    </div>

                    <!-- Questions List -->
                    <div class="space-y-3">
                        <div v-for="question in selectedTopic.questions" :key="question.id" class="bg-gray-50 p-4 rounded border">
                            <div v-if="editingQuestion?.id !== question.id">
                                <h4 class="font-semibold mb-2 text-sm md:text-base">{{ question.question_text }}</h4>
                                <ul class="list-disc list-inside ml-2 space-y-1 mb-3">
                                    <li v-for="answer in question.answers" :key="answer.id" :class="answer.is_correct ? 'text-green-600 font-semibold' : 'text-gray-700'" class="text-xs md:text-sm">
                                        {{ answer.answer_text }} {{ answer.is_correct ? '(Дұрыс)' : '' }}
                                    </li>
                                </ul>
                                <div class="flex flex-col sm:flex-row gap-2">
                                    <button @click="editQuestion(question)" class="bg-yellow-500 text-white px-3 py-1 rounded text-xs hover:bg-yellow-600 transition-colors">Өңдеу</button>
                                    <button @click="deleteQuestion(question.id)" class="bg-red-500 text-white px-3 py-1 rounded text-xs hover:bg-red-600 transition-colors">Жою</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users Tab -->
            <div v-if="activeTab === 'users'">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
                    <h2 class="text-xl md:text-2xl font-semibold text-gray-800">Пайдаланушыларды Басқару</h2>
                    <button @click="loadUsers" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition-colors w-full sm:w-auto">
                        Жаңарту
                    </button>
                </div>

                <div v-if="usersLoading" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    <p class="mt-2 text-gray-600">Пайдаланушылар жүктелуде...</p>
                </div>

                <div v-else class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Аты</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Электрондық Пошта</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Рөлі</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Мәртебесі</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Тіркелген</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Әрекеттер</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ user.id }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-800 font-medium">{{ user.name }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-700">{{ user.email }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <span :class="user.role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'" class="px-2 py-1 rounded text-xs font-medium">
                                            {{ user.role }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span v-if="user.banned_at" class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-medium">
                                            Тыйым салынған
                                        </span>
                                        <span v-else class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-medium">
                                            Белсенді
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">{{ formatDate(user.created_at) }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex gap-2">
                                            <button
                                                v-if="!user.banned_at && user.role !== 'admin'"
                                                @click="banUser(user.id)"
                                                class="bg-red-500 text-white px-3 py-1 rounded text-xs hover:bg-red-600 transition-colors"
                                            >
                                                Тыйым салу
                                            </button>
                                            <button
                                                v-if="user.banned_at"
                                                @click="unbanUser(user.id)"
                                                class="bg-green-500 text-white px-3 py-1 rounded text-xs hover:bg-green-600 transition-colors"
                                            >
                                                Тыйымды алып тастау
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Swal from 'sweetalert2';

export default {
    name: 'AdminDashboard',
    data() {
        // Get user data from meta tags
        const userNameMeta = document.head.querySelector('meta[name="user-name"]');
        const userEmailMeta = document.head.querySelector('meta[name="user-email"]');
        
        return {
            activeTab: 'topics',
            topics: [],
            users: [],
            usersLoading: false,
            selectedTopic: null,
            showTopicForm: false,
            showQuestionForm: false,
            editingQuestion: null,
            currentUser: {
                name: userNameMeta ? userNameMeta.content : 'User',
                email: userEmailMeta ? userEmailMeta.content : ''
            },
            newTopic: {
                title: '',
                time_limit: null
            },
            newQuestion: {
                question_text: '',
                answers: [
                    { answer_text: '', is_correct: false },
                    { answer_text: '', is_correct: false }
                ]
            }
        };
    },
    computed: {
        userInitial() {
            if (this.currentUser.name) {
                return this.currentUser.name.charAt(0).toUpperCase();
            }
            return 'U';
        }
    },
    mounted() {
        // Ensure axios is configured
        if (!window.axios) {
            console.error('Axios is not configured. Make sure bootstrap.js is loaded.');
            return;
        }
        
        // Update CSRF token if available
        const token = document.head.querySelector('meta[name="csrf-token"]');
        if (token && window.axios) {
            window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
        }
        
        this.loadTopics();
        if (this.activeTab === 'users') {
            this.loadUsers();
        }
    },
    watch: {
        activeTab(newTab) {
            if (newTab === 'users') {
                this.loadUsers();
            }
        }
    },
    methods: {
        async loadTopics() {
            try {
                const response = await window.axios.get('/api/topics');
                this.topics = response.data;
            } catch (error) {
                console.error('Error loading topics:', error);
            }
        },
        async loadUsers() {
            this.usersLoading = true;
            try {
                const response = await window.axios.get('/api/users');
                this.users = response.data;
            } catch (error) {
                console.error('Error loading users:', error);
                const errorMessage = error.response?.data?.message || error.message || 'Error loading users. Please try again.';
                await Swal.fire({
                    icon: 'error',
                    title: 'Қате',
                    text: errorMessage,
                    confirmButtonColor: '#ef4444'
                });
            } finally {
                this.usersLoading = false;
            }
        },
        formatDate(dateString) {
            if (!dateString) return '-';
            const date = new Date(dateString);
            return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        },
        async banUser(userId) {
            const result = await Swal.fire({
                icon: 'warning',
                title: 'Пайдаланушыға тыйым салу?',
                text: 'Сіз бұл пайдаланушыға тыйым салғыңыз келе ме?',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Иә, тыйым салу!',
                cancelButtonText: 'Болдырмау'
            });

            if (!result.isConfirmed) return;

            try {
                await window.axios.post(`/api/users/${userId}/ban`);
                await Swal.fire({
                    icon: 'success',
                    title: 'Тыйым салынды!',
                    text: 'Пайдаланушыға тыйым сәтті салынды.',
                    confirmButtonText: 'Дайын',
                    confirmButtonColor: '#10b981'
                });
                this.loadUsers();
            } catch (error) {
                console.error('Error banning user:', error);
                const message = error.response?.data?.message || 'Error banning user. Please try again.';
                await Swal.fire({
                    icon: 'error',
                    title: 'Қате',
                    text: message,
                    confirmButtonColor: '#ef4444'
                });
            }
        },
        async unbanUser(userId) {
            const result = await Swal.fire({
                icon: 'question',
                title: 'Пайдаланушыдан тыйымды алып тастау?',
                text: 'Сіз бұл пайдаланушыдан тыйымды алып тастағыңыз келе ме?',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Иә, тыйымды алып тастау!',
                cancelButtonText: 'Болдырмау'
            });

            if (!result.isConfirmed) return;

            try {
                await window.axios.post(`/api/users/${userId}/unban`);
                await Swal.fire({
                    icon: 'success',
                    title: 'Тыйым алынып тасталды!',
                    text: 'Пайдаланушыдан тыйым сәтті алынып тасталды.',
                    confirmButtonText: 'Дайын',
                    confirmButtonColor: '#10b981'
                });
                this.loadUsers();
            } catch (error) {
                console.error('Error unbanning user:', error);
                await Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Пайдаланушыдан тыйымды алып тастау қатесі. Қайталап көріңіз.',
                    confirmButtonColor: '#ef4444'
                });
            }
        },
        async createTopic() {
            if (!this.newTopic.title || this.newTopic.title.trim() === '') {
                await Swal.fire({
                    icon: 'warning',
                    title: 'Тексеру Қатесі',
                    text: 'Тақырып атауын енгізіңіз',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }
            if (this.newTopic.time_limit && (this.newTopic.time_limit < 1 || this.newTopic.time_limit > 600)) {
                await Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'Уақыт шегі 1 мен 600 минут арасында болуы керек',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }
            try {
                await window.axios.post('/api/topics', this.newTopic);
                this.newTopic.title = '';
                this.newTopic.time_limit = null;
                this.showTopicForm = false;
                this.loadTopics();
                await Swal.fire({
                    icon: 'success',
                    title: 'Сәтті!',
                    text: 'Тақырып сәтті жасалды!',
                    confirmButtonText: 'Дайын',
                    confirmButtonColor: '#10b981'
                });
            } catch (error) {
                console.error('Error creating topic:', error);
                await Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Тақырып жасау қатесі. Қайталап көріңіз.',
                    confirmButtonColor: '#ef4444'
                });
            }
        },
        async deleteTopic(id) {
            const result = await Swal.fire({
                icon: 'warning',
                title: 'Сіз сенімдісіз бе?',
                text: 'Сіз бұл тақырыпты жойғыңыз келе ме?',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Иә, жою!',
                cancelButtonText: 'Болдырмау'
            });

            if (!result.isConfirmed) return;

            try {
                await window.axios.delete(`/api/topics/${id}`);
                this.loadTopics();
                if (this.selectedTopic?.id === id) {
                    this.selectedTopic = null;
                }
                await Swal.fire({
                    icon: 'success',
                    title: 'Жойылды!',
                    text: 'Тақырып жойылды.',
                    confirmButtonColor: '#10b981'
                });
            } catch (error) {
                console.error('Error deleting topic:', error);
                await Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Тақырыпты жою қатесі. Қайталап көріңіз.',
                    confirmButtonColor: '#ef4444'
                });
            }
        },
        async selectTopic(topic) {
            try {
                const response = await window.axios.get(`/api/topics/${topic.id}`);
                this.selectedTopic = response.data;
                this.showQuestionForm = false;
                this.editingQuestion = null;
            } catch (error) {
                console.error('Error loading topic:', error);
            }
        },
        async generateQr(topicId) {
            try {
                const response = await window.axios.post(`/api/topics/${topicId}/generate-qr`);
                await Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'QR Код сәтті жасалды!',
                    confirmButtonText: 'Дайын',
                    confirmButtonColor: '#10b981'
                });
                this.loadTopics();
            } catch (error) {
                console.error('Error generating QR:', error);
                await Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'QR код жасау қатесі. Қайталап көріңіз.',
                    confirmButtonColor: '#ef4444'
                });
            }
        },
        addAnswer() {
            this.newQuestion.answers.push({ answer_text: '', is_correct: false });
        },
        removeAnswer(index) {
            if (this.newQuestion.answers.length > 2) {
                this.newQuestion.answers.splice(index, 1);
            }
        },
        async createQuestion() {
            if (!this.selectedTopic) return;
            if (!this.newQuestion.question_text || this.newQuestion.question_text.trim() === '') {
                await Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'Please enter a question text',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }
            if (this.newQuestion.answers.length < 2) {
                await Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'Please add at least 2 answers',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }
            if (!this.newQuestion.answers.some(a => a.is_correct)) {
                await Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'Please mark at least one answer as correct',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }
            try {
                await window.axios.post('/api/questions', {
                    topic_id: this.selectedTopic.id,
                    question_text: this.newQuestion.question_text,
                    answers: this.newQuestion.answers
                });
                this.newQuestion = {
                    question_text: '',
                    answers: [
                        { answer_text: '', is_correct: false },
                        { answer_text: '', is_correct: false }
                    ]
                };
                this.showQuestionForm = false;
                this.selectTopic(this.selectedTopic);
                await Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Сұрақ сәтті жасалды!',
                    confirmButtonText: 'Дайын',
                    confirmButtonColor: '#10b981'
                });
            } catch (error) {
                console.error('Error creating question:', error);
                await Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Сұрақ жасау қатесі. Қайталап көріңіз.',
                    confirmButtonColor: '#ef4444'
                });
            }
        },
        async deleteQuestion(id) {
            const result = await Swal.fire({
                icon: 'warning',
                title: 'Are you sure?',
                text: 'Сіз бұл сұрақты жойғыңыз келе ме?',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            });

            if (!result.isConfirmed) return;

            try {
                await window.axios.delete(`/api/questions/${id}`);
                this.selectTopic(this.selectedTopic);
                await Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: 'Сұрақ жойылды.',
                    confirmButtonColor: '#10b981'
                });
            } catch (error) {
                console.error('Error deleting question:', error);
                await Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Сұрақты жою қатесі. Қайталап көріңіз.',
                    confirmButtonColor: '#ef4444'
                });
            }
        },
        editQuestion(question) {
            this.editingQuestion = {
                id: question.id,
                question_text: question.question_text,
                answers: question.answers.map(answer => ({
                    id: answer.id,
                    answer_text: answer.answer_text,
                    is_correct: answer.is_correct
                }))
            };
            this.showQuestionForm = true;
        },
        cancelQuestionForm() {
            this.showQuestionForm = false;
            this.editingQuestion = null;
            this.newQuestion = {
                question_text: '',
                answers: [
                    { answer_text: '', is_correct: false },
                    { answer_text: '', is_correct: false }
                ]
            };
        },
        addEditAnswer() {
            this.editingQuestion.answers.push({ answer_text: '', is_correct: false });
        },
        removeEditAnswer(index) {
            if (this.editingQuestion.answers.length > 2) {
                this.editingQuestion.answers.splice(index, 1);
            }
        },
        async updateQuestion() {
            if (!this.editingQuestion) return;
            if (!this.editingQuestion.question_text || this.editingQuestion.question_text.trim() === '') {
                await Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'Please enter a question text',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }
            if (this.editingQuestion.answers.length < 2) {
                await Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'Please add at least 2 answers',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }
            if (!this.editingQuestion.answers.some(a => a.is_correct)) {
                await Swal.fire({
                    icon: 'warning',
                    title: 'Validation Error',
                    text: 'Please mark at least one answer as correct',
                    confirmButtonColor: '#3b82f6'
                });
                return;
            }
            try {
                await window.axios.put(`/api/questions/${this.editingQuestion.id}`, {
                    question_text: this.editingQuestion.question_text,
                    answers: this.editingQuestion.answers.map(answer => ({
                        answer_text: answer.answer_text,
                        is_correct: answer.is_correct
                    }))
                });
                this.cancelQuestionForm();
                this.selectTopic(this.selectedTopic);
                await Swal.fire({
                    icon: 'success',
                    title: 'Сәтті!',
                    text: 'Сұрақ сәтті жаңартылды!',
                    confirmButtonText: 'Дайын',
                    confirmButtonColor: '#10b981'
                });
            } catch (error) {
                console.error('Error updating question:', error);
                await Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Сұрақты жаңарту қатесі. Қайталап көріңіз.',
                    confirmButtonColor: '#ef4444'
                });
            }
        },
        async logout() {
            const result = await Swal.fire({
                icon: 'question',
                title: 'Шығу?',
                text: 'Сіз шығатыныңызға сенімдісіз бе?',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Иә, шығу!',
                cancelButtonText: 'Болдырмау'
            });

            if (result.isConfirmed) {
                try {
                    await window.axios.post('/logout');
                    window.location.href = '/login';
                } catch (error) {
                    console.error('Error logging out:', error);
                    window.location.href = '/login';
                }
            }
        }
    }
};
</script>
