import './bootstrap';
import { createApp } from 'vue';
import AdminDashboard from './components/AdminDashboard.vue';
import StudentQuiz from './components/StudentQuiz.vue';

// Make Vue available globally for inline scripts
window.Vue = { createApp };
// axios is already configured in bootstrap.js and available as window.axios

// Admin Dashboard
const adminApp = document.getElementById('admin-app');
if (adminApp) {
    createApp(AdminDashboard).mount('#admin-app');
}

// Student Quiz
const studentApp = document.getElementById('student-app');
if (studentApp) {
    createApp(StudentQuiz).mount('#student-app');
}

// Home page topics - initialize after DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const topicsApp = document.getElementById('topics-app');
    if (topicsApp && !document.getElementById('admin-app') && !document.getElementById('student-app')) {
        createApp({
            data() {
                return {
                    topics: [],
                    loading: true
                };
            },
            mounted() {
                this.loadTopics();
            },
            methods: {
                async loadTopics() {
                    try {
                        const response = await window.axios.get('/api/public/topics');
                        this.topics = response.data;
                        this.loading = false;
                    } catch (error) {
                        console.error('Error loading topics:', error);
                        this.loading = false;
                    }
                },
                goToQuiz(topicId) {
                    window.location.href = `/quiz/${topicId}`;
                }
            }
        }).mount('#topics-app');
    }
});
