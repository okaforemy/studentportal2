<template>
    <div class="col-12 pt-3">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">CBT exam schedule</h1>
            </div>
            <div class="card-body">
                <div v-if="errors.settings" class="alert alert-danger" role="alert">{{ errors.settings }}</div>
                <div v-if="errors.subject" class="alert alert-danger" role="alert">{{ errors.subject }}</div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Subject</th>
                                <th scope="col">Section</th>
                                <th scope="col">Exam date</th>
                                <th scope="col">Start time</th>
                                <th scope="col">Duration (minutes)</th>
                                <th scope="col">Availability</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(setting, index) in schedule" :key="setting.subject_id + '-' + setting.section">
                                <td>
                                    {{ setting.subject }}
                                    <input type="hidden" :name="'subject[' + index + ']'" :value="setting.subject">
                                    <input type="hidden" :name="'section[' + index + ']'" :value="setting.section">
                                    <input type="hidden" :name="'subject_id[' + index + ']'" :value="setting.subject_id">
                                </td>
                                <td>{{ setting.section.split('_').join(' ') }}</td>
                                <td>
                                    <input
                                        v-model="setting.date"
                                        type="date"
                                        class="form-control"
                                        :aria-label="'Exam date for ' + setting.subject"
                                    >
                                </td>
                                <td>
                                    <input
                                        v-model="setting.time"
                                        type="time"
                                        class="form-control"
                                        :aria-label="'Start time for ' + setting.subject"
                                    >
                                </td>
                                <td>
                                    <input
                                        v-model="setting.duration"
                                        type="number"
                                        min="1"
                                        max="600"
                                        class="form-control"
                                        :aria-label="'Duration in minutes for ' + setting.subject"
                                    >
                                </td>
                                <td>
                                    <button
                                        type="button"
                                        class="btn"
                                        :class="setting.is_started ? 'btn-danger' : 'btn-outline-primary'"
                                        :disabled="!setting.id || !isScheduleReady(setting) || togglingId === setting.id"
                                        @click="toggleExam(setting)"
                                    >
                                        {{ togglingId === setting.id ? 'Saving…' : (setting.is_started ? 'Stop exam' : 'Start exam') }}
                                    </button>
                                    <span v-if="!setting.id" class="d-block small text-muted mt-1">Save schedule to enable</span>
                                </td>
                            </tr>
                            <tr v-if="schedule.length === 0">
                                <td colspan="6" class="text-center text-muted">No subjects are available to schedule.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="text-right">
                    <button class="btn btn-primary" :disabled="saving || schedule.length === 0" @click="saveSettings">
                        {{ saving ? 'Saving…' : 'Save schedule' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        subjects: { type: Array, default: () => [] },
        settings: { type: Array, default: () => [] },
        errors: { type: Object, default: () => ({}) },
    },
    data() {
        return {
            schedule: this.settings.map(setting => ({
                ...setting,
                time: setting.time || '00:00',
                duration: setting.duration === null ? '' : setting.duration,
            })),
            saving: false,
            togglingId: null,
        };
    },
    methods: {
        isScheduleReady(setting) {
            return Boolean(
                setting.date
                && setting.time
                && Number.isInteger(Number(setting.duration))
                && Number(setting.duration) >= 1
                && Number(setting.duration) <= 600
            );
        },
        saveSettings() {
            this.saving = true;
            this.$inertia.post('/cbt-settings', {
                subject: this.schedule.map(setting => setting.subject),
                section: this.schedule.map(setting => setting.section),
                subject_id: this.schedule.map(setting => setting.subject_id),
                date: this.schedule.map(setting => setting.date || null),
                time: this.schedule.map(setting => setting.time || null),
                duration: this.schedule.map(setting => setting.duration || null),
            }, {
                onSuccess: page => {
                    this.schedule = page.props.settings.map(setting => ({
                        ...setting,
                        time: setting.time || '00:00',
                        duration: setting.duration === null ? '' : setting.duration,
                    }));
                    toastr.success('Exam schedules saved successfully.', 'Success');
                },
                onError: () => toastr.error('Check the schedule fields and try again.', 'Unable to save'),
                onFinish: () => { this.saving = false; },
            });
        },
        toggleExam(setting) {
            if (!this.isScheduleReady(setting)) {
                toastr.error('Set a valid date, start time, and duration first.', 'Incomplete schedule');
                return;
            }

            this.togglingId = setting.id;
            this.$inertia.post('/start-exam-setting', {
                id: setting.id,
                is_started: !setting.is_started,
            }, {
                onSuccess: () => {
                    setting.is_started = !setting.is_started;
                    toastr.success(setting.is_started ? 'Exam is now available.' : 'Exam stopped.', 'Schedule updated');
                },
                onError: () => toastr.error('The exam schedule could not be updated.', 'Unable to update'),
                onFinish: () => { this.togglingId = null; },
            });
        },
    },
};
</script>
