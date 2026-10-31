<template>
    <main class="exam-page container-fluid py-3">
        <section class="exam-header card">
            <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
                <div>
                    <p class="text-muted mb-1">Purplins School CBT</p>
                    <h1 class="h4 mb-1">{{ fullname }}</h1>
                    <p class="mb-0">{{ answeredCount }} of {{ questionsState.length }} questions answered</p>
                </div>
                <div class="exam-actions d-flex align-items-center">
                    <div class="timer mr-3" :class="{ 'timer-warning': secondsLeft <= 300 }" role="timer" aria-live="off">
                        {{ formattedTime }}
                        <span class="sr-only">remaining</span>
                    </div>
                    <button class="btn btn-outline-danger" :disabled="submitting" @click="submitExam(false)">
                        Submit exam
                    </button>
                </div>
            </div>
        </section>

        <section class="exam-workspace row mt-3">
            <aside class="col-lg-3 mb-3">
                <div class="card h-100">
                    <div class="card-header font-weight-bold">Question navigator</div>
                    <div class="card-body">
                        <div class="question-grid" aria-label="Question navigator">
                            <button
                                v-for="(item, itemIndex) in questionsState"
                                :key="item.id"
                                type="button"
                                class="question-number"
                                :class="{
                                    'is-current': itemIndex === index,
                                    'is-answered': item.your_answer,
                                }"
                                :aria-label="'Question ' + (itemIndex + 1) + (item.your_answer ? ', answered' : ', unanswered')"
                                :aria-current="itemIndex === index ? 'step' : null"
                                @click="selectQuestion(itemIndex)"
                            >
                                {{ itemIndex + 1 }}
                            </button>
                        </div>
                        <div class="navigator-legend mt-3">
                            <span><i class="legend-dot answered-dot"></i> Answered</span>
                            <span><i class="legend-dot unanswered-dot"></i> Unanswered</span>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="col-lg-9">
                <section v-if="question" class="card question-card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span class="font-weight-bold">Question {{ index + 1 }} <span class="text-muted">/ {{ questionsState.length }}</span></span>
                        <span v-if="saving" class="text-muted" role="status">Saving answer…</span>
                        <span v-else-if="saveError" class="text-danger" role="alert">{{ saveError }}</span>
                        <span v-else-if="question.your_answer" class="text-success" role="status">Answer saved</span>
                    </div>
                    <div class="card-body">
                        <div class="question-content mb-4" v-html="question.question"></div>
                        <fieldset class="answer-options" :disabled="saving || submitting || secondsLeft <= 0">
                            <legend class="sr-only">Choose one answer</legend>
                            <label
                                v-for="option in options"
                                :key="option.value"
                                class="answer-option"
                                :class="{ 'is-selected': question.your_answer === option.value }"
                                :for="'answer-' + question.id + '-' + option.value"
                            >
                                <input
                                    :id="'answer-' + question.id + '-' + option.value"
                                    :checked="question.your_answer === option.value"
                                    type="radio"
                                    :name="'answer-' + question.id"
                                    :value="option.value"
                                    @change="saveAnswer(option.value)"
                                >
                                <span class="option-letter">{{ option.label }}</span>
                                <span class="option-text">{{ question[option.value] }}</span>
                            </label>
                        </fieldset>
                    </div>
                    <div class="card-footer d-flex justify-content-between">
                        <button class="btn btn-outline-secondary" :disabled="index === 0 || saving" @click="selectQuestion(index - 1)">
                            Previous
                        </button>
                        <button
                            v-if="index < questionsState.length - 1"
                            class="btn btn-primary"
                            :disabled="saving"
                            @click="selectQuestion(index + 1)"
                        >
                            Next
                        </button>
                        <button
                            v-else
                            class="btn btn-success"
                            :disabled="saving || submitting"
                            @click="submitExam(false)"
                        >
                            Finish exam
                        </button>
                    </div>
                </section>
                <div v-else class="alert alert-warning">No questions were found for this exam.</div>
                <div v-if="submitError" class="alert alert-danger mt-3" role="alert">{{ submitError }}</div>
            </div>
        </section>

        <p class="keyboard-hint text-muted text-center mt-3 mb-0">
            Keyboard shortcuts: 1–5 to choose an answer, ←/→ to move between questions.
        </p>
    </main>
</template>

<script>
export default {
    props: ['fullname', 'student_id', 'subjectId', 'questions', 'remainingSeconds'],
    data() {
        return {
            questionsState: this.questions.map(question => ({ ...question })),
            index: 0,
            secondsLeft: Math.max(0, Number(this.remainingSeconds) || 0),
            saving: false,
            submitting: false,
            submitWhenSaved: false,
            saveError: '',
            submitError: '',
            timerInterval: null,
        };
    },
    computed: {
        question() {
            return this.questionsState[this.index] || null;
        },
        options() {
            if (!this.question) {
                return [];
            }

            return [
                { label: 'A', value: 'option_a' },
                { label: 'B', value: 'option_b' },
                { label: 'C', value: 'option_c' },
                { label: 'D', value: 'option_d' },
                { label: 'E', value: 'option_e' },
            ].filter(option => this.question[option.value]);
        },
        answeredCount() {
            return this.questionsState.filter(question => question.your_answer).length;
        },
        formattedTime() {
            const hours = Math.floor(this.secondsLeft / 3600);
            const minutes = Math.floor((this.secondsLeft % 3600) / 60);
            const seconds = this.secondsLeft % 60;
            const parts = [minutes, seconds];
            if (hours > 0) {
                parts.unshift(hours);
            }
            return parts.map(value => String(value).padStart(2, '0')).join(':');
        },
    },
    methods: {
        async saveAnswer(answer) {
            if (!this.question || this.saving || this.submitting) {
                return;
            }

            const question = this.question;
            const previousAnswer = question.your_answer;
            question.your_answer = answer;
            this.saving = true;
            this.saveError = '';

            try {
                await axios.post('/answer-question', {
                    question_id: question.id,
                    subject_id: this.subjectId,
                    your_answer: answer,
                });
            } catch (error) {
                question.your_answer = previousAnswer;
                this.saveError = error.response && error.response.status === 409
                    ? 'Time expired. Submitting your exam…'
                    : 'Could not save your answer. Please try again.';
                if (error.response && error.response.status === 409) {
                    this.secondsLeft = 0;
                    this.submitWhenSaved = true;
                }
            } finally {
                this.saving = false;
                if (this.submitWhenSaved) {
                    this.submitWhenSaved = false;
                    this.submitExam(true);
                }
            }
        },
        selectQuestion(questionIndex) {
            if (!this.saving && questionIndex >= 0 && questionIndex < this.questionsState.length) {
                this.index = questionIndex;
            }
        },
        submitExam(timeExpired) {
            if (this.submitting) {
                return;
            }
            if (this.saving) {
                this.submitWhenSaved = true;
                return;
            }

            const unanswered = this.questionsState.length - this.answeredCount;
            if (!timeExpired && unanswered > 0 && !window.confirm(
                `You still have ${unanswered} unanswered question${unanswered === 1 ? '' : 's'}. Submit anyway?`
            )) {
                return;
            }

            this.submitting = true;
            this.submitError = '';
            axios.post('/cbt-result', { subject_id: this.subjectId })
                .then(() => window.location.assign('/cbt-login'))
                .catch(() => {
                    this.submitting = false;
                    this.submitError = 'Could not submit the exam. Please try again.';
                });
        },
        handleKeydown(event) {
            if (event.altKey || event.ctrlKey || event.metaKey || event.target.closest('input, textarea, select')) {
                return;
            }

            const optionIndex = Number(event.key) - 1;
            if (optionIndex >= 0 && optionIndex < this.options.length) {
                this.saveAnswer(this.options[optionIndex].value);
            } else if (event.key === 'ArrowRight') {
                this.selectQuestion(this.index + 1);
            } else if (event.key === 'ArrowLeft') {
                this.selectQuestion(this.index - 1);
            }
        },
    },
    mounted() {
        this.timerInterval = window.setInterval(() => {
            if (this.secondsLeft > 0) {
                this.secondsLeft -= 1;
            }
            if (this.secondsLeft <= 0) {
                window.clearInterval(this.timerInterval);
                this.submitExam(true);
            }
        }, 1000);
        window.addEventListener('keydown', this.handleKeydown);
    },
    beforeDestroy() {
        window.clearInterval(this.timerInterval);
        window.removeEventListener('keydown', this.handleKeydown);
    },
};
</script>

<style scoped>
.exam-page {
    max-width: 1440px;
}

.timer {
    min-width: 110px;
    color: #155724;
    font-size: 1.6rem;
    font-variant-numeric: tabular-nums;
    font-weight: 700;
    text-align: center;
}

.timer-warning {
    color: #b02a37;
}

.question-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(42px, 1fr));
    gap: 0.5rem;
}

.question-number {
    min-height: 42px;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    background: #fff;
    color: #343a40;
    font-weight: 700;
}

.question-number.is-answered {
    border-color: #198754;
    background: #198754;
    color: #fff;
}

.question-number.is-current {
    outline: 3px solid #0d6efd;
    outline-offset: 2px;
}

.navigator-legend {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    font-size: 0.875rem;
}

.legend-dot {
    display: inline-block;
    width: 0.7rem;
    height: 0.7rem;
    margin-right: 0.25rem;
    border-radius: 50%;
}

.answered-dot {
    background: #198754;
}

.unanswered-dot {
    border: 1px solid #adb5bd;
    background: #fff;
}

.question-content {
    font-size: 1.15rem;
    line-height: 1.65;
}

.question-content img {
    max-width: 100%;
    height: auto;
}

.answer-options {
    display: grid;
    gap: 0.75rem;
    border: 0;
    padding: 0;
}

.answer-option {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    margin: 0;
    padding: 1rem;
    border: 1px solid #dee2e6;
    border-radius: 0.5rem;
    cursor: pointer;
}

.answer-option:hover,
.answer-option.is-selected {
    border-color: #0d6efd;
    background: #f2f7ff;
}

.answer-option input {
    margin-top: 0.3rem;
}

.option-letter {
    flex: 0 0 1.5rem;
    font-weight: 700;
}

.keyboard-hint {
    font-size: 0.875rem;
}

@media (max-width: 575px) {
    .exam-actions {
        width: 100%;
        justify-content: space-between;
        margin-top: 1rem;
    }

    .timer {
        font-size: 1.35rem;
    }
}
</style>
